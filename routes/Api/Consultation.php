<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Consultation\ConsultationController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('consultation/user', [ConsultationController::class, 'getConsultationByUserId']);
    Route::get('consultation/counselor', [ConsultationController::class, 'getConsultationByCounselorId']);
    Route::get('consultation', [ConsultationController::class, 'getConsultation']);
    Route::put('consultation/{id}', [ConsultationController::class, 'updateConsultation']);
    Route::post('consultation', [ConsultationController::class, 'createConsultation']);
    Route::patch('consultation/{id}/payment', [ConsultationController::class, 'checkConsulationPaymentStatus']);
    Route::get('consultation/{id}', [ConsultationController::class, 'getConsultationAndPaymentInfo']);
    Route::patch('consultation/{id}/cancel', [ConsultationController::class, 'cancelConsultation']);
});
