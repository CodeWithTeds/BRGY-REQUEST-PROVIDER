<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDocumentStatusRequest;
use App\Http\Requests\AdminListRequest;
use App\Services\ResidencyService;
use App\Traits\DocumentStreaming;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class ResidencyCertificateController extends Controller
{
    use DocumentStreaming;

    public function __construct(protected ResidencyService $service) {}

    public function index(AdminListRequest $request)
    {
        $result = $this->service->indexData($request);
        return Inertia::render('Admin/ResidencyCertificates', [...$result, 'routeGroup' => 'admin', 'canDelete' => true]);
    }

    public function show(int $id)
    {
        $data = $this->service->detail($id);
        return Inertia::render('Admin/ResidencyCertificateView', ['certificate' => $data, 'routeGroup' => 'admin', 'canApprove' => true]);
    }

    public function updateStatus(UpdateDocumentStatusRequest $request, int $id)
    {
        $v = $request->validated();
        $this->service->updateStatus($id, $v['status'], $v['remarks'] ?? null);
        return redirect()->route('admin.residency-certificates.show', $id)->with('success', 'Residency certificate status updated.');
    }

    public function destroy(int $id)
    {
        $this->service->destroy($id);
        return redirect()->route('admin.residency-certificates')->with('success', 'Residency certificate deleted.');
    }

    public function viewDocument(int $id, int $docId)
    {
        return $this->streamSupportingDocument($id, $docId, 'certificate_of_residency_id');
    }

    public function downloadPdf(int $id)
    {
        $data = $this->service->detail($id);
        abort_unless($data['status'] === 'approved', 302);
        return Pdf::setPaper('A4')->loadView('pdf.certificate_of_residency', ['certificate' => $data, 'logoPath' => public_path('images/brg.png')])->download("certificate-of-residency-{$id}.pdf");
    }
}
