<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IndigencyCertificateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => optional($this->user)->name,
            // Guard against null status values
            'status' => $this->status ?? 'pending',
            'application_date' => $this->application_date,
            // Safely traverse nested relations using nullsafe operator
            'barangay' => $this->user?->addresses->first()?->barangay?->name ?? null,
            'address_line' => $this->user?->addresses->first()?->line ?? null,
            'contact_number' => optional($this->user?->applicantProfile)->contact_number ?? null,
            'remarks' => $this->remarks,
            'updated_at' => optional($this->updated_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
        ];
    }
}