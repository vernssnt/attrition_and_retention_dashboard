<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request) 
{
    // ================= FILTERS =================
    $year = $request->year;
    $program = $request->program;

    // ================= BASE QUERY (WITH JOIN) =================
   $students = DB::table('students')
    ->leftJoin('enrollments as e', function ($join) {
        $join->on('students.id', '=', 'e.student_id')
            ->whereRaw('e.id = (
                SELECT id FROM enrollments
                WHERE student_id = students.id
                ORDER BY academic_period_id DESC
                LIMIT 1
            )');
    })
    ->select(
        'students.*',
        'students.enrollment_year',
        'students.program',
'students.status as enrollment_status',
        DB::raw('COALESCE(e.risk_level, students.risk_level) as risk_level')
    );

    // ================= APPLY FILTERS =================
    if ($year) {
        $students->where('students.enrollment_year', $year);
    }

    if ($program) {
        $students->where('students.program', $program);
    }

    // ================= GET FILTERED DATA =================
    $students = $students->get();

    // ================= KPI (NOW FILTERED) =================
    $totalStudents = $students->pluck('id')->unique()->count();

$activeStudents = $students
    ->filter(fn($s) => strtolower(trim($s->enrollment_status ?? '')) === 'active')
    ->pluck('id')
    ->unique()
    ->count();

$droppedStudents = $students
    ->filter(fn($s) => strtolower(trim($s->enrollment_status ?? '')) === 'dropped')
    ->pluck('id')
    ->unique()
    ->count();
    // ADD THESE:
$graduatedStudents = $students
    ->filter(fn($s) => strtolower(trim($s->enrollment_status ?? '')) === 'graduated')
    ->pluck('id')
    ->unique()
    ->count();

$graduationRate = $totalStudents > 0
    ? round(($graduatedStudents / $totalStudents) * 100, 2)
    : 0;

    // ================= RISK COUNTS =================
    $highRisk = $students->filter(fn($s) =>
        strtolower($s->risk_level ?? '') === 'high'
    )->count();

    $mediumRisk = $students->filter(fn($s) =>
        strtolower($s->risk_level ?? '') === 'medium'
    )->count();

    $lowRisk = $students->filter(fn($s) =>
        strtolower($s->risk_level ?? '') === 'low'
    )->count();

    // ================= RATES =================
    $retentionRate = $totalStudents > 0
        ? round(($activeStudents / $totalStudents) * 100, 2)
        : 0;

    $attritionRate = $totalStudents > 0
        ? round(($droppedStudents / $totalStudents) * 100, 2)
        : 0;

    // ================= ATTRITION BY YEAR (FILTERED) =================
    $attritionByYear = $students
    ->filter(fn($s) => strtolower(trim($s->enrollment_status ?? '')) === 'dropped')
    ->groupBy('enrollment_year')
    ->map(function ($group, $year) {
        return [
            'academic_year' => $year,
            'total' => $group->pluck('id')->unique()->count(),
        ];
    })
    ->values();

    // ================= RETENTION BY PROGRAM (FILTERED) =================
    $retentionByProgram = $students
        ->filter(fn($s) =>
           strtolower(trim($s->enrollment_status ?? '')) === 'active'
        )
        ->groupBy('program')
        ->map(fn($group) => [
    'program' => $group->first()->program,
    'total' => $group->pluck('id')->unique()->count(),
])
        ->values();

    // ================= FILTER DROPDOWNS =================
    $years = Student::whereNotNull('enrollment_year')
        ->distinct()
        ->orderBy('enrollment_year')
        ->pluck('enrollment_year');

    $programs = Student::whereNotNull('program')
        ->distinct()
        ->orderBy('program')
        ->pluck('program');

    // ================= ACTIVITY =================
    $activityData = [];

    if (Auth::user()->role === 'admin') {

        $today = now()->toDateString();

        $actionsToday = ActivityLog::whereDate('created_at', $today)->count();

        $studentUpdates = ActivityLog::where('action', 'Student Updated')->count();

        $excelUploads = ActivityLog::where('action', 'Excel Imported')->count();

        $mostActiveUser = ActivityLog::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->first();

        $activityData = [
            'actionsToday' => $actionsToday,
            'studentUpdates' => $studentUpdates,
            'excelUploads' => $excelUploads,
            'mostActiveUser' => $mostActiveUser,
        ];
    }

    return view('dashboard.index', compact(
        'totalStudents',
        'activeStudents',
        'droppedStudents',
        'graduatedStudents',   // ← ADD
        'graduationRate',
        'retentionRate',
        'attritionRate',
        'attritionByYear',
        'retentionByProgram',
        'years',
        'programs',
        'activityData',
        'highRisk',
        'mediumRisk',
        'lowRisk'
    ));
}
        /*
        |--------------------------------------------------------------------------
        | ADMIN ACTIVITY ANALYTICS (ADDED SECTION)
        |--------------------------------------------------------------------------
        */

    }