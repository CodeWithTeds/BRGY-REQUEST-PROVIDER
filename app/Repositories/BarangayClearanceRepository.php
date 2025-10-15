<?php

namespace App\Repositories;

use App\Models\BarangayClearance;
use App\Traits\AdminListFilters;
use App\Models\ApplicantProfile;
use App\Models\Address;
use App\Models\SupportingDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class BarangayClearanceRepository extends Repository {
    use AdminListFilters;
    
    public function __construct(
        BarangayClearance $barangayClearance,
        protected ?ApplicantProfile $applicantProfile = null,
        protected ?Address $address = null,
        protected ?SupportingDocument $supportingDocument = null
    ) {
        parent::__construct($barangayClearance);
    }

    protected function createClearanceWithRelations(array $data, int $userId, array $models = []): BarangayClearance
    {
        return $this->transaction(function () use ($data, $userId, $models) {
            // Create the clearance
            $clearance = $this->create([
                'user_id' => $userId,
                'status' => 'pending',
                'purpose' => $data['purpose'] ?? null,
                'application_date' => now()->toDateString()
            ]);
            
            // Create the related models with the clearance ID
            foreach ($models as $modelKey => $modelData) {
                if (isset($modelData['model']) && $modelData['model'] instanceof Model) {
                    $attributes = array_merge(
                        ['user_id' => $userId, 'barangay_clearance_id' => $clearance->id],
                        $modelData['attributes'] ?? []
                    );

                    $onlyKeys = $modelData['only'] ?? [];
                    $baseValues = !empty($onlyKeys) ? Arr::only($data, $onlyKeys) : [];
                    $extraValues = $modelData['extra'] ?? [];
                    $values = array_merge($baseValues, $extraValues);

                    if (isset($modelData['file']) && 
                        isset($data[$modelData['file']]) && 
                        $data[$modelData['file']] && 
                        $data[$modelData['file']]->isValid()
                    ) {
                        $values['file_path'] = $data[$modelData['file']]->store(
                            $modelData['storage_path'] ?? 'uploads',
                            'public'
                        );
                    }

                    $modelData['model']->updateOrCreate($attributes, $values);
                }
            }

            return $clearance;
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
                'contact_number'
            ]
        ];
    }

    protected function getAddressConfig(array $data): array
    {
        return [
            'model' => $this->address,
            'only' => [
                'house_no',
                'street',
                'purok',
                'region_code',
                'province_code',
                'city_code',
                'barangay_code',
                'zip_code'
            ],
            'extra' => [
                'type' => $data['address_type'] ?? null,
            ]
        ];
    }

    protected function getSupportingDocumentConfig(array $data): array
    {
        return [
            'model' => $this->supportingDocument,
            // Include document_type in attributes so multiple records are created per type
            'only' => ['document_type'],
            'attributes' => [
                'document_type' => $data['document_type'] ?? null,
            ],
            'file' => 'document',
            'storage_path' => 'documents/clearance'
        ];
    }

    protected function getValidIdDocumentConfig(): array
    {
        return [
            'model' => $this->supportingDocument,
            // Force document_type to 'valid_id' independent of request field
            'only' => [],
            'attributes' => [
                'document_type' => 'valid_id',
            ],
            'extra' => ['document_type' => 'valid_id'],
            'file' => 'valid_id_document',
            'storage_path' => 'documents/clearance'
        ];
    }

    public function getClearances(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with(['applicantProfile', 'address', 'supportingDocuments'])
            ->latest()
            ->get();
    }

    public function getPendingClearance(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }

    public function getLatestClearance(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    public function getClearance(int $id, int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with(['applicantProfile', 'address', 'supportingDocuments'])
            ->findOrFail($id);
    }

    public function createClearanceApplication(array $data, int $userId): BarangayClearance
    {
        if (!$this->applicantProfile || !$this->address || !$this->supportingDocument) {
            throw new \RuntimeException('Required models not initialized for clearance application.');
        }

        $models = [
            'applicant_profile' => $this->getApplicantProfileConfig(),
            'address' => $this->getAddressConfig($data),
            'supporting_document' => $this->getSupportingDocumentConfig($data),
            // Optional valid ID document if provided
            'valid_id_document' => $this->getValidIdDocumentConfig(),
        ];

        return $this->createClearanceWithRelations($data, $userId, $models);
    }

    public function updateStatus(int $id, string $status, ?string $remarks = null): bool
    {
        $clearance = $this->find($id);
        
        if (!$clearance) {
            return false;
        }

        $data = ['status' => $status];
        
        if ($status === 'approved') {
            $data['issue_date'] = now()->toDateString();
            $data['expiry_date'] = now()->addYear()->toDateString();
            $data['clearance_number'] = 'BCL-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);
        }

        if ($remarks) {
            $data['remarks'] = $remarks;
        }

        return $clearance->update($data);
    }

    /**
     * Admin: get a single clearance with all related models for detailed view.
     */
    public function getWithAllRelations(int $id): BarangayClearance
    {
        return $this->model->newQuery()
            ->with([
                'applicantProfile',
                'user',
                'address.barangay',
                'address.city',
                'address.province',
                'address.region',
                'supportingDocuments',
            ])->findOrFail($id);
    }

    /**
     * Admin: get list of clearances with relations and optional filters.
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

        // Server-side pagination for large datasets
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}