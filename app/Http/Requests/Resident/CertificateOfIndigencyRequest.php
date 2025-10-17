<?php

namespace App\Http\Requests\Resident;

use Illuminate\Foundation\Http\FormRequest;

class CertificateOfIndigencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purpose' => ['nullable', 'string', 'max:255'],

            // Personal information (optional for indigency)
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'civil_status' => ['nullable', 'string', 'in:single,married,widowed,separated'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'citizenship' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],

            // PSGC address (optional for indigency)
            'address_type' => ['nullable', 'string', 'in:present,permanent'],
            'house_no' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'purok' => ['nullable', 'string', 'max:255'],
            'region_code' => ['nullable', 'string', 'exists:regions,code'],
            'province_code' => ['nullable', 'string', 'exists:provinces,code'],
            'city_code' => ['nullable', 'string', 'exists:cities,code'],
            'barangay_code' => ['nullable', 'string', 'exists:barangays,code'],
            'zip_code' => ['nullable', 'string', 'max:20'],

            // Documents
            'valid_government_id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'proof_of_income_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}