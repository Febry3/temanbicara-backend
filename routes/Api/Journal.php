<?php

use App\Http\Controllers\Journal\JournalController;
use App\Http\Requests\JournalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('journal', function (Request $request) {
        return JournalController::getAllJournalByUserId($request);
    });

    Route::get('journal/{id}', function (Request $request, $id) {
        return JournalController::getJournalById($request, $id);
    });

    Route::put('journal/{id}', function (Request $request, $id) {
        return JournalController::updateJournal($request, $id);
    });

    Route::post('/journal', [JournalController::class, 'createJournal']);

    Route::delete('journal/{id}', function (Request $request, $id) {
        return JournalController::deleteJournal($request, $id);
    });
});

Route::delete('tett', function () {
    return JournalController::testDelete();
});
