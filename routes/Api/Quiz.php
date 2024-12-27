<?php

use App\Http\Controllers\Assessment\AssessmentController as AssessmentAssessmentController;
use App\Http\Controllers\Quiz\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('Quiz', function (Request $request) {
    return QuizController::getAllQuestion();
});
Route::get('Result', function (Request $request) {
    return QuizController::getAllAnswer();
});
