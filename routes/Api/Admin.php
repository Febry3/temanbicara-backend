<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


// Route::post('admin/login', function (Request $request) {
//     return AdminController::loginAdmin($request);
// });

// Route::middleware(['auth:sanctum', 'ability:Admin'])->group(function () {
//     Route::post('admin/counselor', function (Request $request) {
//         return AdminController::createCounselor($request);
//     });
//     Route::get('admin/counselor', function (Request $request) {
//         return AdminController::getAllCounselor($request);
//     });
//     Route::post('admin/verify-password', function (Request $request) {
//         return AdminController::verifyPassword($request);
//     });
//     Route::post('admin/admin', function (Request $request) {
//         return AdminController::createAdmin($request);
//     });
// });

Route::post('admin/account', [AdminController::class, 'createUser']);
Route::get('admin/account', [AdminController::class, 'getUserData']);
Route::get('admin/account/admin', [AdminController::class, 'getAdminData']);
Route::get('admin/account/counselor', [AdminController::class, 'getCounselorData']);
Route::get('admin/account/{id}', [AdminController::class, 'getUserById']);
Route::put('admin/account/{id}', [AdminController::class, 'updateUser']);
Route::delete('admin/account/{id}', [AdminController::class, 'deleteUser']);


Route::get('admin/payment', [AdminController::class, 'getAllPayment']);
