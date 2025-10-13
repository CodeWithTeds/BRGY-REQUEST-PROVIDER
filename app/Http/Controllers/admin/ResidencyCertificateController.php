<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\CertificateOfResidencyRepository;
use App\Models\CertificateOfResidency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\ResidencyCertificateResource;
use App\Http\Resources\ResidencyCertificateDetailResource;
use App\Models\SupportingDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AdminListRequest;
use App\Services\AdminListService;

class ResidencyCertificateController extends Controller
{
    public function __construct(
        protected CertificateOfResidencyRepository $repo,
        protected AdminListService $listService,
    ) {
    }

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
        ]);
    }

    public function show(int $id)
    {
        $residency = $this->repo->getWithAllRelations($id);
        $data = (new ResidencyCertificateDetailResource($residency))->toArray(request());

        return Inertia::render('Admin/ResidencyCertificateView', [
            'certificate' => $data,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $residency = CertificateOfResidency::findOrFail($id);
        $residency->status = $validated['status'];
        // remarks will be saved if column exists; otherwise ignored by fillable
        if (array_key_exists('remarks', $validated)) {
            try {
                $residency->remarks = $validated['remarks'];
            } catch (\Throwable $e) {
                // ignore if column not present
            }
        }
        $residency->save();

        return redirect()->route('admin.residency-certificates.show', $id)
            ->with('success', 'Residency certificate status updated.');
    }

    /**
     * Remove the specified residency certificate from storage.
     */
    public function destroy($id)
    {
        $residency = CertificateOfResidency::findOrFail($id);
        $residency->delete();

        return redirect()->route('admin.residency-certificates')
            ->with('success', 'Residency certificate deleted.');
    }

    public function viewDocument($id, $docId)
    {
        $certificate = CertificateOfResidency::findOrFail($id);
        $document = SupportingDocument::where('id', $docId)
            ->where('certificate_of_residency_id', $certificate->id)
            ->firstOrFail();

        $path = $document->file_path ?? null;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = File::mimeType($fullPath) ?? 'application/octet-stream';
        $filename = basename($fullPath);
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}