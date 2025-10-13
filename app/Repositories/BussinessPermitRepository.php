<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\BarangayPermit;
use App\Models\ApplicantProfile;
use App\Models\Address;
use App\Models\SupportingDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use App\Traits\AdminListFilters;

class BussinessPermitRepository extends Repository {
    use AdminListFilters;
    
    public function __construct(
        protected BarangayPermit $barangayPermit,
        protected ?ApplicantProfile $applicantProfile = null,
        protected ?Address $address = null,
        protected ?SupportingDocument $supportingDocument = null
    ) {
        parent::__construct($barangayPermit);
    }

    protected function createPermitWithRelations(array $data, int $userId, array $models = []): BarangayPermit
    {
        return $this->transaction(function () use ($data, $userId, $models) {
            // First create the permit
            $permit = $this->create([
                'user_id' => $userId,
                'status' => 'pending',
                'application_date' => now()->toDateString() // Ensure application_date is set
            ]);
            
            // Then create the related models with the permit ID
            foreach ($models as $modelKey => $modelData) {
                if (isset($modelData['model']) && $modelData['model'] instanceof Model) {
                    $attributes = array_merge(['user_id' => $userId, 'barangay_permit_id' => $permit->id], $modelData['attributes'] ?? []);

                    $onlyKeys = $modelData['only'] ?? [];
                    $baseValues = !empty($onlyKeys) ? Arr::only($data, $onlyKeys) : [];
                    $extraValues = $modelData['extra'] ?? [];
                    $values = array_merge($baseValues, $extraValues);

                    if (isset($modelData['file']) && isset($data[$modelData['file']]) && $data[$modelData['file']] && $data[$modelData['file']]->isValid()) {
                        $values['file_path'] = $data[$modelData['file']]->store($modelData['storage_path'] ?? 'uploads', 'public');
                    }

                    $modelData['model']->updateOrCreate($attributes, $values);
                }
            }

            return $permit;
        });
    }

    protected function getApplicantProfileConfig(): array
    {
        return [
            'model' => $this->applicantProfile,
            'only' => [
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'date_of_birth',
                'place_of_birth',
                'civil_status',
                'gender',
                'citizenship',
                'contact_number',
            ]
        ];
    }

    protected function getAddressConfig(array $data): array
    {
        return [
            'model' => $this->address,
            'attributes' => ['type' => $data['address_type']],
            'only' => [
                'house_no',
                'street',
                'purok',
                'barangay_code',
                'city_code',
                'province_code',
                'region_code',
                'zip_code'
            ]

        ];
    }

    protected function getSupportingDocumentConfig(array $data): array
    {
        return [
            'model' => $this->supportingDocument,
            'file' => 'document',
            'storage_path' => 'supporting_documents',
            'extra' => ['document_type' => $data['document_type'] ?? null]
        ];
    }

    public function createPermitApplication(array $data, int $userId): BarangayPermit
    {
        if (!$this->applicantProfile || !$this->address || !$this->supportingDocument) {
            throw new \RuntimeException('Required models not initialized for permit application.');
        }

        $models = [
            'applicant_profile' => $this->getApplicantProfileConfig(),
            'address' => $this->getAddressConfig($data),
            'supporting_document' => $this->getSupportingDocumentConfig($data),
        ];

        return $this->createPermitWithRelations($data, $userId, $models);
    }

    public function getPendingPermit(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }

    public function getLatestPermit(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    public function createSupportingDocument(array $data, int $userId): SupportingDocument
    {
        if (!$this->supportingDocument) {
            throw new \RuntimeException('Supporting document model not initialized.');
        }

        return $this->transaction(function () use ($data, $userId) {
            $config = $this->getSupportingDocumentConfig($data);
            
            return $this->supportingDocument->create(array_merge(
                ['user_id' => $userId],
                $config['extra'] ?? [],
                ['file_path' => $data[$config['file']]->store($config['storage_path'], 'public')]
            ));
        });
    }

    /**
     * Create a supporting document linked to a specific Barangay Permit.
     */
    public function createSupportingDocumentForPermit(int $userId, int $permitId, UploadedFile $file, string $type): SupportingDocument
    {
        if (!$this->supportingDocument) {
            throw new \RuntimeException('Supporting document model not initialized.');
        }

        return $this->transaction(function () use ($userId, $permitId, $file, $type) {
            $path = $file->store('supporting_documents', 'public');

            return $this->supportingDocument->create([
                'user_id' => $userId,
                'barangay_permit_id' => $permitId,
                'document_type' => $type,
                'file_path' => $path,
            ]);
        });
    }

    /**
     * Admin: get list of permits with relations and optional filters.
     */
    public function adminListWithFilters(array $filters, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->newQuery()
            ->with(['applicantProfile', 'user', 'address.barangay'])
            ->latest();

        $this->applyNameStatusDateFilters($query, $filters, function ($q, string $name) {
            $q->whereHas('applicantProfile', function ($ap) use ($name) {
                $ap->where(function ($sub) use ($name) {
                    $sub->where('first_name', 'like', '%' . $name . '%')
                        ->orWhere('middle_name', 'like', '%' . $name . '%')
                        ->orWhere('last_name', 'like', '%' . $name . '%')
                        ->orWhere('suffix', 'like', '%' . $name . '%')
                        ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name, suffix) like ?", ['%' . $name . '%']);
                });
            });
        });

        // Server-side pagination to avoid loading massive datasets into memory
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Admin: get a single permit with all related models for detailed view.
     */
    public function getWithAllRelations(int $id): BarangayPermit
    {
        return $this->model->newQuery()
            ->with([
                'applicantProfile',
                'user',
                'addresses.barangay',
                'addresses.city',
                'addresses.province',
                'addresses.region',
                'supportingDocuments',
            ])->findOrFail($id);
    }

    /**
     * Admin: update status and optional remarks for a permit.
     */
    public function updateStatus(int $id, string $status, ?string $remarks = null): bool
    {
        /** @var BarangayPermit|null $permit */
        $permit = $this->model->find($id);
        if (!$permit) {
            return false;
        }

        $data = ['status' => $status];
        if ($remarks !== null) {
            $data['remarks'] = $remarks;
        }

        return $permit->update($data);
    }

    /**
     * Admin: delete permit and cascade delete related records and files.
     */
    public function deleteWithCascade(int $id): bool
    {
        /** @var BarangayPermit|null $permit */
        $permit = $this->model->newQuery()->with(['supportingDocuments', 'addresses'])->find($id);
        if (!$permit) {
            return false;
        }

        return $this->transaction(function () use ($permit) {
            foreach ($permit->supportingDocuments as $doc) {
                $path = $doc->file_path;
                if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
                $doc->delete();
            }

            if (method_exists($permit, 'addresses')) {
                $permit->addresses()->delete();
            }

            return (bool) $permit->delete();
        });
    }
}
