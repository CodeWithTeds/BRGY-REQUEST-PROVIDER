<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BarangayPermitDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $ap = $this->applicantProfile;

        $fullName = trim(collect([
            $ap?->first_name,
            $ap?->middle_name,
            $ap?->last_name,
            $ap?->suffix,
        ])->filter()->join(' '));
        if ($fullName === '') {
            $fullName = $this->user?->name;
        }

        $addresses = $this->addresses->map(function ($addr) {
            $line = trim(collect([
                $addr?->house_no,
                $addr?->street,
                $addr?->purok,
            ])->filter()->join(', '));

            return [
                'id' => $addr->id,
                'type' => $addr->type,
                'line' => $line,
                'barangay' => $addr?->barangay?->name,
                'city' => $addr?->city?->name,
                'province' => $addr?->province?->name,
                'region' => $addr?->region?->name,
                'zip_code' => $addr?->zip_code,
            ];
        });

        return [
            'id' => $this->id,
            'full_name' => $fullName,
            'application_date' => $this->application_date,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'addresses' => $addresses,
        ];
    }
}