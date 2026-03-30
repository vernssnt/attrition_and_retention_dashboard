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

// 🔐 Helper function for API Key checking
function checkApiKey($request) {
    return $request->header('X-API-KEY') === env('POWERBI_API_KEY');
}

/*
|--------------------------------------------------------------------------
| BASIC TABLE ENDPOINTS
|--------------------------------------------------------------------------
*/

// ✅ STUDENTS
Route::get('/students', function (Request $request) {

    if (!checkApiKey($request)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return DB::table('students')->get();
});

// ✅ ACADEMIC PERIODS
Route::get('/academic-periods', function (Request $request) {

    if (!checkApiKey($request)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return DB::table('academic_periods')->get();
});

// ✅ ENROLLMENTS
Route::get('/enrollments', function (Request $request) {

    if (!checkApiKey($request)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return DB::table('enrollments')->get();
});

/*
|--------------------------------------------------------------------------
| EXISTING CONTROLLER ROUTES (UNCHANGED)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard-summary', [StudentApiController::class, 'dashboardSummary']);

Route::get('/risk-distribution', [StudentApiController::class, 'riskDistribution']);

/*
|--------------------------------------------------------------------------
| EXISTING POWER BI TABLE (UNCHANGED)
|--------------------------------------------------------------------------
*/

Route::get('/powerbi/student-risk-analytics', function (Request $request) {

    if (!checkApiKey($request)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return DB::table('student_risk_analytics')->get();
});

/*
|--------------------------------------------------------------------------
| 🔥 MAIN POWER BI DATA (JOINED - BEST FOR DASHBOARD)
|--------------------------------------------------------------------------
*/

Route::get('/powerbi/full-data', function (Request $request) {

    if (!checkApiKey($request)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return DB::table('enrollments')
        ->join('students', 'enrollments.student_id', '=', 'students.id')
        ->join('academic_periods', 'enrollments.academic_period_id', '=', 'academic_periods.id')
        ->select(
            // STUDENT INFO
            'students.id as student_id',
            'students.student_no',
            'students.first_name',
            'students.middle_name',
            'students.last_name',
            'students.gender',
            'students.program',
            'students.year_level',
            'students.status as student_status',
            'students.has_scholarship',
            'students.scholarship_type',
            'students.date_of_birth',

            // ENROLLMENT INFO
            'enrollments.id as enrollment_id',
            'enrollments.status as enrollment_status',
            'enrollments.attendance',
            'enrollments.grade',
            'enrollments.tuition_total',
            'enrollments.tuition_paid',
            'enrollments.base_risk',
            'enrollments.final_risk',
            'enrollments.risk_level',
            'enrollments.intervention',
            'enrollments.financial_status',
            'enrollments.adjustment',
            'enrollments.is_latest',

            // ACADEMIC PERIOD
            'academic_periods.academic_year',
            'academic_periods.term'
        )
        // ✅ OPTIONAL (prevents duplicates if needed)
        // ->where('enrollments.is_latest', 1)

        ->get();
});