<?php

namespace App\Http\Middleware;

use App\Models\ApiAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            abort(401);
        }

        $accessToken = ApiAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        $user = $accessToken?->user;

        if (! $user || ! $user->is_active) {
            abort(401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();

        Auth::setUser($user);
        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
