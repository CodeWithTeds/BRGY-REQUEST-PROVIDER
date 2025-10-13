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
use Illuminate\Http\JsonResponse;
use App\Traits\JsonResponds;
use App\Http\Resources\BarangayClearanceResource;
use Illuminate\Support\Facades\Storage;

class BarangayClearanceController extends Controller
{
    use DocumentStreaming;
    use JsonResponds;
    public function __construct(
        protected BarangayClearanceRepository $clearanceRepo,
        protected AdminListService $listService,
    ) {
    }

    public function index(AdminListRequest $request): JsonResponse
    {
        $result = $this->listService->getList(
            $request,
            $this->clearanceRepo,
            BarangayClearance::class,
            fn($c) => (new BarangayClearanceResource($c))->toArray($request)
        );

        return response()->json([
            'clearances' => $result['items'],
            'stats' => $result['stats'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination'],
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $clearance = $this->clearanceRepo->getWithAllRelations($id);

        // Unwrap resource to a plain array so Vue gets direct fields
        $data = (new BarangayClearanceDetailResource($clearance))->toArray($request);
        return response()->json([
            'clearance' => $data,
            'stats' => $this->listService->getStats(BarangayClearance::class),
        ]);
    }

    public function viewDocument($id, $docId)
    {
        BarangayClearance::findOrFail($id);
        return $this->streamSupportingDocument((int) $id, (int) $docId, 'barangay_clearance_id');
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $clearance = BarangayClearance::findOrFail($id);
        $clearance->status = $validated['status'];
        $clearance->remarks = $validated['remarks'] ?? null;
        $clearance->save();

        return $this->jsonUpdated($clearance->id, [
            'status' => $clearance->status,
            'remarks' => $clearance->remarks,
        ], 'Clearance status updated.');
    }

    public function destroy($id): JsonResponse
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

        return $this->jsonDeleted($id, 'Barangay Clearance');
    }
}