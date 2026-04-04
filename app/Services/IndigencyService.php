<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Repositories\CertificateOfIndigencyRepository;
use App\Http\Resources\IndigencyCertificateResource;
use App\Http\Resources\IndigencyCertificateDetailResource;
use Illuminate\Http\Request;

class IndigencyService
{
    public function __construct(protected CertificateOfIndigencyRepository $repo) {}

    /** Paginated index data shaped for the Inertia page. */
    public function indexData(array $filters, Request $request): array
    {
        $result = $this->repo->listWithFilters($filters, 10);

        $items = collect($result['items'])
            ->map(fn($item) => (new IndigencyCertificateResource($item))->toArray($request))
            ->all();

        return [
            'indigencies' => $items,
            'stats'       => $result['stats'],
            'filters'     => $filters,
            'pagination'  => $result['pagination'],
        ];
    }

    /** Single certificate detail array for the show page. */
    public function detail(int $id, Request $request): array
    {
        $certificate = $this->repo->getWithAllRelations($id);
        return (new IndigencyCertificateDetailResource($certificate))->toArray($request);
    }

    /** Update status (and optionally remarks) for an indigency certificate. */
    public function updateStatus(int $id, string $status, ?string $remarks): void
    {
        $certificate = $this->repo->model()->findOrFail($id);
        $certificate->status = $status;
        if ($remarks !== null) {
            $certificate->remarks = $remarks;
        }
        $certificate->save();
    }

    /** Delete an indigency certificate record. */
    public function destroy(int $id): void
    {
        $this->repo->model()->findOrFail($id)->delete();
    }
}
