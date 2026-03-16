<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1️⃣ Get All Students (Analytics Ready)
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $students = Student::select([
            'id',
            'student_no',
            'first_name',
            'last_name',
            'program',
            'year_level',
            'attendance',
            'grade',
            'status',
            'enrollment_year',
            'gender',
            'date_of_birth',
            'tuition_total',
            'tuition_paid'
        ])->get();

        return response()->json([
            'data' => $students
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | 2️⃣ Dashboard Summary
    |--------------------------------------------------------------------------
    */

    public function dashboardSummary()
    {
        $students = Student::all();

        $totalStudents   = $students->count();
        $activeStudents  = $students->where('status', 'active')->count();
        $droppedStudents = $students->where('status', 'dropped')->count();

        $highRisk   = $students->where('risk_level', 'High Risk')->count();
        $mediumRisk = $students->where('risk_level', 'Medium Risk')->count();
        $lowRisk    = $students->where('risk_level', 'Low Risk')->count();

        $retentionRate = $totalStudents > 0
            ? round(($activeStudents / $totalStudents) * 100, 2)
            : 0;

        return response()->json([
            'total_students'   => $totalStudents,
            'active_students'  => $activeStudents,
            'dropped_students' => $droppedStudents,
            'high_risk'        => $highRisk,
            'medium_risk'      => $mediumRisk,
            'low_risk'         => $lowRisk,
            'retention_rate'   => $retentionRate
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | 3️⃣ Risk Distribution Only (For Charts)
    |--------------------------------------------------------------------------
    */

    public function riskDistribution()
    {
        $students = Student::all();

        return response()->json([
            'high_risk'   => $students->where('risk_level', 'High Risk')->count(),
            'medium_risk' => $students->where('risk_level', 'Medium Risk')->count(),
            'low_risk'    => $students->where('risk_level', 'Low Risk')->count(),
        ]);
    }
}
