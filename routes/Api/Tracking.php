<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tracking\TrackingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('do-tracking', function (Request $request) {
        return TrackingController::doTracking($request);
    });
    Route::get('tracking', function (Request $request) {
        return TrackingController::getAllTracking($request);
    });
    Route::get('tracking/lastseven', [TrackingController::class, 'getLastSevenDaysTracking']);
});
