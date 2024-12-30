<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Article\ArticleController;
Route::middleware('auth:sanctum')->group(function () {
    Route::post('article', function (Request $request) {
        return ArticleController::createArticle($request);
    });
    Route::get('articleById', function (Request $request) {
        return ArticleController::getAllArticleByCounselor($request);
    });

    Route::get('article/{id}', function ($id) {
        return ArticleController::getArticleById($id);
    });
        Route::get('get-artikel/{id}', [ArticleController::class, 'getArtikelById']);

});


    Route::get('article', function () {
       return ArticleController::getAllArticle();
    });
