<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResidencyCertificateDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->user;
        $ap = $user?->applicantProfile;
        $addresses = $user?->addresses ?? collect();
        $present = $addresses?->firstWhere('type', 'present') ?? $addresses?->first();

        $line = null;
        if ($present) {
            $line = trim(collect([
                $present?->house_no,
                $present?->street,
                $present?->purok,
            ])->filter()->join(', '));
        }

        // Collect certificate-scoped supporting documents only
        $documents = $this->supportingDocuments ?? collect();

        return [
            'id' => $this->id,
            'full_name' => $user?->name,
            'application_date' => $this->application_date,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
            'remarks' => $this->remarks,
            'contact_number' => $ap?->contact_number,
            'applicant_profile' => [
                'first_name' => $ap?->first_name,
                'middle_name' => $ap?->middle_name,
                'last_name' => $ap?->last_name,
                'suffix' => $ap?->suffix,
                'date_of_birth' => $ap?->date_of_birth,
                'place_of_birth' => $ap?->place_of_birth,
                'civil_status' => $ap?->civil_status,
            ],
            'addresses' => $addresses?->map(function ($addr) {
                $line = trim(collect([
                    $addr?->house_no,
                    $addr?->street,
                    $addr?->purok,
                ])->filter()->join(', '));
                return [
                    'id' => $addr->id,
                    'type' => $addr->type,
                    'line' => $line ?: null,
                    'barangay' => $addr?->barangay?->name,
                    'city' => $addr?->city?->name,
                    'province' => $addr?->province?->name,
                    'region' => $addr?->region?->name,
                    'zip_code' => $addr?->zip_code,
                ];
            })?->all() ?? [],
            'supporting_documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'file_path' => $doc->file_path,
                    'verified' => (bool) $doc->verified,
                ];
            })->values()->all(),
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
            ],
        ];
    }
}