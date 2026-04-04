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
use Barryvdh\DomPDF\Facade\Pdf;

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
            fn($permit) => (new BusinessPermitResource($permit))->toArray($request),
            'business_permits_stats'
        );

        return Inertia::render('Admin/BusinessPermits', [
            'permits' => $result['items'],
            'stats' => $result['stats'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination'],
            'routeGroup' => 'admin',
        ]);
    }

    public function show(AdminListRequest $request, $id)
    {
        $permit = $this->permitRepo->getWithAllRelations($id);

        $data = (new BusinessPermitDetailResource($permit))->toArray($request);
        $stats = $this->listService->getStatsCached(BarangayPermit::class, 'business_permits_stats');

        return Inertia::render('Admin/BusinessPermitView', [
            'permit' => $data,
            'stats' => $stats,
            'routeGroup' => 'admin',
            'canApprove' => true,
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

    public function downloadPdf(AdminListRequest $request, $id)
    {
        $permit = $this->permitRepo->getWithAllRelations($id);
        if (!$permit || $permit->status !== 'approved') {
            return redirect()->route('admin.business-permits.show', $id)
                ->with('error', 'PDF is available only for approved permits.');
        }

        $data = (new BusinessPermitDetailResource($permit))->toArray($request);
        $logoPath = public_path('images/brg.png');

        $pdf = Pdf::loadView('pdf.business_permit', [
            'permit' => $data,
            'logoPath' => $logoPath,
        ])->setPaper('A4');

        $filename = sprintf('barangay-business-permit-%s.pdf', $permit->id);
        return $pdf->download($filename);
    }
}