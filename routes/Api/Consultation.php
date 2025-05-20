<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsultationController;
use Illuminate\Support\Carbon;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('consultation/user', [ConsultationController::class, 'getConsultationByUserId']);
    Route::get('consultation/counselor', [ConsultationController::class, 'getConsultationByCounselorId']);
    Route::get('consultation', [ConsultationController::class, 'getConsultation']);
    Route::put('consultation/{id}', [ConsultationController::class, 'updateConsultation']);
    Route::post('consultation', [ConsultationController::class, 'createConsultation']);
    Route::patch('consultation/{id}/payment', [ConsultationController::class, 'checkConsulationPaymentStatus']);
    Route::get('consultation/{id}', [ConsultationController::class, 'getConsultationAndPaymentInfo']);
    Route::patch('consultation/{id}/cancel', [ConsultationController::class, 'cancelConsultation']);
    Route::post('bookingHistory', [ConsultationController::class, 'bookingHistoryConsultation']);
    Route::get('/server-time', function () {
        return Carbon::now();
    });
});
