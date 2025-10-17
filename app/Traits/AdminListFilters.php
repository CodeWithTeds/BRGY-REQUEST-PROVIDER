<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AdminListFilters
{
    /**
     * Apply reusable name, status, and date range filters.
     * $applyNameSearch receives (Builder $query, string $name) and should add model-specific name conditions.
     */
    protected function applyNameStatusDateFilters(Builder $query, array $filters, callable $applyNameSearch): Builder
    {
        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $applyNameSearch($query, $name);
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, ['pending', 'processing', 'pre-approved', 'approved', 'rejected'])) {
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

        return $query;
    }
}