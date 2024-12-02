<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('v1/register', function (Request $request) {
    return AuthController::register($request);
});
Route::post('v1/login', function (Request $request) {
    return AuthController::login($request);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('v1/logout', function (Request $request) {
        return AuthController::logout($request);
    });

    Route::patch('v1/changepassword', function (Request $request) {
        return AuthController::changePassword($request);
    });
});
