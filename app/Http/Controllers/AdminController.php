<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        abort_if(! $user->isAdmin(), 403);

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->authorizeAdmin($request);

        return view('admin', [
            'user' => $user,
        ]);
    }

    public function users(Request $request)
    {
        $user = $this->authorizeAdmin($request);
        $users = User::with('role')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users', [
            'user' => $user,
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $role = Role::where('name', $data['role'])->firstOrFail();

        $user->role_id = $role->id;
        $user->save();

        return back()->with('status', 'User role updated successfully.');
    }
}
