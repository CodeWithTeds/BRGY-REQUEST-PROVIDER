<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDocumentStatusRequest;
use App\Services\IndigencyService;
use App\Traits\DocumentStreaming;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndigencyCertificateController extends Controller
{
    use DocumentStreaming;

    public function __construct(protected IndigencyService $service) {}

    public function index(Request $request)
    {
        $data = $this->service->indexData($request->only(['name', 'status', 'date_from', 'date_to']), $request);
        return Inertia::render('Admin/IndigencyCertificates', [...$data, 'routeGroup' => 'admin', 'canDelete' => false]);
    }

    public function show(Request $request, int $id)
    {
        $data = $this->service->detail($id, $request);
        return Inertia::render('Admin/IndigencyCertificateView', ['certificate' => $data, 'routeGroup' => 'admin', 'canApprove' => true]);
    }

    public function updateStatus(UpdateDocumentStatusRequest $request, int $id)
    {
        $v = $request->validated();
        $this->service->updateStatus($id, $v['status'], $v['remarks'] ?? null);
        return redirect()->route('admin.indigency-certificates.show', $id)->with('success', 'Indigency certificate status updated.');
    }

    public function destroy(int $id)
    {
        $this->service->destroy($id);
        return redirect()->route('admin.indigency-certificates');
    }

    public function viewDocument(int $id, int $docId)
    {
        return $this->streamSupportingDocument($id, $docId, 'certificate_of_indigency_id');
    }

    public function viewPdf(Request $request, int $id)
    {
        $data = $this->service->detail($id, $request);
        abort_unless($data['status'] === 'approved', 302);
        return Pdf::setPaper('A4')->loadView('pdf.certificate_of_indigency', ['certificate' => $data, 'logoPath' => public_path('images/brg.png')])->stream("certificate-of-indigency-{$id}.pdf", ['Attachment' => false]);
    }
}
