<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ActivityLog; 
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RBAC Constructor
    |--------------------------------------------------------------------------
    */
    public function __construct()
    {
        // Registrar only
        $this->middleware('role:registrar')->only([
            'create',
            'store',
            'destroy'
        ]);

        // Registrar + Cashier
        $this->middleware('role:registrar,cashier')->only([
            'edit',
            'update',
            'import'
        ]);
    }

    public function index(Request $request)
    {
        $students = Student::all();

        if ($request->filled('risk_level') && $request->risk_level !== 'All') {
            $students = $students->filter(function ($student) use ($request) {
                return $student->risk_level === $request->risk_level;
            });
        }

        if ($request->filled('year_level') && $request->year_level !== 'All') {
            $students = $students->where('year_level', $request->year_level);
        }

        $students = $students->values();

        $totalStudents   = $students->count();
        $activeStudents  = $students->where('status', 'active')->count();
        $droppedStudents = $students->where('status', 'dropped')->count();

        $highRisk   = $students->where('risk_level', 'High Risk')->count();
        $mediumRisk = $students->where('risk_level', 'Medium Risk')->count();
        $lowRisk    = $students->where('risk_level', 'Low Risk')->count();

        $maleCount   = $students->where('gender', 'Male')->count();
        $femaleCount = $students->where('gender', 'Female')->count();

        $retentionRate = $totalStudents > 0
            ? round(($activeStudents / $totalStudents) * 100, 2)
            : 0;

        return view('students.index', compact(
            'students',
            'totalStudents',
            'activeStudents',
            'droppedStudents',
            'highRisk',
            'mediumRisk',
            'lowRisk',
            'maleCount',
            'femaleCount',
            'retentionRate'
        ));
    }

    public function create()
    {
        return view('students.create');
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_no'      => 'required|unique:students,student_no',
            'first_name'      => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'program'         => 'required|string|max:255',
            'year_level'      => 'required',
            'attendance'      => 'nullable|numeric',
            'grade'           => 'nullable|numeric',
            'gender'          => 'nullable|in:Male,Female',
            'date_of_birth'   => 'nullable|date',
            'status'          => 'required',
            'enrollment_year' => 'required|numeric',
            'tuition_total'   => 'nullable|numeric',
            'tuition_paid'    => 'nullable|numeric',
            'has_scholarship' => 'nullable|boolean',
            'scholarship_type'=> 'required_if:has_scholarship,1|nullable|in:Indigency,Partnership,SHS Graduate'
        ]);

        $student = Student::create([
            ...$request->all(),
            'has_scholarship' => $request->has_scholarship ? 1 : 0
        ]);

        $this->logActivity(
            'Student Created',
            'Created student: ' . $student->student_no
        );

        return redirect()->route('students.index')
            ->with('success', 'Student added successfully.');
    }

    public function update(Request $request, Student $student)
    {
        if (Auth::user()->role === 'cashier') {

            $request->validate([
                'tuition_total' => 'nullable|numeric',
                'tuition_paid'  => 'nullable|numeric',
            ]);

            $student->update($request->only([
                'tuition_total',
                'tuition_paid'
            ]));

        } else {

            $request->validate([
                'student_no'      => 'required|unique:students,student_no,' . $student->id,
                'first_name'      => 'required|string|max:255',
                'middle_name'     => 'nullable|string|max:255',
                'last_name'       => 'required|string|max:255',
                'program'         => 'required|string|max:255',
                'year_level'      => 'required',
                'attendance'      => 'nullable|numeric',
                'grade'           => 'nullable|numeric',
                'gender'          => 'nullable|in:Male,Female',
                'date_of_birth'   => 'nullable|date',
                'status'          => 'required',
                'enrollment_year' => 'required|numeric',
                'tuition_total'   => 'nullable|numeric',
                'tuition_paid'    => 'nullable|numeric',
                'has_scholarship' => 'nullable|boolean',
                'scholarship_type'=> 'required_if:has_scholarship,1|nullable|in:Indigency,Partnership,SHS Graduate'
            ]);

            $student->update([
                ...$request->only([
                    'student_no',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'program',
                    'year_level',
                    'attendance',
                    'grade',
                    'gender',
                    'date_of_birth',
                    'status',
                    'enrollment_year',
                    'tuition_total',
                    'tuition_paid',
                    'scholarship_type'
                ]),
                'has_scholarship' => $request->has_scholarship ? 1 : 0
            ]);
        }

        $this->logActivity(
            'Student Updated',
            'Updated student: ' . $student->student_no
        );

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $studentNo = $student->student_no;
        $student->delete();

        $this->logActivity(
            'Student Deleted',
            'Deleted student: ' . $studentNo
        );

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new StudentsImport, $request->file('file'));

        $this->logActivity(
            'Excel Imported',
            'Uploaded student Excel file'
        );

        return redirect()->route('students.index')
            ->with('success', 'Excel file imported successfully.');
    }

    private function logActivity($action, $description)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}