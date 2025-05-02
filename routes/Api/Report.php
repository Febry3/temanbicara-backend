<?php

use App\Http\Controllers\Report\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Expertise\ExpertiseController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('report', [ReportController::class, 'getReport']);
});
