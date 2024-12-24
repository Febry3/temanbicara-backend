<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Artikel\ArticleController;

Route::post('article', function (Request $request) {
    return ArticleController::createArticle($request);
});
Route::get('article', function () {
    return ArticleController::getAllArticle();
});

Route::get('article/{id}', function ($id) {
    return ArticleController::getArticleById($id);
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
