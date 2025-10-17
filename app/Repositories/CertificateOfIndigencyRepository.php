<?php

namespace App\Repositories;

use App\Models\CertificateOfIndigency;
use App\Models\SupportingDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Traits\AdminListFilters;

class CertificateOfIndigencyRepository extends Repository
{
    use AdminListFilters;

    protected ?SupportingDocument $supportingDocument = null;

    public function __construct(CertificateOfIndigency $model, SupportingDocument $supportingDocument)
    {
        parent::__construct($model);
        $this->supportingDocument = $supportingDocument;
    }

    /**
     * Create indigency certificate application.
     */
    public function createApplication(array $data, int $userId): CertificateOfIndigency
    {
        return DB::transaction(function () use ($data, $userId) {
            /** @var CertificateOfIndigency $cert */
            $cert = $this->model->newQuery()->create([
                'user_id' => $userId,
                'purpose' => $data['purpose'] ?? null,
                'status' => 'pending',
                'application_date' => now()->toDateString(),
            ]);
            return $cert;
        });
    }

    /**
     * Get the user's pending or processing indigency application, if any.
     */
    public function getPending(int $userId): ?CertificateOfIndigency
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }

    /**
     * Get the most recent indigency certificate for the given user.
     */
    public function getLatest(int $userId): ?CertificateOfIndigency
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    /**
     * Admin: list indigency certificates with filters and stats.
     */
    public function listWithFilters(array $filters = [], int $perPage = 10): array
    {
        $query = $this->model->newQuery()
            ->with(['user', 'user.applicantProfile'])
            ->orderByDesc('updated_at');

        $this->applyNameStatusDateFilters($query, $filters, function ($q, string $name) {
            $q->where(function ($sub) use ($name) {
                $sub->whereHas('user', function ($u) use ($name) {
                    $u->where('name', 'like', '%' . $name . '%');
                })
                ->orWhereHas('user.applicantProfile', function ($ap) use ($name) {
                    $ap->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name, suffix) like ?", ['%' . $name . '%']);
                });
            });
        });

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage)->appends($filters);

        $stats = [
            'total' => $this->model->newQuery()->count(),
            'pending' => $this->model->newQuery()->where('status', 'pending')->count(),
            'approved' => $this->model->newQuery()->where('status', 'approved')->count(),
            'rejected' => $this->model->newQuery()->where('status', 'rejected')->count(),
            'processing' => $this->model->newQuery()->where('status', 'processing')->count(),
        ];

        return [
            'items' => $paginator->items(),
            'stats' => $stats,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Admin: get a single indigency certificate with user relations.
     */
    public function getWithAllRelations(int $id): CertificateOfIndigency
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