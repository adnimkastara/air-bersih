<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function editPassword(Request $request)
    {
        return view('auth.change-password', [
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak valid.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', 'Password berhasil diperbarui.');
    }
}
