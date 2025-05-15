<?php

use App\Http\Controllers\Journal\JournalController;
use App\Http\Requests\JournalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum'])->group(function () {
    $journalById = '/journal/{id}';
    Route::put($journalById, [JournalController::class, 'updateJournal']);
    Route::post('/journal', [JournalController::class, 'createJournal']);
    Route::post('/journal/get', [JournalController::class, 'getAllJournalByUserId']);
    Route::get($journalById, [JournalController::class, 'getJournalById']);
    Route::delete($journalById, [JournalController::class, 'deleteJournal']);
});

Route::post('/test-journal', function (Illuminate\Http\Request $request) {
    dd($request->all());
});
