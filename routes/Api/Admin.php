<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

//auth
Route::post('admin/login', [AdminController::class, 'loginAsAdmin']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('admin', [AdminController::class, 'getAdminData']);
    Route::post('admin/logout', [AdminController::class, 'logoutAsAdmin']);
});

//admin
Route::get('admin/account', [AdminController::class, 'getUserData']);
Route::post('admin/account', [AdminController::class, 'createUser']);
Route::get('admin/account/admin', [AdminController::class, 'getAdminData']);
Route::get('admin/account/counselor', [AdminController::class, 'getCounselorData']);
Route::get('admin/account/{id}', [AdminController::class, 'getUserById']);
Route::put('admin/account/{id}', [AdminController::class, 'updateUser']);
Route::delete('admin/account/{id}', [AdminController::class, 'deleteUser']);

//article
Route::get('admin/article', [AdminController::class, 'getAllArticle']);
Route::patch('admin/article/{id}', [AdminController::class, 'updateArticleStatus']);

//payment
Route::get('admin/payment', [AdminController::class, 'getAllPayment']);

//dashboard
Route::get('admin/dashboard', [AdminController::class, 'getDashboardData']);
