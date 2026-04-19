<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        $users = User::with(['role', 'desa'])
            ->when(! $actor->isRoot(), fn ($query) => $query->where('desa_id', $actor->desa_id))
            ->orderBy('name')
            ->get();

        return view('admin.users', [
            'users' => $users,
            'desas' => Desa::orderBy('name')->get(),
            'actor' => $actor,
        ]);
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'desa_id' => ['nullable', 'integer', 'exists:desas,id'],
            'petugas_subtype' => ['nullable', Rule::in(['pencatat_meter', 'penagih_iuran'])],
        ]);

        if ($actor->isRoot()) {
            $roleName = 'admin_desa';
            abort_if(empty($data['desa_id']), 422, 'Admin desa harus memiliki desa.');
        } else {
            $roleName = 'petugas_lapangan';
            $data['desa_id'] = $actor->desa_id;
        }

        $role = Role::where('name', $roleName)->firstOrFail();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'desa_id' => $data['desa_id'] ?? null,
            'petugas_subtype' => $roleName === 'petugas_lapangan' ? ($data['petugas_subtype'] ?? 'pencatat_meter') : null,
        ]);

        return back()->with('status', 'User berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        if (! $actor->isRoot()) {
            abort_if(! ($user->hasRole('petugas_lapangan') && (int) $user->desa_id === (int) $actor->desa_id), 403);
        } else {
            abort_unless($user->hasRole('admin_desa'), 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'desa_id' => ['nullable', 'integer', 'exists:desas,id'],
            'petugas_subtype' => ['nullable', Rule::in(['pencatat_meter', 'penagih_iuran'])],
        ]);

        if ($actor->isRoot()) {
            abort_if(empty($data['desa_id']), 422, 'Admin desa harus memiliki desa.');
        } else {
            $data['desa_id'] = $actor->desa_id;
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'desa_id' => $data['desa_id'],
            'petugas_subtype' => $user->hasRole('petugas_lapangan') ? ($data['petugas_subtype'] ?? $user->petugas_subtype ?? 'pencatat_meter') : null,
        ]);

        return back()->with('status', 'User berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        if ($actor->isRoot()) {
            abort_unless(in_array($user->role?->name, ['admin_desa', 'petugas_lapangan'], true), 403);
        } else {
            abort_if(! ($user->hasRole('petugas_lapangan') && (int) $user->desa_id === (int) $actor->desa_id), 403);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', 'Password user berhasil direset.');
    }
}
