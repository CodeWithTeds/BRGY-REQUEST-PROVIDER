<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use App\Models\Clerk;

class UpdateClerkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // handled by admin middleware
    }

    public function rules(): array
    {
        $id = request()->route('id');
        $userId = optional(Clerk::find($id))->user_id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clerks', 'email')->ignore($id), Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'contact_number' => ['nullable', 'string', 'max:32'],
            'position' => ['nullable', 'string', 'max:64'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}