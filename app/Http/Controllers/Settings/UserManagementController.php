<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetManagedUserPasswordRequest;
use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\Desa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        $query = User::with(['role', 'desa'])
            ->when(! $actor->isRoot(), fn ($builder) => $builder->where('desa_id', $actor->desa_id))
            ->when($search = trim((string) $request->query('q')), function ($builder) use ($search) {
                $builder->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });

        if ($actor->isRoot()) {
            $query->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('name', ['admin_desa', 'petugas_lapangan']));
        } else {
            $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('name', 'petugas_lapangan'));
        }

        return view('settings.users.index', [
            'users' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'actor' => $actor,
            'filters' => ['q' => $request->query('q')],
        ]);
    }

    public function create(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        return view('settings.users.create', [
            'actor' => $actor,
            'desas' => Desa::orderBy('name')->get(),
        ]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $request->validated();
        $roleName = $actor->isRoot() ? 'admin_desa' : 'petugas_lapangan';
        $role = Role::where('name', $roleName)->firstOrFail();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'desa_id' => $data['desa_id'] ?? null,
            'petugas_subtype' => $roleName === 'petugas_lapangan' ? ($data['petugas_subtype'] ?? 'pencatat_meter') : null,
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('settings.users.index')->with('status', 'User berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user)
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        if ($actor->isRoot()) {
            abort_unless($user->hasRole('admin_desa'), 403);
        } else {
            abort_if(! ($user->hasRole('petugas_lapangan') && (int) $user->desa_id === (int) $actor->desa_id), 403);
        }

        return view('settings.users.edit', [
            'actor' => $actor,
            'user' => $user->load('desa', 'role'),
            'desas' => Desa::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateManagedUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'desa_id' => $data['desa_id'] ?? null,
            'petugas_subtype' => $user->hasRole('petugas_lapangan') ? ($data['petugas_subtype'] ?? 'pencatat_meter') : null,
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('settings.users.index')->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor?->canManageUsers(), 403);

        if ($actor->isRoot()) {
            abort_unless($user->hasRole('admin_desa'), 403);
        } else {
            abort_if(! ($user->hasRole('petugas_lapangan') && (int) $user->desa_id === (int) $actor->desa_id), 403);
        }

        $user->delete();

        return redirect()->route('settings.users.index')->with('status', 'User berhasil dihapus.');
    }

    public function resetPassword(ResetManagedUserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('settings.users.edit', $user)->with('status', 'Password user berhasil direset.');
    }
}
