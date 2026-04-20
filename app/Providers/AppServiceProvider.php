<?php

namespace App\Providers;

use App\Support\BrandingResolver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', 'guest');

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('api-login', function (Request $request) {
            $email = (string) $request->input('email', 'guest');

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        View::composer('*', function ($view) {
            $view->with('branding', BrandingResolver::resolve(auth()->user()));
        });
    }
}
