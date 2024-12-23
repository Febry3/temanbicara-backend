<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Artikel\ArtikelController;

    Route::post('do-artikel', function (Request $request) {
        return ArtikelController::doArtikel($request);
    });
Route::get('get-artikel', function (Request $request) {
    return ArtikelController::getArtikel($request);
});

    Route::get('get-artikel/{id}', [ArtikelController::class, 'getArtikelById']);


//ini buat kalo harus login baru bisa buat artikel
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('do-artikel', function (Request $request) {
//         return ArtikelController::doArtikel($request);
//     });
//     Route::get('get-artikel', function (Request $request) {
//         return ArtikelController::getArtikel($request);
//     });
// });
