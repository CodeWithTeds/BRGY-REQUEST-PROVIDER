<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDocumentStatusRequest;
use App\Http\Requests\AdminListRequest;
use App\Services\BarangayClearanceService;
use App\Traits\DocumentStreaming;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class BarangayClearanceController extends Controller
{
    use DocumentStreaming;

    public function __construct(protected BarangayClearanceService $service) {}

    public function index(AdminListRequest $request)
    {
        $result = $this->service->indexData($request);
        return Inertia::render('Admin/BarangayClearances', [...$result, 'routeGroup' => 'admin']);
    }

    public function show(AdminListRequest $request, int $id)
    {
        $data = $this->service->detail($id, $request);
        return Inertia::render('Admin/BarangayClearanceView', ['clearance' => $data, 'routeGroup' => 'admin', 'canApprove' => true]);
    }

    public function viewDocument(int $id, int $docId)
    {
        return $this->streamSupportingDocument($id, $docId, 'barangay_clearance_id');
    }

    public function viewPdf(int $id)
    {
        $data = $this->service->detail($id, request());
        abort_unless($data['status'] === 'approved', 302, redirect()->route('admin.barangay-clearances.show', $id)->with('error', 'PDF is available only after approval.'));
        return Pdf::setPaper('A4')->loadView('pdf.barangay_clearance', ['clearance' => $data, 'logoPath' => public_path('images/brg.png')])->stream("barangay-clearance-{$id}.pdf");
    }

    public function updateStatus(UpdateDocumentStatusRequest $request, int $id)
    {
        $v = $request->validated();
        $this->service->updateStatus($id, $v['status'], $v['remarks'] ?? null);
        return redirect()->route('admin.barangay-clearances.show', $id)->with('success', 'Clearance status updated.');
    }

    public function destroy(int $id)
    {
        $this->service->destroy($id);
        return redirect()->route('admin.barangay-clearances')->with('success', 'Barangay Clearance deleted.');
    }
}