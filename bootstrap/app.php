<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__ . '/../routes/web.php',
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
                ->group(base_path('routes/Api/Admin.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Article.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Schedule.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Expertise.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Quiz.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Consultation.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Payment.php'));
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/Api/Ai.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
