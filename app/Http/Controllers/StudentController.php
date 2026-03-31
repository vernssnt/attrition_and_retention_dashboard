<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ActivityLog; 
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Enrollment;
use App\Services\RiskCalculator;
use App\Services\EnrollmentService;


class StudentController extends Controller {
    
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
{
    $this->enrollmentService = $enrollmentService;

    $this->middleware('role:registrar')->only([
        'create','store','destroy'
    ]);

    $this->middleware('role:registrar,cashier')->only([
        'edit','update','import'
    ]);
}

    /* ================= CREATE ================= */
    public function create()
    {
        $academicPeriods = DB::table('academic_periods')->get();

        return view('students.create', compact('academicPeriods'));
    }   
    
    // 👉 ADD YOUR OTHER FUNCTIONS BELOW (store, edit, update, index)


    /* ================= STORE ================= */
  public function store(Request $request)
{
    DB::beginTransaction();

    try {
        // ✅ Save student
        $student = DB::table('students')->insertGetId([
            'student_no' => $request->student_no,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'program' => $request->program,
            'year_level' => $request->year_level,
            'has_scholarship' => $request->has_scholarship,
            'scholarship_type' => $request->scholarship_type,
            'status' => $request->status,
            'enrollment_year' => $request->enrollment_year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✅ Save academic records
       if ($request->has('records')) {
    foreach ($request->records as $record) {
        if (empty($record['academic_period_id'])) continue;

        $grade         = ($record['grade'] !== null && $record['grade'] !== '')
                            ? (float) $record['grade'] : null;
        $attendance    = ($record['attendance'] !== null && $record['attendance'] !== '')
                            ? (float) $record['attendance'] : null;
        $tuition_paid  = (float) ($record['tuition_paid'] ?? 0);
        $tuition_total = (float) ($record['tuition_total'] ?? 0);

        $financialStatus = ($tuition_total > 0 && $tuition_paid < $tuition_total)
                            ? 'Low' : 'Paid';

        $studentModel = Student::find($student);

        $this->enrollmentService->enroll(
            $studentModel,
            $record['academic_period_id'],
            $grade,
            $attendance,
            $financialStatus,
            $tuition_paid,
            $tuition_total
        );
    }
}

        DB::commit();

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors($e->getMessage());
    }
}
    /* ================= EDIT ================= */
   public function edit(Student $student)
{
    $enrollments = DB::table('enrollments')
        ->join('academic_periods', 'enrollments.academic_period_id', '=', 'academic_periods.id')
        ->where('enrollments.student_id', $student->id)
        ->select(
            'enrollments.id', // ✅ VERY IMPORTANT
            'enrollments.academic_period_id',
            'enrollments.grade',
            'enrollments.attendance',
             'enrollments.tuition_total',
            'enrollments.tuition_paid',
            'academic_periods.academic_year',
            'academic_periods.term'
        )
        ->orderBy('academic_periods.academic_year')
        ->orderBy('academic_periods.id')
        ->get();

    $academicPeriods = DB::table('academic_periods')->get();

    return view('students.edit', compact('student', 'enrollments', 'academicPeriods'));
}

    /* ================= UPDATE ================= */
    public function update(Request $request, Student $student)
{
    DB::beginTransaction();

    try {

        // ================= CASHIER =================
        if (Auth::user()->role === 'cashier') {

            $request->validate([
                'records.*.tuition_total' => 'nullable|numeric',
                'records.*.tuition_paid'  => 'nullable|numeric',
            ]);

            if (!empty($request->records)) {
                foreach ($request->records as $record) {

                    // ✅ UPDATE EXISTING RECORD
                    if (!empty($record['id'])) {

    $risk = RiskCalculator::calculate(
        $record['grade'] ?? null,
        $record['attendance'] ?? null,
        $record['tuition_total'] ?? 0,
        $record['tuition_paid'] ?? 0
    );

    // ✅ ADD THIS HERE
    $intervention = app(\App\Services\EnrollmentService::class)
        ->getIntervention(
            $record['grade'] ?? null,
            $record['attendance'] ?? null,
            $record['tuition_paid'] ?? 0,
            $record['tuition_total'] ?? 0
        );

    DB::table('enrollments')
        ->where('id', $record['id'])
        ->update([
            'grade' => $record['grade'] ?? null,
            'attendance' => $record['attendance'] ?? null,
            'tuition_total' => $record['tuition_total'] ?? 0,
            'tuition_paid' => $record['tuition_paid'] ?? 0,
            'adjustment' => $record['adjustment'] ?? 0,
            'status' => $record['status'] ?? 'Active',
            'final_risk' => $risk['final_risk'],
            'risk_level' => $risk['risk_level'],
            'intervention' => $intervention, // ✅ ADD THIS LINE
            'updated_at' => now(),
        ]);
}
                    

                    // ✅ INSERT NEW TERM (FIXED HERE)
                    else {

                        // 🚨 IMPORTANT: use service instead of manual insert
                      $tuition_paid  = (float) ($record['tuition_paid'] ?? 0);
$tuition_total = (float) ($record['tuition_total'] ?? 0);
$grade         = ($record['grade'] !== null && $record['grade'] !== '')
                    ? (float) $record['grade'] : null;
$attendance    = ($record['attendance'] !== null && $record['attendance'] !== '')
                    ? (float) $record['attendance'] : null;

$financialStatus = ($tuition_total > 0 && $tuition_paid < $tuition_total) ? 'Low' : 'Paid';

$this->enrollmentService->enroll(
    $student,
    $record['academic_period_id'],
    $grade,
    $attendance,
    $financialStatus,
    $tuition_paid,
    $tuition_total
);
                    }
                }
            }

        }
        

        // ================= REGISTRAR =================
        else {

            $request->validate([
                'student_no'      => 'required|unique:students,student_no,' . $student->id,
                'first_name'      => 'required|string|max:255',
                'middle_name'     => 'nullable|string|max:255',
                'last_name'       => 'required|string|max:255',
                'program'         => 'required|string|max:255',
                'year_level'      => 'required',
                'gender'          => 'nullable|in:Male,Female',
                'date_of_birth'   => 'nullable|date',
                'status'          => 'required|in:Active,Dropped,Graduated',
                'enrollment_year' => 'required|numeric',
                'has_scholarship' => 'nullable|in:0,1',
                'scholarship_type'=> 'nullable|in:Indigency,Partnership,SHS Graduate'
            ]);

            // ✅ UPDATE STUDENT
            $student->update([
                'student_no' => $request->student_no,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'program' => $request->program,
                'year_level' => $request->year_level,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'status' => $request->status,
                'enrollment_year' => $request->enrollment_year,
                'has_scholarship' => $request->input('has_scholarship', 0),
                'scholarship_type' => $request->scholarship_type,
            ]);

            if (!empty($request->records)) {
                foreach ($request->records as $record) {

                    if (
                        empty($record['academic_period_id']) &&
                        empty($record['grade']) &&
                        empty($record['attendance']) &&
                        empty($record['tuition_total']) &&
                        empty($record['tuition_paid'])
                    ) {
                        continue;
                    }

                    // ✅ UPDATE EXISTING
                    if (!empty($record['id'])) {

                        $risk = RiskCalculator::calculate(
                            $record['grade'] ?? null,
                            $record['attendance'] ?? null,
                            $record['tuition_total'] ?? 0,
                            $record['tuition_paid'] ?? 0
                        );
                        $intervention = app(\App\Services\EnrollmentService::class)
    ->getIntervention(
        $record['grade'] ?? null,
        $record['attendance'] ?? null,
        $record['tuition_paid'] ?? 0,
        $record['tuition_total'] ?? 0
    );

                        DB::table('enrollments')
                            ->where('id', $record['id'])
                            ->update([
                                'grade' => $record['grade'] ?? null,
                                'attendance' => $record['attendance'] ?? null,
                                'tuition_total' => $record['tuition_total'] ?? 0,
                                'tuition_paid' => $record['tuition_paid'] ?? 0,
                                'adjustment' => $record['adjustment'] ?? 0,
                                'status' => $record['status'] ?? 'Active',
                                'final_risk' => $risk['final_risk'],
                                'risk_level' => $risk['risk_level'],
                                'intervention' => $intervention,
                                'updated_at' => now(),
                            ]);
                    }

                    // ✅ INSERT NEW TERM (FIXED)
                    else {

                       $tuition_paid  = (float) ($record['tuition_paid'] ?? 0);
$tuition_total = (float) ($record['tuition_total'] ?? 0);
$grade         = ($record['grade'] !== null && $record['grade'] !== '')
                    ? (float) $record['grade'] : null;
$attendance    = ($record['attendance'] !== null && $record['attendance'] !== '')
                    ? (float) $record['attendance'] : null;

$financialStatus = ($tuition_total > 0 && $tuition_paid < $tuition_total) ? 'Low' : 'Paid';

$this->enrollmentService->enroll(
    $student,
    $record['academic_period_id'],
    $grade,
    $attendance,
    $financialStatus,
    $tuition_paid,
    $tuition_total
);
                    }
                }
            }
        }

        DB::commit();

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', $e->getMessage());
    }
    
}

    public function index(Request $request) 
{
    set_time_limit(150); // 120 seconds
    // ✅ Step 1: Get latest enrollment per student
    $latestEnrollments = DB::table('enrollments')
        ->select('student_id', DB::raw('MAX(id) as latest_id'))
        ->groupBy('student_id');

    // ✅ Step 2: Main query
    $students = DB::table('students')
        ->leftJoinSub($latestEnrollments, 'latest_enrollments', function ($join) {
            $join->on('students.id', '=', 'latest_enrollments.student_id');
        })
        ->leftJoin('enrollments as e', 'e.id', '=', 'latest_enrollments.latest_id')
        ->leftJoin('academic_periods as ap', 'ap.id', '=', 'e.academic_period_id')
        ->select(
            'students.*',
            DB::raw("COALESCE(e.year_level::text, students.year_level) as year_level"),
            'e.attendance',
            'e.grade',
            'students.status as enrollment_status',
            'e.final_risk',
            'e.risk_level',
            'e.intervention',
            'ap.academic_year', // 👈 ADD
            'ap.term',
            DB::raw('e.id as enrollment_id') 
                    
        );

    // ✅ Filters
    if ($request->filled('risk_level') && $request->risk_level !== '') {
        $students->where('e.risk_level', $request->risk_level);
    }

    if ($request->filled('year_level') && $request->year_level !== '') {
        $yearMap = [
            '1st year' => 1,
            '2nd year' => 2,
            '3rd year' => 3,
            '4th year' => 4,
        ];
        $yearInt = $yearMap[$request->year_level] ?? null;

        if ($yearInt) {
            $students->where('e.year_level', $yearInt);
        }
    }

    if ($request->filled('status') && $request->status !== '') {
        $students->where('students.status', $request->status);
    }

    if ($request->filled('academic_year') && $request->academic_year !== '') {
    $students->where('ap.academic_year', $request->academic_year);
}

if ($request->filled('term') && $request->term !== '') {
    $students->where('ap.term', $request->term);
}

    // ✅ Execute query
    $students = $students->get();

    // ✅ Stats
    $totalStudents     = $students->count();
    $activeStudents    = $students->filter(fn($s) => strtolower($s->enrollment_status ?? '') === 'active')->count();
    $droppedStudents   = $students->filter(fn($s) => strtolower($s->enrollment_status ?? '') === 'dropped')->count();
    $graduatedStudents = $students->filter(fn($s) => strtolower($s->enrollment_status ?? '') === 'graduated')->count();

    $highRisk   = $students->filter(fn($s) => ($s->risk_level ?? '') === 'High')->count();
    $mediumRisk = $students->filter(fn($s) => ($s->risk_level ?? '') === 'Medium')->count();
    $lowRisk    = $students->filter(fn($s) => ($s->risk_level ?? '') === 'Low')->count();

    $maleCount   = $students->where('gender', 'Male')->count();
    $femaleCount = $students->where('gender', 'Female')->count();

    $retentionRate = $totalStudents > 0
        ? round(($activeStudents / $totalStudents) * 100, 2)
        : 0;

    // ✅ Student history
    $studentHistories = DB::table('enrollments')
        ->join('academic_periods', 'enrollments.academic_period_id', '=', 'academic_periods.id')
        ->select(
            'enrollments.student_id',
            'academic_periods.academic_year',
            'academic_periods.term',
            'enrollments.final_risk'
        )
        ->orderBy('academic_periods.id')
        ->get()
        ->groupBy('student_id');
    
        $academicYears = DB::table('academic_periods')
    ->select('academic_year')
    ->distinct()
    ->orderByDesc('academic_year')
    ->pluck('academic_year');

    return view('students.index', compact(
        'students',
        'academicYears',
        'totalStudents',
        'activeStudents',
        'droppedStudents',
        'graduatedStudents',
        'highRisk',
        'mediumRisk',
        'lowRisk',
        'maleCount',
        'femaleCount',
        'retentionRate',
        'studentHistories'
    ));
}
// AFTER (fixed)
public function import(Request $request)
{

    $request->validate([
        'file' => 'required|mimes:xlsx,csv'
    ]);

    \Maatwebsite\Excel\Facades\Excel::import(
        new \App\Imports\StudentsImport,  // ← import class (arg 1)
        $request->file('file')            // ← the file (arg 2)
    );

    return back()->with('success', 'Students imported successfully!');
}
public function destroy($id)
{
    $student = \App\Models\Student::findOrFail($id);

    $student->delete();

    return redirect()->back()->with('success', 'Student deleted successfully.');
}
}