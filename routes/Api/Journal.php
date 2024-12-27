<?php

use App\Http\Controllers\Journal\JournalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('journal', function (Request $request) {
        return JournalController::getAllJournal($request);
    });
    Route::get('journal/{id}', function (Request $request, $id) {
        return JournalController::getJournal($request, $id);
    });
    Route::post('journal', function (Request $request) {
        return JournalController::createJournal($request);
    });
    Route::put('journal/{id}', function (Request $request, $id) {
        return JournalController::updateJournal($request, $id);
    });
    Route::delete('journal/{id}', function (Request $request, $id) {
        return JournalController::deleteJournal($request, $id);
    });
});


