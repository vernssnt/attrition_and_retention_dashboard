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

/* Power BI Student Data */
Route::get('/powerbi/students', function () {
    return DB::table('students')->get();
});

/* Power BI Risk Data */
Route::get('/powerbi/risk-data', function () {
    return DB::table('students')
        ->select(
            'id',
            'program',
            'year_level',
            'grade',
            'attendance',
            'tuition_paid',
            'tuition_total'
        )
        ->get();
});
