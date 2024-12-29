<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Consultation\ConsultationController;

Route::get('consultation', function (Request $request) {
    return ConsultationController::getConsultation($request);
});

Route::put('consultation/{id}', function (Request $request, $id) {
    return ConsultationController::updateConsultation($request, $id);
});

Route::post('consultation', function (Request $request) {
    return ConsultationController::createConsultation($request);
});
