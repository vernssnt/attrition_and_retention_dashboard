<?php

use Illuminate\Http\Request;
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

Route::get('/powerbi/student-risk-analytics', function (Request $request) {

    // Get API key from request header
    $apiKey = $request->header('X-API-KEY');

    // Check if API key matches
    if ($apiKey !== env('POWERBI_API_KEY')) {
        return response()->json([
            'error' => 'Unauthorized'
        ], 401);
    }

    // Return data if authorized
    return DB::table('student_risk_analytics')->get();
});