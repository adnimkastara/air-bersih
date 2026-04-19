<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        /** @var User $target */
        $target = $this->route('user');

        if (! $actor?->canManageUsers() || ! $target) {
            return false;
        }

        if ($actor->isRoot()) {
            return $target->hasRole('admin_desa');
        }

        return $target->hasRole('petugas_lapangan') && (int) $target->desa_id === (int) $actor->desa_id;
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'desa_id' => $this->user()?->isRoot()
                ? ['required', 'integer', 'exists:desas,id']
                : ['nullable', Rule::in([(int) $this->user()?->desa_id])],
            'petugas_subtype' => ['nullable', Rule::in(['pencatat_meter', 'penagih_iuran'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->user()?->isRoot()) {
            $this->merge([
                'desa_id' => $this->user()?->desa_id,
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
