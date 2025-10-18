<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessPermitDetailResource extends JsonResource
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

        $documents = $this->supportingDocuments->map(function ($doc) {
            return [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'file_path' => $doc->file_path,
                'verified' => (bool) $doc->verified,
            ];
        });

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
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'remarks' => $this->remarks,
            'applicant_profile' => [
                'first_name' => $ap?->first_name,
                'middle_name' => $ap?->middle_name,
                'last_name' => $ap?->last_name,
                'suffix' => $ap?->suffix,
                'date_of_birth' => optional($ap?->date_of_birth)?->toDateString(),
                'place_of_birth' => $ap?->place_of_birth,
                'civil_status' => $ap?->civil_status,
            ],
            'addresses' => $addresses,
            'supporting_documents' => $documents,
        ];
    }
}