<?php

use App\Http\Middleware\CustomedSanctum;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Auth.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Assessment.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Tracking.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Journal.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Artikel.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Schedule.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(CustomedSanctum::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
