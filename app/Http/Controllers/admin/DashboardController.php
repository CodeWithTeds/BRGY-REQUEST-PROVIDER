<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangayPermit;
use App\Models\BarangayClearance;
use App\Models\CertificateOfResidency;
use App\Models\CertificateOfIndigency;
use App\Models\Appointment;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Per-type status counts (include pre-approved for completeness)
        $counts = [
            'permits' => [
                'pending' => BarangayPermit::where('status', 'pending')->count(),
                'processing' => BarangayPermit::where('status', 'processing')->count(),
                'approved' => BarangayPermit::where('status', 'approved')->count(),
                'rejected' => BarangayPermit::where('status', 'rejected')->count(),
                'pre_approved' => BarangayPermit::where('status', 'pre-approved')->count(),
            ],
            'clearances' => [
                'pending' => BarangayClearance::where('status', 'pending')->count(),
                'processing' => BarangayClearance::where('status', 'processing')->count(),
                'approved' => BarangayClearance::where('status', 'approved')->count(),
                'rejected' => BarangayClearance::where('status', 'rejected')->count(),
                'pre_approved' => BarangayClearance::where('status', 'pre-approved')->count(),
            ],
            'residencies' => [
                'pending' => CertificateOfResidency::where('status', 'pending')->count(),
                'processing' => CertificateOfResidency::where('status', 'processing')->count(),
                'approved' => CertificateOfResidency::where('status', 'approved')->count(),
                'rejected' => CertificateOfResidency::where('status', 'rejected')->count(),
                'pre_approved' => CertificateOfResidency::where('status', 'pre-approved')->count(),
            ],
            'indigencies' => [
                'pending' => CertificateOfIndigency::where('status', 'pending')->count(),
                'processing' => CertificateOfIndigency::where('status', 'processing')->count(),
                'approved' => CertificateOfIndigency::where('status', 'approved')->count(),
                'rejected' => CertificateOfIndigency::where('status', 'rejected')->count(),
                'pre_approved' => CertificateOfIndigency::where('status', 'pre-approved')->count(),
            ],
        ];

        // Aggregate distribution across key statuses for donut chart
        $statusDistribution = [
            'pending' => $counts['permits']['pending'] + $counts['clearances']['pending'] + $counts['residencies']['pending'] + $counts['indigencies']['pending'],
            'processing' => $counts['permits']['processing'] + $counts['clearances']['processing'] + $counts['residencies']['processing'] + $counts['indigencies']['processing'],
            'approved' => $counts['permits']['approved'] + $counts['clearances']['approved'] + $counts['residencies']['approved'] + $counts['indigencies']['approved'],
            'rejected' => $counts['permits']['rejected'] + $counts['clearances']['rejected'] + $counts['residencies']['rejected'] + $counts['indigencies']['rejected'],
        ];

        // Totals per type
        $typeBreakdown = [
            'permits' => array_sum($counts['permits']),
            'clearances' => array_sum($counts['clearances']),
            'residencies' => array_sum($counts['residencies']),
            'indigencies' => array_sum($counts['indigencies']),
        ];

        // Build time-series: daily (last 14 days) and weekly (last 8 weeks)
        $todayLocal = Carbon::now('Asia/Manila')->startOfDay();
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

        $timeSeries = [
            'daily' => ['labels' => $dailyLabels, 'values' => $dailyValues],
            'weekly' => ['labels' => $weeklyLabels, 'values' => $weeklyValues],
        ];

        // KPI cards
        $totalRequests = array_sum($typeBreakdown);
        $kpis = [
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

        // Quick actions
        $quickActions = [
            ['label' => 'Manage Permits', 'href' => route('admin.business-permits')],
            ['label' => 'Manage Clearances', 'href' => route('admin.barangay-clearances')],
            ['label' => 'Manage Residencies', 'href' => route('admin.residency-certificates')],
            ['label' => 'Manage Indigencies', 'href' => route('admin.indigency-certificates')],
            ['label' => 'Clerks', 'href' => route('admin.clerks')],
        ];

        // Recent applications (latest 8 across all types)
        $recent = [];
        foreach (BarangayPermit::orderByDesc('created_at')->limit(8)->get(['id', 'status', 'created_at']) as $p) {
            $recent[] = [
                'id' => $p->id,
                'type' => 'Permit',
                'status' => $p->status,
                'application_date' => optional($p->created_at)->toDateString(),
            ];
        }
        foreach (BarangayClearance::orderByDesc('created_at')->limit(8)->get(['id', 'status', 'created_at']) as $c) {
            $recent[] = [
                'id' => $c->id,
                'type' => 'Clearance',
                'status' => $c->status,
                'application_date' => optional($c->created_at)->toDateString(),
            ];
        }
        foreach (CertificateOfResidency::orderByDesc('created_at')->limit(8)->get(['id', 'status', 'created_at']) as $r) {
            $recent[] = [
                'id' => $r->id,
                'type' => 'Residency',
                'status' => $r->status,
                'application_date' => optional($r->created_at)->toDateString(),
            ];
        }
        foreach (CertificateOfIndigency::orderByDesc('created_at')->limit(8)->get(['id', 'status', 'created_at']) as $i) {
            $recent[] = [
                'id' => $i->id,
                'type' => 'Indigency',
                'status' => $i->status,
                'application_date' => optional($i->created_at)->toDateString(),
            ];
        }

        // Sort combined by application_date desc and take top 8
        usort($recent, function ($a, $b) {
            return strcmp(($b['application_date'] ?? ''), ($a['application_date'] ?? ''));
        });
        $recentApplications = array_slice($recent, 0, 8);

        return Inertia::render('Admin/Dashboard', [
            'counts' => [
                'permits' => $counts['permits'],
                'clearances' => $counts['clearances'],
                'residencies' => $counts['residencies'],
            ],
            'recentApplications' => $recentApplications,
            'statusDistribution' => $statusDistribution,
            'typeBreakdown' => $typeBreakdown,
            'timeSeries' => $timeSeries,
            'kpis' => $kpis,
            'quickActions' => $quickActions,
        ]);
    }
}