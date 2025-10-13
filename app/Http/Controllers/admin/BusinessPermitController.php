<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangayPermit;
use App\Repositories\BussinessPermitRepository;
use App\Http\Resources\BusinessPermitResource;
use App\Http\Resources\BusinessPermitDetailResource;
use App\Http\Requests\AdminListRequest;
use App\Http\Requests\Admin\UpdateBusinessPermitStatusRequest;
use App\Services\AdminListService;
use App\Traits\DocumentStreaming;
use Inertia\Inertia;

class BusinessPermitController extends Controller
{
    use DocumentStreaming;
    public function __construct(
        protected BussinessPermitRepository $permitRepo,
        protected AdminListService $listService,
    ) {
    }

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
        ]);
    }

    public function show(AdminListRequest $request, $id)
    {
        $permit = $this->permitRepo->getWithAllRelations($id);

        $data = (new BusinessPermitDetailResource($permit))->toArray($request);
        $stats = $this->listService->getStats(BarangayPermit::class);

        return Inertia::render('Admin/BusinessPermitView', [
            'permit' => $data,
            'stats' => $stats,
        ]);
    }

    public function viewDocument($id, $docId)
    {
        BarangayPermit::findOrFail($id);
        return $this->streamSupportingDocument((int) $id, (int) $docId, 'barangay_permit_id');
    }

    public function updateStatus(UpdateBusinessPermitStatusRequest $request, $id)
    {
        $v = $request->validated();
        $this->permitRepo->updateStatus($id, $v['status'], $v['remarks'] ?? null);

        return redirect()->route('admin.business-permits.show', $id)
            ->with('success', 'Permit status updated.');
    }

    public function destroy($id)
    {
        $this->permitRepo->deleteWithCascade($id);

        return redirect()->route('admin.business-permits')->with('success', 'Barangay Business Permit deleted.');
    }
}