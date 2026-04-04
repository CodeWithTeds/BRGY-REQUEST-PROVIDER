<?php

namespace App\Services;

use App\Models\CertificateOfResidency;
use App\Repositories\CertificateOfResidencyRepository;
use App\Http\Resources\ResidencyCertificateResource;
use App\Http\Resources\ResidencyCertificateDetailResource;
use App\Http\Requests\AdminListRequest;
use App\Services\AdminListService;
use Illuminate\Http\Request;

class ResidencyService
{
    public function __construct(
        protected CertificateOfResidencyRepository $repo,
        protected AdminListService $listService,
    ) {}

    /** Paginated index data for the Inertia page. */
    public function indexData(AdminListRequest $request): array
    {
        return $this->listService->getList(
            $request,
            $this->repo,
            CertificateOfResidency::class,
            fn($item) => (new ResidencyCertificateResource($item))->toArray($request)
        );
    }

    /** Single residency certificate detail array. */
    public function detail(int $id): array
    {
        $residency = $this->repo->getWithAllRelations($id);
        return (new ResidencyCertificateDetailResource($residency))->toArray(request());
    }

    /** Update status (and optionally remarks) for a residency certificate. */
    public function updateStatus(int $id, string $status, ?string $remarks): void
    {
        $residency = CertificateOfResidency::findOrFail($id);
        $residency->status = $status;
        try {
            if ($remarks !== null) {
                $residency->remarks = $remarks;
            }
        } catch (\Throwable) {}
        $residency->save();
    }

    /** Delete a residency certificate. */
    public function destroy(int $id): void
    {
        CertificateOfResidency::findOrFail($id)->delete();
    }
}
