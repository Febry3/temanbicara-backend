<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::post('/payment', [PaymentController::class, 'createPayment']);
Route::get('/payment/{uuid}', [PaymentController::class, 'checkPaymentStatus']);
Route::post('/notify/payment', [PaymentController::class, 'handlePaymentNotification']);
