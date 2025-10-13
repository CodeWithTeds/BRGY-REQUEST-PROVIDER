<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndigencyCertificateDetailResource;
use App\Http\Resources\IndigencyCertificateResource;
use App\Repositories\CertificateOfIndigencyRepository;
use App\Traits\DocumentStreaming;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        // Transform items to plain arrays to match Vue expectation (Array, not ResourceCollection)
        $items = collect($result['items'])
            ->map(fn($item) => (new IndigencyCertificateResource($item))->toArray($request))
            ->all();

        return Inertia::render('Admin/IndigencyCertificates', [
            'indigencies' => $items,
            'stats' => $result['stats'],
            'filters' => $request->only(['name', 'status', 'date_from', 'date_to']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function show(Request $request, int $id)
    {
        $certificate = $this->repository->getWithAllRelations($id);

        // Unwrap resource into a plain array so Vue receives direct fields
        $data = (new IndigencyCertificateDetailResource($certificate))->toArray($request);

        return Inertia::render('Admin/IndigencyCertificateView', [
            'certificate' => $data,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $certificate = $this->repository->model()->findOrFail($id);
        $certificate->status = $validated['status'];
        if (array_key_exists('remarks', $validated)) {
            $certificate->remarks = $validated['remarks'];
        }
        $certificate->save();

        return redirect()->route('admin.indigency-certificates.show', $id)
            ->with('success', 'Indigency certificate status updated.');
    }

    public function destroy(int $id)
    {
        $certificate = $this->repository->model()->findOrFail($id);
        $certificate->delete();
        return redirect()->route('admin.indigency-certificates');
    }

    public function viewDocument(int $id, int $docId)
    {
        return $this->streamSupportingDocument($id, $docId, 'certificate_of_indigency_id');
    }
}