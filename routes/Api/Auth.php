<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forget-password', [AuthController::class, 'changePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('verify-token', [AuthController::class, 'verifySanctumToken']);
    Route::post('profile', [AuthController::class, 'editProfileData']);
    Route::post('profile/image', [AuthController::class, 'editProfileImage']);
    Route::get('profile', [AuthController::class, 'getUser']);
});

Route::post('password', [AuthController::class, 'forgetPassword']);
Route::post('password/otp', [AuthController::class, 'sendResetPasswordOTP']);
Route::post('password/otp/valid', [AuthController::class, 'verifyOtp']);
