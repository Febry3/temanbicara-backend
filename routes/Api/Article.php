<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Artikel\ArtikelController;

Route::post('article', function (Request $request) {
    return ArtikelController::createArticle($request);
});
Route::get('article', function (Request $request) {
    return ArtikelController::getAllArticle($request);
});

//ini buat kalo harus login baru bisa buat artikel
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('do-artikel', function (Request $request) {
//         return ArtikelController::doArtikel($request);
//     });
//     Route::get('get-artikel', function (Request $request) {
//         return ArtikelController::getArtikel($request);
//     });
// });
