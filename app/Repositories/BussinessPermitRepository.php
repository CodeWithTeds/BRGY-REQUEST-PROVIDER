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

class BussinessPermitRepository extends Repository {
    
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
            // Removed legacy barangay_id usage in favor of PSGC codes
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
    public function adminListWithFilters(array $filters)
    {
        $query = $this->model->newQuery()
            ->with(['applicantProfile', 'user', 'address.barangay'])
            ->latest();

        // Filters: name, status, application date range
        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            // Limit name search strictly to Applicant Profile fields
            $query->whereHas('applicantProfile', function ($ap) use ($name) {
                $ap->where(function ($sub) use ($name) {
                    $sub->where('first_name', 'like', '%' . $name . '%')
                        ->orWhere('middle_name', 'like', '%' . $name . '%')
                        ->orWhere('last_name', 'like', '%' . $name . '%')
                        ->orWhere('suffix', 'like', '%' . $name . '%');
                });
            });
        }

        $status = $filters['status'] ?? null;
        if (in_array($status, ['pending', 'processing', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        if ($dateFrom && $dateTo) {
            $query->whereBetween('application_date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->whereDate('application_date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->whereDate('application_date', '<=', $dateTo);
        }

        return $query->get();
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
}
