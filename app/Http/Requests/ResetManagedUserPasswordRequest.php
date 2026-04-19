<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetManagedUserPasswordRequest extends FormRequest
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
            return in_array($target->role?->name, ['admin_desa', 'petugas_lapangan'], true);
        }

        return $target->hasRole('petugas_lapangan') && (int) $target->desa_id === (int) $actor->desa_id;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
