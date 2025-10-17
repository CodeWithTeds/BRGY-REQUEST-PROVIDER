<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\BarangayPermit;
use App\Models\BarangayClearance;
use App\Models\CertificateOfResidency;
use App\Models\CertificateOfIndigency;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Basic status counts per request type
        $counts = [
            'permits' => [
                'pending' => BarangayPermit::where('user_id', $userId)->where('status', 'pending')->count(),
                'approved' => BarangayPermit::where('user_id', $userId)->where('status', 'approved')->count(),
                'rejected' => BarangayPermit::where('user_id', $userId)->where('status', 'rejected')->count(),
                'processing' => BarangayPermit::where('user_id', $userId)->where('status', 'processing')->count(),
                'pre_approved' => BarangayPermit::where('user_id', $userId)->where('status', 'pre-approved')->count(),
            ],
            'clearances' => [
                'pending' => BarangayClearance::where('user_id', $userId)->where('status', 'pending')->count(),
                'approved' => BarangayClearance::where('user_id', $userId)->where('status', 'approved')->count(),
                'rejected' => BarangayClearance::where('user_id', $userId)->where('status', 'rejected')->count(),
                'processing' => BarangayClearance::where('user_id', $userId)->where('status', 'processing')->count(),
                'pre_approved' => BarangayClearance::where('user_id', $userId)->where('status', 'pre-approved')->count(),
            ],
            'residencies' => [
                'pending' => CertificateOfResidency::where('user_id', $userId)->where('status', 'pending')->count(),
                'approved' => CertificateOfResidency::where('user_id', $userId)->where('status', 'approved')->count(),
                'rejected' => CertificateOfResidency::where('user_id', $userId)->where('status', 'rejected')->count(),
                'processing' => CertificateOfResidency::where('user_id', $userId)->where('status', 'processing')->count(),
                'pre_approved' => CertificateOfResidency::where('user_id', $userId)->where('status', 'pre-approved')->count(),
            ],
            'indigencies' => [
                'pending' => CertificateOfIndigency::where('user_id', $userId)->where('status', 'pending')->count(),
                'approved' => CertificateOfIndigency::where('user_id', $userId)->where('status', 'approved')->count(),
                'rejected' => CertificateOfIndigency::where('user_id', $userId)->where('status', 'rejected')->count(),
                'processing' => CertificateOfIndigency::where('user_id', $userId)->where('status', 'processing')->count(),
                'pre_approved' => CertificateOfIndigency::where('user_id', $userId)->where('status', 'pre-approved')->count(),
            ],
        ];

        // Aggregate recent applications (latest 8 across all types)
        $recent = [];

        $recentPermits = BarangayPermit::where('user_id', $userId)
            ->orderByDesc('application_date')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'status', 'application_date', 'updated_at']);
        foreach ($recentPermits as $p) {
            $recent[] = [
                'id' => $p->id,
                'type' => 'Barangay Permit',
                'status' => $p->status,
                'application_date' => $p->application_date ?? optional($p->updated_at)->toDateString(),
                'route' => route('barangay-permit.pdf', $p->id),
            ];
        }

        $recentClearances = BarangayClearance::where('user_id', $userId)
            ->orderByDesc('application_date')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'status', 'application_date', 'updated_at']);
        foreach ($recentClearances as $c) {
            $recent[] = [
                'id' => $c->id,
                'type' => 'Barangay Clearance',
                'status' => $c->status,
                'application_date' => $c->application_date ?? optional($c->updated_at)->toDateString(),
                'route' => route('barangay-clearance.show', $c->id),
            ];
        }

        $recentResidencies = CertificateOfResidency::where('user_id', $userId)
            ->orderByDesc('application_date')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'status', 'application_date', 'updated_at']);
        foreach ($recentResidencies as $r) {
            $recent[] = [
                'id' => $r->id,
                'type' => 'Certificate of Residency',
                'status' => $r->status,
                'application_date' => $r->application_date ?? optional($r->updated_at)->toDateString(),
                'route' => route('resident.certificate-of-residency.create'),
            ];
        }

        $recentIndigencies = CertificateOfIndigency::where('user_id', $userId)
            ->orderByDesc('application_date')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'status', 'application_date', 'updated_at']);
        foreach ($recentIndigencies as $i) {
            $recent[] = [
                'id' => $i->id,
                'type' => 'Certificate of Indigency',
                'status' => $i->status,
                'application_date' => $i->application_date ?? optional($i->updated_at)->toDateString(),
                'route' => route('resident.certificate-of-indigency.create'),
            ];
        }

        // Sort by application_date desc and take top 8
        usort($recent, function ($a, $b) {
            return strcmp(($b['application_date'] ?? ''), ($a['application_date'] ?? ''));
        });
        $recentApplications = array_slice($recent, 0, 8);

        // Applicant profile for personalization (if exists)
        $ap = Auth::user()->applicantProfile;
        $apData = $ap ? [
            'first_name' => $ap->first_name,
            'middle_name' => $ap->middle_name,
            'last_name' => $ap->last_name,
            'suffix' => $ap->suffix,
        ] : null;

        return Inertia::render('Resident/Home', [
            'counts' => $counts,
            'recentApplications' => $recentApplications,
            'applicantProfile' => $apData,
        ]);
    }
}