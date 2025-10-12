<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangayPermit;
use App\Models\SupportingDocument;
use App\Repositories\BussinessPermitRepository;
use App\Http\Resources\BusinessPermitResource;
use App\Http\Resources\BusinessPermitDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class BusinessPermitController extends Controller
{
    public function __construct(protected BussinessPermitRepository $permitRepo)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'name' => trim((string) $request->input('name', '')),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $collection = $this->permitRepo->adminListWithFilters($filters);

        // Unwrap resource collection to a plain array to avoid 'data' wrapper
        $permits = $collection->map(function ($permit) use ($request) {
            return (new BusinessPermitResource($permit))->toArray($request);
        })->all();

        $stats = [
            'total' => $collection->count(),
            'approved' => $collection->where('status', 'approved')->count(),
            'pending' => $collection->where('status', 'pending')->count(),
            'rejected' => $collection->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BusinessPermits', [
            'permits' => $permits,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $permit = $this->permitRepo->getWithAllRelations($id);

        $data = new BusinessPermitDetailResource($permit);

        $stats = [
            'total' => BarangayPermit::count(),
            'pending' => BarangayPermit::where('status', 'pending')->count(),
            'processing' => BarangayPermit::where('status', 'processing')->count(),
            'approved' => BarangayPermit::where('status', 'approved')->count(),
            'rejected' => BarangayPermit::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BusinessPermitView', [
            'permit' => $data,
            'stats' => $stats,
        ]);
    }

    public function viewDocument($id, $docId)
    {
        $permit = BarangayPermit::findOrFail($id);
        $document = SupportingDocument::where('id', $docId)
            ->where('barangay_permit_id', $permit->id)
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

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $permit = BarangayPermit::findOrFail($id);
        $permit->status = $validated['status'];
        $permit->remarks = $validated['remarks'] ?? null;
        $permit->save();

        return redirect()->route('admin.business-permits.show', $id)
            ->with('success', 'Permit status updated.');
    }

    public function destroy($id)
    {
        $permit = BarangayPermit::with(['supportingDocuments', 'addresses'])->findOrFail($id);

        foreach ($permit->supportingDocuments as $doc) {
            $path = $doc->file_path;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $doc->delete();
        }

        if (method_exists($permit, 'addresses')) {
            $permit->addresses()->delete();
        }

        $permit->delete();

        return redirect()->route('admin.business-permits')->with('success', 'Barangay Business Permit deleted.');
    }
}