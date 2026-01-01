<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;

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
        // Force HTTPS if behind a proxy (like ngrok) or in production
        if (request()->header('x-forwarded-proto') === 'https') {
             \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        //
        Route::aliasMiddleware('role', CheckRole::class);
    }
}
