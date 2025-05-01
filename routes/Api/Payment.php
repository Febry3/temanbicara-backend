<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\PaymentController;

Route::post('/payment', [PaymentController::class, 'createPayment']);
Route::get('/payment/{uuid}', [PaymentController::class, 'checkPaymentStatus']);
Route::post('/notify/payment', function (Request $request) {
    return response()->json($request, 200);
});
