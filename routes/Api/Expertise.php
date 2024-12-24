<?php

use App\Http\Controllers\Assessment\AssessmentController as AssessmentAssessmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Expertise\ExpertiseController;

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('do-Expertise', function (Request $request) {
//         return ExpertiseController::doExpertise($request);
//     });
// });
Route::post('Expertise', function (Request $request) {
    return ExpertiseController::doExpertise($request);
});
Route::get('Expertise', function (Request $request) {
    return ExpertiseController::getAllExpertise();
});
