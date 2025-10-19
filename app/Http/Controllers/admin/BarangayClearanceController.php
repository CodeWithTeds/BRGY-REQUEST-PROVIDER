<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\BarangayClearanceRepository;
use App\Models\BarangayClearance;
use App\Http\Resources\BarangayClearanceDetailResource;
use App\Traits\DocumentStreaming;
use Illuminate\Http\Request;
use App\Http\Requests\AdminListRequest;
use App\Services\AdminListService;
use App\Http\Resources\BarangayClearanceResource;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangayClearanceController extends Controller
{
    use DocumentStreaming;
    public function __construct(
        protected BarangayClearanceRepository $clearanceRepo,
        protected AdminListService $listService,
    ) {
    }

    public function index(AdminListRequest $request)
    {
        $result = $this->listService->getList(
            $request,
            $this->clearanceRepo,
            BarangayClearance::class,
            fn($c) => (new BarangayClearanceResource($c))->toArray($request)
        );

        return Inertia::render('Admin/BarangayClearances', [
            'clearances' => $result['items'],
            'stats' => $result['stats'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination'],
        ]);
    }

    public function show(Request $request, $id)
    {
        $clearance = $this->clearanceRepo->getWithAllRelations($id);

        // Unwrap resource to a plain array so Vue gets direct fields
        $data = (new BarangayClearanceDetailResource($clearance))->toArray($request);
        return Inertia::render('Admin/BarangayClearanceView', [
            'clearance' => $data,
            'routeGroup' => 'admin',
            'canApprove' => true,
        ]);
    }

    public function viewDocument($id, $docId)
    {
        BarangayClearance::findOrFail($id);
        return $this->streamSupportingDocument((int) $id, (int) $docId, 'barangay_clearance_id');
    }

    public function viewPdf($id)
    {
        $clearance = $this->clearanceRepo->getWithAllRelations((int) $id);

        if ($clearance->status !== 'approved') {
            return redirect()->route('admin.barangay-clearances.show', $id)
                ->with('error', 'PDF is available only after approval.');
        }

        $data = (new BarangayClearanceDetailResource($clearance))->toArray(request());
        $viewData = [
            'clearance' => $data,
            'logoPath' => public_path('images/brg.png'),
        ];

        $pdf = Pdf::setPaper('A4')->loadView('pdf.barangay_clearance', $viewData);
        return $pdf->stream('barangay-clearance-' . $clearance->id . '.pdf');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        // Use repository to ensure approval metadata is populated (issue/expiry dates, number)
        $this->clearanceRepo->updateStatus((int)$id, (string)$validated['status'], $validated['remarks'] ?? null);

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

        return redirect()->route('admin.barangay-clearances')
            ->with('success', 'Barangay Clearance deleted.');
    }
}