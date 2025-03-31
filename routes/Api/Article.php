<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Article\ArticleController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('article/counselor', [ArticleController::class, 'getAllArticleByCounselor']);
    Route::get('article/{id}',  [ArticleController::class, 'getArticleById']);
    Route::get('article',  [ArticleController::class, 'getAllArticle']);
    Route::post('article',  [ArticleController::class, 'createArticle']);
});
