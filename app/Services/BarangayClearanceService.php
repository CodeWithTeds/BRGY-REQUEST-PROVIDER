<?php

namespace App\Services;

use App\Repositories\BarangayClearanceRepository;
use App\Models\BarangayClearance;
use App\Http\Resources\BarangayClearanceResource;
use App\Http\Resources\BarangayClearanceDetailResource;
use App\Http\Requests\AdminListRequest;
use App\Services\AdminListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarangayClearanceService
{
    public function __construct(
        protected BarangayClearanceRepository $repo,
        protected AdminListService $listService,
    ) {}

    /** Paginated index data for the Inertia admin page. */
    public function indexData(AdminListRequest $request): array
    {
        return $this->listService->getList(
            $request,
            $this->repo,
            BarangayClearance::class,
            fn($c) => (new BarangayClearanceResource($c))->toArray($request)
        );
    }

    /** Single clearance detail array for the show page. */
    public function detail(int $id, Request $request): array
    {
        $clearance = $this->repo->getWithAllRelations($id);
        return (new BarangayClearanceDetailResource($clearance))->toArray($request);
    }

    /** Update status for a clearance using the repository (handles metadata). */
    public function updateStatus(int $id, string $status, ?string $remarks): void
    {
        $this->repo->updateStatus($id, $status, $remarks);
    }

    /** Delete a clearance and cascade documents / address. */
    public function destroy(int $id): void
    {
        $clearance = BarangayClearance::with(['supportingDocument', 'address'])->findOrFail($id);

        if ($doc = $clearance->supportingDocument) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->delete();
        }

        if (method_exists($clearance, 'address') && $clearance->address) {
            $clearance->address()->delete();
        }

        $clearance->delete();
    }
}
