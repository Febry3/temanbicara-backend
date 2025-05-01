<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ai\AiController;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('generate', [AiController::class, 'generate']);
});
