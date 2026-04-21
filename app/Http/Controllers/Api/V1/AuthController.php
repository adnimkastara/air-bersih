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

        if (! Auth::guard('web')->attempt(array_merge($credentials, ['is_active' => true]))) {
            return response()->json(['message' => 'Email/password tidak sesuai atau akun nonaktif.'], 422);
        }

        $user = Auth::guard('web')->user();

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => $user?->load('role', 'desa', 'kecamatan'),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request)
    {
        return response()->json(['data' => $request->user()->load('role', 'desa', 'kecamatan')]);
    }
}
