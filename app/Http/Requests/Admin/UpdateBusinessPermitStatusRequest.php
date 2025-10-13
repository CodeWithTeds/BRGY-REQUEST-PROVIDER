<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessPermitStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize via middleware or policies; allow by default for admins
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,processing,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}