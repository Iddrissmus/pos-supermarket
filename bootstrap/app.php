<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // Allow login routes, logout, and SuperAdmin routes during maintenance mode
        // SuperAdmin routes are protected by 'role:superadmin' middleware
        $middleware->preventRequestsDuringMaintenance(
            except: [
                'login/*',      // Allow all login routes
                'logout',
                'superadmin/*', // Allow all SuperAdmin routes (protected by role middleware)
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
