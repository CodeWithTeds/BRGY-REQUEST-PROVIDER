<?php

namespace App\Http\Requests\Resident;

use Illuminate\Foundation\Http\FormRequest;

class CertificateOfResidencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string', 'max:255'],
            // Personal information
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'civil_status' => ['required', 'string', 'in:single,married,widowed,separated'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'citizenship' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],
            // PSGC address
            'address_type' => ['required', 'string', 'in:present,permanent'],
            'house_no' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'purok' => ['nullable', 'string', 'max:255'],
            'region_code' => ['required', 'string', 'exists:regions,code'],
            'province_code' => ['required', 'string', 'exists:provinces,code'],
            'city_code' => ['required', 'string', 'exists:cities,code'],
            'barangay_code' => ['required', 'string', 'exists:barangays,code'],
            'zip_code' => ['required', 'string', 'max:20'],
            // Supporting documents (optional)
            'valid_government_id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'proof_of_residence_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'lease_contract_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'authorization_letter_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
