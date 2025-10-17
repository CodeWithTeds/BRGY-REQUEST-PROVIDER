<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Repositories\BarangayClearanceRepository;
use App\Models\BarangayClearance;
use App\Http\Resources\BarangayClearanceDetailResource;
use App\Http\Resources\BarangayClearanceResource;
use App\Traits\DocumentStreaming;
use App\Http\Requests\AdminListRequest;
use App\Services\AdminListService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangayClearanceController extends Controller
{
    use DocumentStreaming;

    public function __construct(
        protected BarangayClearanceRepository $clearanceRepo,
        protected AdminListService $listService,
    ) {}

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
            'routeGroup' => 'staff',
            'canApprove' => false,
            'canDelete' => false,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $clearance = $this->clearanceRepo->getWithAllRelations($id);
        $data = (new BarangayClearanceDetailResource($clearance))->toArray($request);

        return Inertia::render('Admin/BarangayClearanceView', [
            'clearance' => $data,
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

        $clearance = BarangayClearance::findOrFail($id);
        $from = $clearance->status;

        $this->clearanceRepo->updateStatus($id, $validated['status'], $validated['remarks'] ?? null);

        \App\Services\ActivityLogger::log('status_updated', $clearance, ['from' => $from, 'to' => $validated['status']]);

        return redirect()->route('staff.barangay-clearances.show', $id)
            ->with('success', 'Clearance status updated.');
    }

    public function viewDocument(int $id, int $docId)
    {
        BarangayClearance::findOrFail($id);
        return $this->streamSupportingDocument($id, $docId, 'barangay_clearance_id');
    }
}