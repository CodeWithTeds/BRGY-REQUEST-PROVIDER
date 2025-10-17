<?php

namespace App\Services;

use App\Http\Requests\AdminListRequest;

class AdminListService
{
    /**
     * Build a standardized admin list response using repository pagination and reusable filters.
     *
     * @param AdminListRequest $request Validated request providing filters and pagination
     * @param object $repository Repository with adminListWithFilters($filters, $page, $perPage)
     * @param string $modelClass Eloquent model class used for global stats
     * @param callable $toResource Maps a model item to a plain array suitable for the frontend
     * @return array{items: array, stats: array, filters: array, pagination: array}
     */
    public function getList(AdminListRequest $request, object $repository, string $modelClass, callable $toResource): array
    {
        $filters = $request->filters();
        $pagination = $request->pagination();
        $page = $pagination['page'];
        $perPage = $pagination['per_page'];

        $paginator = $repository->adminListWithFilters($filters, $page, $perPage);

        $items = collect($paginator->items())
            ->map(fn($item) => $toResource($item))
            ->all();

        // Global stats for dashboard cards
        $stats = [
            'total' => $modelClass::count(),
            'approved' => $modelClass::where('status', 'approved')->count(),
            'pending' => $modelClass::where('status', 'pending')->count(),
            'rejected' => $modelClass::where('status', 'rejected')->count(),
            'processing' => $modelClass::where('status', 'processing')->count(),
            // added pre-approved status count
            'pre_approved' => $modelClass::where('status', 'pre-approved')->count(),
        ];

        return [
            'items' => $items,
            'stats' => $stats,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /**
     * Get global status counts for a given model class.
     *
     * @param string $modelClass Eloquent model class
     * @return array{total:int,approved:int,pending:int,rejected:int,processing:int,pre_approved:int}
     */
    public function getStats(string $modelClass): array
    {
        return [
            'total' => $modelClass::count(),
            'approved' => $modelClass::where('status', 'approved')->count(),
            'pending' => $modelClass::where('status', 'pending')->count(),
            'rejected' => $modelClass::where('status', 'rejected')->count(),
            'processing' => $modelClass::where('status', 'processing')->count(),
            'pre_approved' => $modelClass::where('status', 'pre-approved')->count(),
        ];
    }
}