<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\CertificateOfResidencyRequest;
use App\Repositories\CertificateOfResidencyRepository;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CertificateOfResidencyController extends Controller
{
    public function __construct(private readonly CertificateOfResidencyRepository $certificateOfResidencyRepository)
    {
    }

    public function create()
    {
        $pending = $this->certificateOfResidencyRepository->getPending(Auth::id());

        if ($pending) {
            return Inertia::render('Resident/CertificateOfResidency/Pending', [
                'residency' => $pending,
            ]);
        }

        return Inertia::render('Resident/CertificateOfResidency/Create');
    }

    public function store(CertificateOfResidencyRequest $request)
    {
        $this->certificateOfResidencyRepository->createApplication($request->validated(), Auth::id());

        return redirect()->route('resident.certificate-of-residency.create');
    }
}
