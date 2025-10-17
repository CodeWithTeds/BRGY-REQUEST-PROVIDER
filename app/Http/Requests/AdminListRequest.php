<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminListRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Gate at route/controller if needed; allow by default for admins
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,processing,pre-approved,approved,rejected'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Provide sanitized data to the validator without calling Request::merge/input.
     */
    public function validationData(): array
    {
        $data = parent::validationData();
        $data['name'] = trim((string) ($data['name'] ?? ''));
        return $data;
    }

    public function filters(): array
    {
        $v = $this->validated();
        return [
            'name' => $v['name'] ?? null,
            'status' => $v['status'] ?? null,
            'date_from' => $v['date_from'] ?? null,
            'date_to' => $v['date_to'] ?? null,
        ];
    }

    public function pagination(): array
    {
        $v = $this->validated();
        $page = (int) ($v['page'] ?? 1);
        $perPage = (int) ($v['per_page'] ?? 10);
        return [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage)),
        ];
    }
}