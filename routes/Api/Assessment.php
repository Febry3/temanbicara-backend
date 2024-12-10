<?php

use App\Http\Controllers\Assessment\AssessmentController as AssessmentAssessmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Assessment\AssessmentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('do-assessment', function (Request $request) {
        return AssessmentController::doAssessment($request);
    });
});
