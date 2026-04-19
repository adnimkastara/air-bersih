<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canManageUsers();
    }

    public function rules(): array
    {
        $actor = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'desa_id' => $actor?->isRoot()
                ? ['required', 'integer', 'exists:desas,id']
                : ['nullable', 'integer', Rule::in([(int) $actor?->desa_id])],
            'petugas_subtype' => ['nullable', Rule::in(['pencatat_meter', 'penagih_iuran'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $actor = $this->user();

        if ($actor && ! $actor->isRoot()) {
            $this->merge([
                'desa_id' => $actor->desa_id,
                'petugas_subtype' => $this->input('petugas_subtype') ?: 'pencatat_meter',
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
