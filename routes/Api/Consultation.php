<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Consultation\ConsultationController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('consultation-user', function (Request $request) {
        return ConsultationController::getConsultationByUserId($request);
    });
    Route::get('consultation-counselor', function (Request $request) {
        return ConsultationController::getConsultationByCounselorId($request);
    });
});
Route::get('consultation', function (Request $request) {
    return ConsultationController::getConsultation($request);
});

Route::put('consultation/{id}', function (Request $request, $id) {
    return ConsultationController::updateConsultation($request, $id);
});

Route::post('consultation', function (Request $request) {
    return ConsultationController::createConsultation($request);
});

