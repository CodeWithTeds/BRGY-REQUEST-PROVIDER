<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreClerkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // handled by admin middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clerks,email', 'unique:users,email'],
            'password' => ['required_with:email', 'confirmed', Rules\Password::defaults()],
            'contact_number' => ['nullable', 'string', 'max:32'],
            'position' => ['nullable', 'string', 'max:64'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}