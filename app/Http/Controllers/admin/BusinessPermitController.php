<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessPermitStatusRequest;
use App\Http\Requests\AdminListRequest;
use App\Repositories\BussinessPermitRepository;
use App\Http\Resources\BusinessPermitResource;
use App\Http\Resources\BusinessPermitDetailResource;
use App\Models\BarangayPermit;
use App\Services\AdminListService;
use App\Traits\DocumentStreaming;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $result = $this->listService->getList($request, $this->permitRepo, BarangayPermit::class, fn($p) => (new BusinessPermitResource($p))->toArray($request), 'business_permits_stats');
        return Inertia::render('Admin/BusinessPermits', [...$result, 'routeGroup' => 'admin']);
    }

    public function show(AdminListRequest $request, int $id)
    {
        $data  = (new BusinessPermitDetailResource($this->permitRepo->getWithAllRelations($id)))->toArray($request);
        $stats = $this->listService->getStatsCached(BarangayPermit::class, 'business_permits_stats');
        return Inertia::render('Admin/BusinessPermitView', ['permit' => $data, 'stats' => $stats, 'routeGroup' => 'admin', 'canApprove' => true]);
    }

    public function viewDocument(int $id, int $docId)
    {
        BarangayPermit::findOrFail($id);
        return $this->streamSupportingDocument($id, $docId, 'barangay_permit_id');
    }

    public function updateStatus(UpdateBusinessPermitStatusRequest $request, int $id)
    {
        $v = $request->validated();
        $this->permitRepo->updateStatus($id, $v['status'], $v['remarks'] ?? null);
        return redirect()->route('admin.business-permits.show', $id)->with('success', 'Permit status updated.');
    }

    public function destroy(int $id)
    {
        $this->permitRepo->deleteWithCascade($id);
        return redirect()->route('admin.business-permits')->with('success', 'Barangay Business Permit deleted.');
    }

    public function downloadPdf(AdminListRequest $request, int $id)
    {
        $permit = $this->permitRepo->getWithAllRelations($id);
        abort_if(!$permit || $permit->status !== 'approved', 302);
        $data = (new BusinessPermitDetailResource($permit))->toArray($request);
        return Pdf::loadView('pdf.business_permit', ['permit' => $data, 'logoPath' => public_path('images/brg.png')])->setPaper('A4')->download("barangay-business-permit-{$id}.pdf");
    }
}