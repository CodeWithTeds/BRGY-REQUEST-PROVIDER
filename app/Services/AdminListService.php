<?php

namespace App\Services;

use App\Http\Requests\AdminListRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminListService
{
    /**
     * Build a standardized admin list response using repository pagination and reusable filters.
     *
     * @param AdminListRequest $request Validated request providing filters and pagination
     * @param object $repository Repository with adminListWithFilters($filters, $page, $perPage)
     * @param string $modelClass Eloquent model class used for global stats
     * @param callable $toResource Maps a model item to a plain array suitable for the frontend
     * @param string|null $cacheKey Optional cache key for stats optimization
     * @return array{items: array, stats: array, filters: array, pagination: array}
     */
    public function getList(AdminListRequest $request, object $repository, string $modelClass, callable $toResource, ?string $cacheKey = null): array
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
        if ($cacheKey) {
            $stats = $this->getStatsCached($modelClass, $cacheKey);
        } else {
            $stats = $this->getStats($modelClass);
        }

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

    /**
     * Get global status counts for a given model class with caching.
     *
     * @param string $modelClass Eloquent model class
     * @param string $cacheKey Cache key prefix
     * @param int $ttl Cache TTL in seconds (default 600)
     * @return array{total:int,approved:int,pending:int,rejected:int,processing:int,pre_approved:int}
     */
    public function getStatsCached(string $modelClass, string $cacheKey, int $ttl = 600): array
    {
        return Cache::remember($cacheKey, $ttl, function () use ($modelClass) {
            $results = $modelClass::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $stats = [
                'approved' => $results['approved'] ?? 0,
                'pending' => $results['pending'] ?? 0,
                'rejected' => $results['rejected'] ?? 0,
                'processing' => $results['processing'] ?? 0,
                'pre_approved' => $results['pre-approved'] ?? 0,
            ];
            
            $stats['total'] = array_sum($stats);
            return $stats;
        });
    }
}