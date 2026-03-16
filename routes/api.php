<?php

use Illuminate\Support\Facades\Route;
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
