<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Schedule\ScheduleController;

Route::get('get-schedule', function (Request $request) {
    return ScheduleController::getSchedule($request);
});