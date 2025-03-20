<?php

use App\Http\Controllers\Assessment\AssessmentController as AssessmentAssessmentController;
use App\Http\Controllers\Quiz\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('Result', function (Request $request) {
        return QuizController::getAllAnswer($request);
    });
    Route::get('Quiz', function (Request $request) {
        return QuizController::getAllQuestion();
    });


    Route::get('getMaxPoint', function (Request $request) {
        return QuizController::getMaxPoints();
    });
    Route::post('storeAnswer', function (Request $request) {
        return QuizController::storeAnswer($request);
    });
});
