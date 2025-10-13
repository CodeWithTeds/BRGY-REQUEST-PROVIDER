<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\CertificateOfResidencyRequest;
use App\Repositories\CertificateOfResidencyRepository;
use App\Models\SupportingDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        /** @var Request $request */
        $cert = $this->certificateOfResidencyRepository->createApplication($request->validated(), Auth::id());

        // Handle optional supporting documents
        $files = [
            'valid_government_id_document' => 'valid_government_id',
            'proof_of_residence_document' => 'proof_of_residence',
            'lease_contract_document' => 'lease_contract',
            'authorization_letter_document' => 'authorization_letter',
        ];

        foreach ($files as $field => $type) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('supporting-documents', 'public');
                SupportingDocument::create([
                    'user_id' => Auth::id(),
                    'certificate_of_residency_id' => $cert->id,
                    'document_type' => $type,
                    'file_path' => $path,
                    'verified' => false,
                ]);
            }
        }

        return redirect()->route('resident.certificate-of-residency.create');
    }
}
