<?php

namespace App\Repositories;

use App\Models\CertificateOfResidency;

class CertificateOfResidencyRepository extends Repository
{
    public function __construct(CertificateOfResidency $model)
    {
        parent::__construct($model);
    }

    public function createApplication(array $data, int $userId): CertificateOfResidency
    {
        return $this->create([
            'user_id' => $userId,
            'purpose' => $data['purpose'],
            'application_date' => now()->toDateString(),
        ]);
    }

    public function getPending(int $userId)
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }
}