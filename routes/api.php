<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PerformanceController;
use App\Http\Controllers\API\ConsultationController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/performance-metrics', [PerformanceController::class, 'index']);
    Route::post('/consultations', [ConsultationController::class, 'store']);
    Route::get('/patients/search', [PatientController::class, 'search']);
    Route::get('/users/doctors', [UserController::class, 'getDoctors']);
});