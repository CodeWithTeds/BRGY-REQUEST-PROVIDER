<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResidencyCertificateResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->user;
        $ap = $user?->applicantProfile;
        // Prefer present address; fallback to first
        $addr = optional($user)->addresses?->firstWhere('type', 'present') ?? optional($user)->addresses?->first();

        $line = null;
        if ($addr) {
            $line = trim(collect([
                $addr?->house_no,
                $addr?->street,
                $addr?->purok,
            ])->filter()->join(', '));
        }

        return [
            'id' => $this->id,
            'full_name' => $user?->name,
            'application_date' => $this->application_date,
            'status' => $this->status,
            'barangay' => $addr?->barangay?->name,
            'address_line' => $line,
            'contact_number' => $ap?->contact_number,
            'remarks' => $this->remarks,
            'updated_at' => optional($this->updated_at)?->toDateTimeString(),
        ];
    }
}