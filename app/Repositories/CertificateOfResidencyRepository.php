<?php

namespace App\Repositories;

use App\Models\CertificateOfResidency;

class CertificateOfResidencyRepository extends Repository
{
    public function __construct(CertificateOfResidency $model)
    {
        parent::__construct($model);
    }

    public function createApplication(array $data, int $userId): CertificateOfResidency
    {
        return $this->create([
            'user_id' => $userId,
            'purpose' => $data['purpose'],
            'application_date' => now()->toDateString(),
        ]);
    }

    public function getPending(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }

    /**
     * Admin: list residency certificates with optional filters.
     */
    public function adminListWithFilters(array $filters)
    {
        $query = $this->model->newQuery()
            ->with(['user.applicantProfile', 'user.addresses.barangay'])
            ->latest();

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $query->whereHas('user', function ($u) use ($name) {
                $u->where('name', 'like', '%' . $name . '%');
            });
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, ['pending', 'processing', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        if ($dateFrom && $dateTo) {
            $query->whereBetween('application_date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->whereDate('application_date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('application_date', '<=', $dateTo);
        }

        return $query->get();
    }

    /**
     * Admin: get a single residency certificate with related user data for detailed view.
     */
    public function getWithAllRelations(int $id): CertificateOfResidency
    {
        return $this->model->newQuery()
            ->with([
                'user',
                'user.applicantProfile',
                'user.addresses.barangay',
                'user.addresses.city',
                'user.addresses.province',
                'user.addresses.region',
            ])
            ->findOrFail($id);
    }
}