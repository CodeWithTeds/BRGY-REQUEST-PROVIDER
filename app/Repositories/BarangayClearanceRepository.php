<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\BarangayClearance;
use App\Models\ApplicantProfile;
use App\Models\Address;
use App\Models\SupportingDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class BarangayClearanceRepository extends Repository {
    
    public function __construct(
        protected BarangayClearance $barangayClearance,
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
                'address_type',
                'house_no',
                'street',
                'purok',
                'region_code',
                'province_code',
                'city_code',
                'barangay_code',
                'zip_code'
            ]
        ];
    }

    protected function getSupportingDocumentConfig(array $data): array
    {
        return [
            'model' => $this->supportingDocument,
            'only' => ['document_type'],
            'file' => 'document',
            'storage_path' => 'documents/clearance'
        ];
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
}