<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\CertificateOfIndigencyRequest;
use App\Repositories\CertificateOfIndigencyRepository;
use App\Models\SupportingDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificateOfIndigencyController extends Controller
{
    public function __construct(
        protected CertificateOfIndigencyRepository $repository
    ) {}

    public function create()
    {
        $pending = $this->repository->getPending(Auth::id());

        if ($pending) {
            return Inertia::render('Resident/CertificateOfIndigency/Pending', [
                'indigency' => $pending,
            ]);
        }

        return Inertia::render('Resident/CertificateOfIndigency/Create');
    }

    public function store(CertificateOfIndigencyRequest $request)
    {
        /** @var Request $request */
        $cert = $this->repository->createApplication($request->validated(), Auth::id());

        // Require and store only one valid government ID
        if ($request->hasFile('valid_government_id_document')) {
            $path = $request->file('valid_government_id_document')->store('supporting-documents', 'public');
            SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_indigency_id' => $cert->id,
                'document_type' => 'valid_government_id',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        return redirect()->route('resident.certificate-of-indigency.create');
    }
}