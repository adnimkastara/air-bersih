<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected function authorizeAdmin(Request $request): User
    {
        $user = $request->user();

        abort_if(! $user->isRoot(), 403);

        return $user;
    }

    public function index(Request $request)
    {
        $user = $this->authorizeAdmin($request);

        return view('admin', [
            'user' => $user,
        ]);
    }
}
