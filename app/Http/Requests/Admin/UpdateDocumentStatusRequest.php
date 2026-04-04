<?php

namespace App\Http\Requests\Admin;

use App\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'  => ['required', 'string', DocumentStatus::validationRule()],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
