<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Repositories\CertificateOfResidencyRepository;
use App\Models\CertificateOfResidency;
use App\Http\Resources\ResidencyCertificateResource;
use App\Http\Resources\ResidencyCertificateDetailResource;
use App\Http\Requests\AdminListRequest;
use App\Services\AdminListService;
use App\Traits\DocumentStreaming;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResidencyCertificateController extends Controller
{
    use DocumentStreaming;

    public function __construct(
        protected CertificateOfResidencyRepository $repo,
        protected AdminListService $listService,
    ) {}

    public function index(AdminListRequest $request)
    {
        $result = $this->listService->getList(
            $request,
            $this->repo,
            CertificateOfResidency::class,
            fn($item) => (new ResidencyCertificateResource($item))->toArray($request)
        );

        return Inertia::render('Admin/ResidencyCertificates', [
            'residencies' => $result['items'],
            'stats' => $result['stats'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination'],
            'routeGroup' => 'staff',
            'canDelete' => false,
        ]);
    }

    public function show(int $id)
    {
        $residency = $this->repo->getWithAllRelations($id);
        $data = (new ResidencyCertificateDetailResource($residency))->toArray(request());

        return Inertia::render('Admin/ResidencyCertificateView', [
            'certificate' => $data,
            'routeGroup' => 'staff',
            'canApprove' => false,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:processing,pre-approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        $certificate = CertificateOfResidency::findOrFail($id);
        $from = $certificate->status;

        $this->repo->updateStatus($id, $validated['status'], $validated['remarks'] ?? null);

        \App\Services\ActivityLogger::log('status_updated', $certificate, ['from' => $from, 'to' => $validated['status']]);

        return redirect()->route('staff.residency-certificates.show', $id)
            ->with('success', 'Residency certificate status updated.');
    }

    public function viewDocument(int $id, int $docId)
    {
        CertificateOfResidency::findOrFail($id);
        return $this->streamSupportingDocument($id, $docId, 'certificate_of_residency_id');
    }
}