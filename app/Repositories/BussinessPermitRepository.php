<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\BarangayPermit;
use App\Models\ApplicantProfile;
use App\Models\Address;
use App\Models\SupportingDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
            foreach ($models as $modelKey => $modelData) {
                if (isset($modelData['model']) && $modelData['model'] instanceof Model) {
                    $attributes = array_merge(['user_id' => $userId], $modelData['attributes'] ?? []);
                    $values = array_merge(
                        $modelData['only'] ? Arr::only($data, $modelData['only']) : [],
                        $modelData['extra'] ?? []
                    );

                    if (isset($modelData['file']) && isset($data[$modelData['file']]) && $data[$modelData['file']]->isValid()) {
                        $values['file_path'] = $data[$modelData['file']]->store($modelData['storage_path'], 'public');
                    }

                    $modelData['model']->updateOrCreate($attributes, $values);
                }
            }

            return $this->create(array_merge(
                ['user_id' => $userId],
                $data['permit_data'] ?? []
            ));
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
            ],
            'extra' => ['barangay_id' => $data['barangay_id'] ?? null]
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
}
