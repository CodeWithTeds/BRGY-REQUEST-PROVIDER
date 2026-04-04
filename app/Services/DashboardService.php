<?php

namespace App\Services;

use App\Models\BarangayPermit;
use App\Models\BarangayClearance;
use App\Models\CertificateOfResidency;
use App\Models\CertificateOfIndigency;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all dashboard statistics and data.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        $cacheKey = 'admin_dashboard_stats_';
        $ttl = 600; // 10 minutes

        $counts = $this->getCounts($cacheKey, $ttl);
        $statusDistribution = $this->getStatusDistribution($counts);
        $typeBreakdown = $this->getTypeBreakdown($counts);
        $timeSeries = $this->getTimeSeries($cacheKey, $ttl);
        $kpis = $this->getKPIs($typeBreakdown, $statusDistribution);
        $recentApplications = $this->getRecentApplications($cacheKey, $ttl);
        $quickActions = $this->getQuickActions();

        return [
            'counts' => $counts,
            'statusDistribution' => $statusDistribution,
            'typeBreakdown' => $typeBreakdown,
            'timeSeries' => $timeSeries,
            'kpis' => $kpis,
            'recentApplications' => $recentApplications,
            'quickActions' => $quickActions,
        ];
    }

    /**
     * Get counts for all application types grouped by status.
     */
    protected function getCounts(string $cacheKey, int $ttl): array
    {
        return Cache::remember($cacheKey . 'counts', $ttl, function () {
            $getCounts = function ($model) {
                $results = $model::select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();
                
                return [
                    'pending' => $results['pending'] ?? 0,
                    'processing' => $results['processing'] ?? 0,
                    'approved' => $results['approved'] ?? 0,
                    'rejected' => $results['rejected'] ?? 0,
                    'pre_approved' => $results['pre-approved'] ?? 0,
                ];
            };

            return [
                'permits' => $getCounts(BarangayPermit::class),
                'clearances' => $getCounts(BarangayClearance::class),
                'residencies' => $getCounts(CertificateOfResidency::class),
                'indigencies' => $getCounts(CertificateOfIndigency::class),
            ];
        });
    }

    /**
     * Aggregate distribution across key statuses.
     */
    protected function getStatusDistribution(array $counts): array
    {
        return [
            'pending' => $counts['permits']['pending'] + $counts['clearances']['pending'] + $counts['residencies']['pending'] + $counts['indigencies']['pending'],
            'processing' => $counts['permits']['processing'] + $counts['clearances']['processing'] + $counts['residencies']['processing'] + $counts['indigencies']['processing'],
            'approved' => $counts['permits']['approved'] + $counts['clearances']['approved'] + $counts['residencies']['approved'] + $counts['indigencies']['approved'],
            'rejected' => $counts['permits']['rejected'] + $counts['clearances']['rejected'] + $counts['residencies']['rejected'] + $counts['indigencies']['rejected'],
        ];
    }

    /**
     * Calculate totals per type.
     */
    protected function getTypeBreakdown(array $counts): array
    {
        return [
            'permits' => array_sum($counts['permits']),
            'clearances' => array_sum($counts['clearances']),
            'residencies' => array_sum($counts['residencies']),
            'indigencies' => array_sum($counts['indigencies']),
        ];
    }

    /**
     * Build time-series data: daily (last 14 days) and weekly (last 8 weeks).
     */
    protected function getTimeSeries(string $cacheKey, int $ttl): array
    {
        return Cache::remember($cacheKey . 'time_series', $ttl, function () {
            $todayLocal = Carbon::now('Asia/Manila')->startOfDay();
            
            // Daily Data
            $dailyLabels = [];
            $dailyValues = [];
            for ($i = 13; $i >= 0; $i--) {
                $dayLocal = $todayLocal->copy()->subDays($i);
                $label = $dayLocal->format('M d');
                $startUtc = $dayLocal->copy()->startOfDay()->setTimezone('UTC');
                $endUtc = $dayLocal->copy()->endOfDay()->setTimezone('UTC');

                $count = 0;
                $count += BarangayPermit::whereBetween('created_at', [$startUtc, $endUtc])->count();
                $count += BarangayClearance::whereBetween('created_at', [$startUtc, $endUtc])->count();
                $count += CertificateOfResidency::whereBetween('created_at', [$startUtc, $endUtc])->count();
                $count += CertificateOfIndigency::whereBetween('created_at', [$startUtc, $endUtc])->count();

                $dailyLabels[] = $label;
                $dailyValues[] = $count;
            }

            // Weekly Data
            $weeklyLabels = [];
            $weeklyValues = [];
            $startOfWeekLocal = $todayLocal->copy()->startOfWeek(Carbon::MONDAY);
            for ($w = 7; $w >= 0; $w--) {
                $weekStartLocal = $startOfWeekLocal->copy()->subWeeks($w);
                $weekEndLocal = $weekStartLocal->copy()->endOfWeek(Carbon::SUNDAY);
                $label = $weekStartLocal->format('M d') . ' - ' . $weekEndLocal->format('M d');
                $startUtc = $weekStartLocal->copy()->startOfDay()->setTimezone('UTC');
                $endUtc = $weekEndLocal->copy()->endOfDay()->setTimezone('UTC');

                $count = 0;
                $count += BarangayPermit::whereBetween('created_at', [$startUtc, $endUtc])->count();
                $count += BarangayClearance::whereBetween('created_at', [$startUtc, $endUtc])->count();
                $count += CertificateOfResidency::whereBetween('created_at', [$startUtc, $endUtc])->count();
                $count += CertificateOfIndigency::whereBetween('created_at', [$startUtc, $endUtc])->count();

                $weeklyLabels[] = $label;
                $weeklyValues[] = $count;
            }

            return [
                'daily' => ['labels' => $dailyLabels, 'values' => $dailyValues],
                'weekly' => ['labels' => $weeklyLabels, 'values' => $weeklyValues],
            ];
        });
    }

    /**
     * Calculate KPI metrics.
     */
    protected function getKPIs(array $typeBreakdown, array $statusDistribution): array
    {
        $todayLocal = Carbon::now('Asia/Manila')->startOfDay();
        $totalRequests = array_sum($typeBreakdown);

        return [
            'totalRequests' => $totalRequests,
            'pending' => $statusDistribution['pending'],
            'approved' => $statusDistribution['approved'],
            'todaysAppointments' => Appointment::query()
                ->whereBetween('appointment_at', [
                    $todayLocal->copy()->setTimezone('UTC'),
                    $todayLocal->copy()->endOfDay()->setTimezone('UTC'),
                ])
                ->where('status', 'scheduled')
                ->count(),
        ];
    }

    /**
     * Get recent applications (latest 8 across all types).
     */
    protected function getRecentApplications(string $cacheKey, int $ttl): array
    {
        return Cache::remember($cacheKey . 'recent', $ttl, function () {
            $permits = BarangayPermit::select('id', 'status', 'created_at', DB::raw("'Permit' as type"));
            $clearances = BarangayClearance::select('id', 'status', 'created_at', DB::raw("'Clearance' as type"));
            $residencies = CertificateOfResidency::select('id', 'status', 'created_at', DB::raw("'Residency' as type"));
            $indigencies = CertificateOfIndigency::select('id', 'status', 'created_at', DB::raw("'Indigency' as type"));

            return $permits->unionAll($clearances)
                ->unionAll($residencies)
                ->unionAll($indigencies)
                ->orderByDesc('created_at')
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => $item->type,
                        'status' => $item->status,
                        'application_date' => optional($item->created_at)->toDateString(),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get quick action links.
     */
    protected function getQuickActions(): array
    {
        return [
            ['label' => 'Manage Permits', 'href' => route('admin.business-permits')],
            ['label' => 'Manage Clearances', 'href' => route('admin.barangay-clearances')],
            ['label' => 'Manage Residencies', 'href' => route('admin.residency-certificates')],
            ['label' => 'Manage Indigencies', 'href' => route('admin.indigency-certificates')],
            ['label' => 'Clerks', 'href' => route('admin.clerks')],
        ];
    }
}
