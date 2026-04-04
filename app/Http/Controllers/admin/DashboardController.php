<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        return Inertia::render('Admin/Dashboard', [
            'counts' => [
                'permits' => $data['counts']['permits'],
                'clearances' => $data['counts']['clearances'],
                'residencies' => $data['counts']['residencies'],
            ],
            'recentApplications' => $data['recentApplications'],
            'statusDistribution' => $data['statusDistribution'],
            'typeBreakdown' => $data['typeBreakdown'],
            'timeSeries' => $data['timeSeries'],
            'kpis' => $data['kpis'],
            'quickActions' => $data['quickActions'],
        ]);
    }
}
