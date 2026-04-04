<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'           => ['nullable', 'integer'],
            'action'       => ['nullable', 'string'],
            'subject_type' => ['nullable', 'string'],
            'clerk_id'     => ['nullable', 'integer'],
            'permit_id'    => ['nullable', 'integer'],
            'date_from'    => ['nullable', 'date'],
            'date_to'      => ['nullable', 'date'],
        ];
    }

    /** Return only the filter fields as a clean array. */
    public function filters(): array
    {
        return $this->only(['id', 'action', 'subject_type', 'clerk_id', 'permit_id', 'date_from', 'date_to']);
    }
}
