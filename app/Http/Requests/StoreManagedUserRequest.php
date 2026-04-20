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
        $roleName = $this->input('role_name');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_name' => $actor?->isKecamatanLevel()
                ? ['required', Rule::in(['admin_kecamatan', 'admin_desa', 'petugas_lapangan'])]
                : ['required', Rule::in(['petugas_lapangan'])],
            'kecamatan_id' => $actor?->isKecamatanLevel()
                ? ['nullable', 'integer', 'exists:kecamatans,id']
                : ['nullable'],
            'desa_id' => $actor?->isKecamatanLevel() && in_array($roleName, ['admin_desa', 'petugas_lapangan'], true)
                ? ['required', 'integer', 'exists:desas,id']
                : ['nullable', 'integer', Rule::in([(int) $actor?->desa_id])],
            'petugas_subtype' => ['nullable', Rule::in(['pencatat_meter', 'penagih_iuran'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $actor = $this->user();

        if ($actor && ! $actor->isKecamatanLevel()) {
            $this->merge([
                'desa_id' => $actor->desa_id,
                'petugas_subtype' => $this->input('petugas_subtype') ?: 'pencatat_meter',
            ]);
        }

        if ($actor?->isAdminKecamatan()) {
            $this->merge([
                'kecamatan_id' => $actor->kecamatan_id,
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
