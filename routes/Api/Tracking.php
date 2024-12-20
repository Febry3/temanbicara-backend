<?php

use App\Http\Controllers\Assessment\AssessmentController as AssessmentAssessmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tracking\TrackingController;
use App\Http\Middleware\CustomedSanctum;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('do-tracking', function (Request $request) {
        return TrackingController::doTracking($request);
    });
});
