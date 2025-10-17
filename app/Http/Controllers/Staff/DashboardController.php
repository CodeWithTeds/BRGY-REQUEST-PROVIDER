<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\BarangayPermit;
use App\Models\BarangayClearance;
use App\Models\CertificateOfResidency;
use App\Models\CertificateOfIndigency;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'barangay_permits' => [
                'pending' => BarangayPermit::where('status', 'pending')->count(),
                'processing' => BarangayPermit::where('status', 'processing')->count(),
                'approved' => BarangayPermit::where('status', 'approved')->count(),
                'rejected' => BarangayPermit::where('status', 'rejected')->count(),
                'pre_approved' => BarangayPermit::where('status', 'pre-approved')->count(),
            ],
            'barangay_clearances' => [
                'pending' => BarangayClearance::where('status', 'pending')->count(),
                'processing' => BarangayClearance::where('status', 'processing')->count(),
                'approved' => BarangayClearance::where('status', 'approved')->count(),
                'rejected' => BarangayClearance::where('status', 'rejected')->count(),
                'pre_approved' => BarangayClearance::where('status', 'pre-approved')->count(),
            ],
            'residency_certificates' => [
                'pending' => CertificateOfResidency::where('status', 'pending')->count(),
                'processing' => CertificateOfResidency::where('status', 'processing')->count(),
                'approved' => CertificateOfResidency::where('status', 'approved')->count(),
                'rejected' => CertificateOfResidency::where('status', 'rejected')->count(),
                'pre_approved' => CertificateOfResidency::where('status', 'pre-approved')->count(),
            ],
            'indigency_certificates' => [
                'pending' => CertificateOfIndigency::where('status', 'pending')->count(),
                'processing' => CertificateOfIndigency::where('status', 'processing')->count(),
                'approved' => CertificateOfIndigency::where('status', 'approved')->count(),
                'rejected' => CertificateOfIndigency::where('status', 'rejected')->count(),
                'pre_approved' => CertificateOfIndigency::where('status', 'pre-approved')->count(),
            ],
        ];

        return Inertia::render('Staff/Dashboard', [
            'stats' => $stats,
            'auth_user' => Auth::user(),
        ]);
    }
}