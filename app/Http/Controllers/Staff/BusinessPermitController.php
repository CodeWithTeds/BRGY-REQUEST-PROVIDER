<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessPermitResource;
use App\Http\Resources\BusinessPermitDetailResource;
use App\Models\BarangayPermit;
use App\Repositories\BussinessPermitRepository;
use App\Services\AdminListService;
use App\Traits\DocumentStreaming;
use App\Http\Requests\AdminListRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessPermitController extends Controller
{
    use DocumentStreaming;

    public function __construct(
        protected BussinessPermitRepository $permitRepo,
        protected AdminListService $listService,
    ) {}

    public function index(AdminListRequest $request)
    {
        $result = $this->listService->getList(
            $request,
            $this->permitRepo,
            BarangayPermit::class,
            fn($permit) => (new BusinessPermitResource($permit))->toArray($request)
        );

        return Inertia::render('Admin/BusinessPermits', [
            'permits' => $result['items'],
            'stats' => $result['stats'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination'],
            // Staff context for UI routing and permissions
            'routeGroup' => 'staff',
            'canApprove' => false,
            'canDelete' => false,
        ]);
    }

    public function show(AdminListRequest $request, int $id)
    {
        $permit = $this->permitRepo->getWithAllRelations($id);

        $data = (new BusinessPermitDetailResource($permit))->toArray($request);
        $stats = $this->listService->getStats(BarangayPermit::class);

        return Inertia::render('Admin/BusinessPermitView', [
            'permit' => $data,
            'stats' => $stats,
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

        $permit = BarangayPermit::findOrFail($id);
        $from = $permit->status;

        $this->permitRepo->updateStatus($id, $validated['status'], $validated['remarks'] ?? null);

        \App\Services\ActivityLogger::log('status_updated', $permit, ['from' => $from, 'to' => $validated['status']]);

        return redirect()->route('staff.business-permits.show', $id)
            ->with('success', 'Permit status updated.');
    }

    public function viewDocument(int $id, int $docId)
    {
        BarangayPermit::findOrFail($id);
        return $this->streamSupportingDocument($id, $docId, 'barangay_permit_id');
    }
}