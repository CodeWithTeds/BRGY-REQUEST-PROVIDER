<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Repositories\CertificateOfIndigencyRepository;
use App\Http\Resources\IndigencyCertificateResource;
use App\Http\Resources\IndigencyCertificateDetailResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\DocumentStreaming;

class IndigencyCertificateController extends Controller
{
    use DocumentStreaming;

    public function __construct(
        protected CertificateOfIndigencyRepository $repository
    ) {}

    public function index(Request $request)
    {
        $result = $this->repository->listWithFilters(
            $request->only(['name', 'status', 'date_from', 'date_to']),
            10
        );

        $items = collect($result['items'])
            ->map(fn($item) => (new IndigencyCertificateResource($item))->toArray($request))
            ->all();

        return Inertia::render('Admin/IndigencyCertificates', [
            'indigencies' => $items,
            'stats' => $result['stats'],
            'filters' => $request->only(['name', 'status', 'date_from', 'date_to']),
            'pagination' => $result['pagination'],
            'routeGroup' => 'staff',
            'canDelete' => false,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $certificate = $this->repository->getWithAllRelations($id);
        $data = (new IndigencyCertificateDetailResource($certificate))->toArray($request);

        return Inertia::render('Admin/IndigencyCertificateView', [
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

        $certificate = $this->repository->model()->findOrFail($id);
        $from = $certificate->status;
        $certificate->status = $validated['status'];
        if (array_key_exists('remarks', $validated)) {
            $certificate->remarks = $validated['remarks'];
        }
        $certificate->save();

        \App\Services\ActivityLogger::log('status_updated', $certificate, ['from' => $from, 'to' => $validated['status']]);

        return redirect()->route('staff.indigency-certificates.show', $id)
            ->with('success', 'Certificate status updated.');
    }

    public function viewDocument(int $id, int $docId)
    {
        return $this->streamSupportingDocument($id, $docId, 'certificate_of_indigency_id');
    }
}