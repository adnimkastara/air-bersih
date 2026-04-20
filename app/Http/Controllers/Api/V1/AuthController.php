<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(array_merge($credentials, ['is_active' => true]))) {
            return response()->json(['message' => 'Email/password tidak sesuai atau akun nonaktif.'], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'session_id' => $request->session()->getId(),
                'user' => $request->user()->load('role'),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request)
    {
        return response()->json(['data' => $request->user()->load('role', 'desa', 'kecamatan')]);
    }
}
