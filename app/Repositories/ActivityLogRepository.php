<?php

namespace App\Repositories;

use App\Models\StaffActivityLog;

class ActivityLogRepository extends Repository
{
    public function __construct(StaffActivityLog $model)
    {
        parent::__construct($model);
    }

    /**
     * Return a paginated, filtered query of activity logs with eager-loaded relations.
     */
    public function listWithFilters(array $filters, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['user', 'clerk']);

        if (!empty($filters['id'])) {
            $query->where('id', (int) $filters['id']);
        }
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }
        if (!empty($filters['clerk_id'])) {
            $query->where('clerk_id', (int) $filters['clerk_id']);
        }
        if (!empty($filters['permit_id'])) {
            $query->where('subject_id', (int) $filters['permit_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }
}
