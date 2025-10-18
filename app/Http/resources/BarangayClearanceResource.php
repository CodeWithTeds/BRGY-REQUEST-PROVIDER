<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BarangayClearanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $ap = $this->applicantProfile;
        $addr = $this->address;
        $barangay = $addr?->barangay?->name ?? null;

        $fullName = trim(implode(' ', array_filter([
            $ap->first_name ?? null,
            $ap->middle_name ?? null,
            $ap->last_name ?? null,
            $ap->suffix ?? null,
        ])));

        $addressLine = null;
        if ($addr) {
            $addressLine = trim(implode(', ', array_filter([
                $addr->house_no,
                $addr->street,
                $addr->purok,
            ])));
        }

        return [
            'id' => $this->id,
            'full_name' => $fullName,
            'application_date' => optional($this->application_date)?->toDateString(),
            'status' => $this->status,
            'created_at' => optional($this->created_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
            'updated_at' => optional($this->updated_at)?->copy()->setTimezone('Asia/Manila')->toDateTimeString(),
            'gender' => $ap->gender ?? null,
            'citizenship' => $ap->citizenship ?? null,
            'contact_number' => $ap->contact_number ?? null,
            'barangay' => $barangay,
            'address_line' => $addressLine,
            'remarks' => $this->remarks ?? null,
        ];
    }
}