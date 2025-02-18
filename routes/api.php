// routes/api.php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/performance-metrics', 'API\PerformanceController@index');
    Route::post('/consultations', 'API\ConsultationController@store');
});
