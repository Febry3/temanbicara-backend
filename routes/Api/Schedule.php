<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Schedule\ScheduleController;

Route::get('schedule', function (Request $request) {
    return ScheduleController::getSchedule($request);
});

Route::get('schedule/{id}', function (Request $request, $id) {
    return ScheduleController::getScheduleByID($request, $id);
});

Route::post('schedule', function (Request $request) {
    return ScheduleController::createSchedule($request);
});