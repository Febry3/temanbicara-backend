<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Schedule\ScheduleController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('schedule', [ScheduleController::class, 'getSchedule']);
    Route::get('schedule/{id}', [ScheduleController::class, 'getScheduleByID']);
    Route::get('available-schedule', [ScheduleController::class, 'getAvailableSchedule']);
    Route::get('available-schedule/{id}', [ScheduleController::class, 'getAvailableScheduleByID']);
    Route::get('schedule/{id}', [ScheduleController::class, 'updateScheduleStatus']);
    Route::post('schedule', [ScheduleController::class, 'createSchedule']);
});
