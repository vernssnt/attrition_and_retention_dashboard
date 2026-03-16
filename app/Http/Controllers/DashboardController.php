<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $year = $request->year;
        $program = $request->program;

        $query = Student::query();

        if ($year) {
            $query->where('enrollment_year', $year);
        }

        if ($program) {
            $query->where('program', $program);
        }

        // Summary counts
        $totalStudents   = $query->count();
        $activeStudents  = (clone $query)->where('status', 'active')->count();
        $droppedStudents = (clone $query)->where('status', 'dropped')->count();

        $retentionRate = $totalStudents > 0
            ? round(($activeStudents / $totalStudents) * 100, 2)
            : 0;

        $attritionRate = $totalStudents > 0
            ? round(($droppedStudents / $totalStudents) * 100, 2)
            : 0;

        // Attrition by year
        $attritionByYear = Student::selectRaw('enrollment_year, COUNT(*) as total')
            ->where('status', 'dropped')
            ->groupBy('enrollment_year')
            ->orderBy('enrollment_year')
            ->get();

        // Retention by program
        $retentionByProgram = Student::selectRaw('program, COUNT(*) as total')
            ->where('status', 'active')
            ->groupBy('program')
            ->orderBy('program')
            ->get();

        // Filter dropdown values
        $years = Student::whereNotNull('enrollment_year')
            ->distinct()
            ->orderBy('enrollment_year')
            ->pluck('enrollment_year');

        $programs = Student::whereNotNull('program')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');

        /*
        |--------------------------------------------------------------------------
        | ADMIN ACTIVITY ANALYTICS (ADDED SECTION)
        |--------------------------------------------------------------------------
        */

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
            'retentionRate',
            'attritionRate',
            'attritionByYear',
            'retentionByProgram',
            'years',
            'programs',
            'activityData' // ✅ Added
        ));
    }
}