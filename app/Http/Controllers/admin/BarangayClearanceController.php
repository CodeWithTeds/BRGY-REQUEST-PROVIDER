<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\BarangayClearanceRepository;
use App\Models\BarangayClearance;
use App\Models\SupportingDocument;
use App\Http\Resources\BarangayClearanceDetailResource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangayClearanceController extends Controller
{
    public function __construct(protected BarangayClearanceRepository $clearanceRepo)
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
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));

        $paginator = $this->clearanceRepo->adminListWithFilters($filters, $page, $perPage);

        $clearances = collect($paginator->items())->map(function ($c) {
            $ap = $c->applicantProfile;
            $addr = $c->address;
            $barangay = $addr?->barangay?->name ?? null;

            $fullName = trim(implode(' ', array_filter([
                $ap->first_name ?? null,
                $ap->middle_name ?? null,
                $ap->last_name ?? null,
                $ap->suffix ?? null,
            ])));

            $addressLine = null;
            if ($addr) {
                $addressLine = trim(implode(', ', array_filter([
                    $addr->house_no,
                    $addr->street,
                    $addr->purok,
                ])));
            }

            return [
                'id' => $c->id,
                'full_name' => $fullName,
                // Format dates for clean display in the Admin list
                'application_date' => optional($c->application_date)?->toDateString(),
                'status' => $c->status,
                'created_at' => optional($c->created_at)?->toDateTimeString(),
                'updated_at' => optional($c->updated_at)?->toDateTimeString(),
                'gender' => $ap->gender ?? null,
                'citizenship' => $ap->citizenship ?? null,
                'contact_number' => $ap->contact_number ?? null,
                'barangay' => $barangay,
                'address_line' => $addressLine,
                'remarks' => $c->remarks ?? null,
            ];
        })->all();

        // Global stats from DB for accuracy on large datasets
        $stats = [
            'total' => BarangayClearance::count(),
            'approved' => BarangayClearance::where('status', 'approved')->count(),
            'pending' => BarangayClearance::where('status', 'pending')->count(),
            'rejected' => BarangayClearance::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BarangayClearances', [
            'clearances' => $clearances,
            'stats' => $stats,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $clearance = $this->clearanceRepo->getWithAllRelations($id);

        // Unwrap resource to a plain array so Vue gets direct fields
        $data = (new BarangayClearanceDetailResource($clearance))->toArray($request);

        $stats = [
            'total' => BarangayClearance::count(),
            'pending' => BarangayClearance::where('status', 'pending')->count(),
            'processing' => BarangayClearance::where('status', 'processing')->count(),
            'approved' => BarangayClearance::where('status', 'approved')->count(),
            'rejected' => BarangayClearance::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BarangayClearanceView', [
            'clearance' => $data,
            'stats' => $stats,
        ]);
    }

    public function viewDocument($id, $docId)
    {
        $clearance = BarangayClearance::findOrFail($id);
        $document = SupportingDocument::where('id', $docId)
            ->where('barangay_clearance_id', $clearance->id)
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

        $clearance = BarangayClearance::findOrFail($id);
        $clearance->status = $validated['status'];
        $clearance->remarks = $validated['remarks'] ?? null;
        $clearance->save();

        return redirect()->route('admin.barangay-clearances.show', $id)
            ->with('success', 'Clearance status updated.');
    }

    public function destroy($id)
    {
        $clearance = BarangayClearance::with(['supportingDocument', 'address'])->findOrFail($id);

        $doc = $clearance->supportingDocument;
        if ($doc) {
            $path = $doc->file_path;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $doc->delete();
        }

        if (method_exists($clearance, 'address') && $clearance->address) {
            $clearance->address()->delete();
        }

        $clearance->delete();

        return redirect()->route('admin.barangay-clearances')->with('success', 'Barangay Clearance deleted.');
    }
}