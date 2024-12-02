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
Route::get('v1/logout', function (Request $request) {
    return AuthController::logout($request);
})->middleware('auth:sanctum');
