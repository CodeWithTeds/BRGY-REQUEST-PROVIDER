<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessPermitResource extends JsonResource
{
    public function toArray($request): array
    {
        $ap = $this->applicantProfile;
        $addr = $this->address;

        $fullName = trim(collect([
            $ap?->first_name,
            $ap?->middle_name,
            $ap?->last_name,
            $ap?->suffix,
        ])->filter()->join(' '));
        if ($fullName === '') {
            $fullName = $this->user?->name;
        }

        $addressLine = trim(collect([
            $addr?->house_no,
            $addr?->street,
            $addr?->purok,
        ])->filter()->join(', '));

        return [
            'id' => $this->id,
            'full_name' => $fullName,
            'application_date' => $this->application_date,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
            'gender' => $ap?->gender,
            'citizenship' => $ap?->citizenship,
            'contact_number' => $ap?->contact_number,
            'barangay' => $addr?->barangay?->name,
            'address_line' => $addressLine,
            'remarks' => $this->remarks,
        ];
    }
}