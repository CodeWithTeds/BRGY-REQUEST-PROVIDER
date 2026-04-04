<?php

namespace App\Repositories;

use App\Models\CertificateOfResidency;
use App\Traits\AdminListFilters;

class CertificateOfResidencyRepository extends Repository
{
    use AdminListFilters;
    public function __construct(CertificateOfResidency $model)
    {
        parent::__construct($model);
    }

    public function createApplication(array $data, int $userId): CertificateOfResidency
    {
        return $this->create([
            'user_id' => $userId,
            'purpose' => $data['purpose'],
            'application_date' => now()->setTimezone('Asia/Manila')->toDateString(),
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
     * Get the most recent residency certificate for the given user.
     */
    public function getLatest(int $userId): ?CertificateOfResidency
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    /**
     * Admin: list residency certificates with optional filters.
     */
    public function adminListWithFilters(array $filters, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->newQuery()
            ->with(['user.applicantProfile', 'user.addresses.barangay'])
            ->latest();

        $this->applyNameStatusDateFilters($query, $filters, function ($q, string $name) {
            $q->where(function ($sub) use ($name) {
                $sub->whereHas('user', function ($u) use ($name) {
                    $u->where('name', 'like', $name . '%');
                })
                ->orWhereHas('user.applicantProfile', function ($ap) use ($name) {
                    $ap->where('first_name', 'like', $name . '%')
                       ->orWhere('last_name', 'like', $name . '%');
                });
            });
        });

        // Server-side pagination to reduce payload size
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Update status and optional remarks for a residency certificate.
     */
    public function updateStatus(int $id, string $status, ?string $remarks = null): bool
    {
        $certificate = $this->find($id);
        if (!$certificate) {
            return false;
        }

        $data = ['status' => $status];
        if ($remarks !== null) {
            $data['remarks'] = $remarks;
        }

        return $certificate->update($data);
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
                'supportingDocuments',
            ])
            ->findOrFail($id);
    }
}