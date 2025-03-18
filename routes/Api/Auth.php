<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('register', function (Request $request) {
    return AuthController::register($request);
});

Route::post('login', function (Request $request) {
    return AuthController::login($request);
});

Route::patch('forget-password', function (Request $request) {
    return AuthController::forgetPassword($request);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout', function (Request $request) {
        return AuthController::logout($request);
    });

    Route::patch('change-password', function (Request $request) {
        return AuthController::changePassword($request);
    });

    Route::post('verify-token', function (Request $request) {
        return AuthController::verifySanctumToken($request);
    });
    Route::put('edit-profile', function (Request $request) {
        return AuthController::editProfile($request);
    });
});

Route::POST('edit/profile_image', function (Request $request) {
    return AuthController::editProfileImage($request);
});
