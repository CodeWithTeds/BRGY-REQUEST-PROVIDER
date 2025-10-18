<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IndigencyCertificateDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user = $this->user;
        
        // Include all supporting documents linked to this certificate
        $documents = collect($this->supportingDocuments ?? []);

        return [
            'id' => $this->id,
            'full_name' => optional($user)->name,
            'application_date' => $this->application_date,
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
            'contact_number' => optional($user?->applicantProfile)->contact_number ?? null,
            'remarks' => $this->remarks,
            'applicant_profile' => [
                'first_name' => optional($user?->applicantProfile)->first_name,
                'middle_name' => optional($user?->applicantProfile)->middle_name,
                'last_name' => optional($user?->applicantProfile)->last_name,
                'suffix' => optional($user?->applicantProfile)->suffix,
                'date_of_birth' => optional($user?->applicantProfile)->date_of_birth,
                'place_of_birth' => optional($user?->applicantProfile)->place_of_birth,
                'civil_status' => optional($user?->applicantProfile)->civil_status,
            ],
            'addresses' => optional($user?->addresses)->map(function ($addr) {
                return [
                    'id' => $addr->id,
                    'type' => $addr->type,
                    'line' => $addr->line,
                    'barangay' => optional($addr->barangay)->name,
                    'city' => optional($addr->city)->name,
                    'province' => optional($addr->province)->name,
                    'region' => optional($addr->region)->name,
                    'zip_code' => $addr->zip_code,
                ];
            })->values()->all(),
            'user' => [
                'id' => optional($user)->id,
                'name' => optional($user)->name,
                'email' => optional($user)->email,
            ],
            'supporting_documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_type' => $doc->document_type,
                    'file_path' => $doc->file_path,
                    'verified' => (bool) $doc->verified,
                ];
            })->values()->all(),
        ];
    }
}