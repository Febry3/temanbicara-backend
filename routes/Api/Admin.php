<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;

Route::post('admin/login', function (Request $request) {
    return AdminController::loginAdmin($request);
});

Route::middleware(['auth:sanctum', 'ability:Admin'])->group(function () {
    Route::post('admin/counselor', function (Request $request) {
        return AdminController::createCounselor($request);
    });
    Route::get('admin/counselor', function (Request $request) {
        return AdminController::getAllCounselor($request);
    });
});
