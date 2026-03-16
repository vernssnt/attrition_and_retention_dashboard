<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\StudentApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Public for now (Power BI integration testing)
|--------------------------------------------------------------------------
*/

Route::get('/students', [StudentApiController::class, 'index']);

Route::get('/dashboard-summary', [StudentApiController::class, 'dashboardSummary']);

Route::get('/risk-distribution', [StudentApiController::class, 'riskDistribution']);

Route::get('/powerbi/students', function () {
    return DB::table('students')->get();
});
