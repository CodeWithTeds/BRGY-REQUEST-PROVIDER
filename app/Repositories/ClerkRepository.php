<?php

namespace App\Repositories;

use App\Models\Clerk;

class ClerkRepository extends Repository
{
    public function __construct(Clerk $model)
    {
        parent::__construct($model);
    }

    /** Paginated list with optional search filter. */
    public function listWithSearch(string $search, int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery()->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->appends(['search' => $search]);
    }
}
