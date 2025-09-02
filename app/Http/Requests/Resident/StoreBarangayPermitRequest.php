<?php

namespace App\Http\Requests\Resident;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangayPermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'civil_status' => ['required', 'string', 'in:single,married,widowed,separated'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'citizenship' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],

            // Address fields using app-level PSGC tables
            'address_type' => ['required', 'in:permanent,present'],
            'house_no' => ['nullable', 'string', 'max:50'],
            'street' => ['nullable', 'string', 'max:255'],
            'purok' => ['nullable', 'string', 'max:255'],
            'barangay_code' => ['nullable', 'exists:barangays,code'],
            'city_code' => ['nullable', 'exists:cities,code'],
            'province_code' => ['nullable', 'exists:provinces,code'],
            'region_code' => ['nullable', 'exists:regions,code'],
            'zip_code' => ['nullable', 'string', 'max:10'],

            // Document upload
            'document_type' => ['required', 'string', 'max:100'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
