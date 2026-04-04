<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'    => ['required', 'date', 'after_or_equal:today'],
            'time'    => ['required', 'date_format:H:i'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
