<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Services\EnrollmentService;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

   public function enroll(Request $request)
{
    $request->validate([
        'student_id'         => 'required|exists:students,id',
        'academic_period_id' => 'required|exists:academic_periods,id',
        'grades'             => 'nullable|numeric|min:1.0|max:4.0',
        'attendance'         => 'nullable|numeric|min:0|max:100',
        'tuition_total'      => 'required|numeric|min:0',
        'tuition_paid'       => 'required|numeric|min:0',
    ]);

    $student = Student::findOrFail($request->student_id);

    // ✅ STEP 1 — cast first
    $grade         = ($request->grades !== null && $request->grades !== '')
                        ? (float) $request->grades : null;
    $attendance    = ($request->attendance !== null && $request->attendance !== '')
                        ? (float) $request->attendance : null;
    $tuition_paid  = (float) ($request->tuition_paid ?? 0);
    $tuition_total = (float) ($request->tuition_total ?? 0);

    // ✅ STEP 2 — financial status uses casted values
    $financialStatus = ($tuition_total > 0 && $tuition_paid < $tuition_total)
                        ? 'Low'
                        : 'Paid';

    // ✅ STEP 3 — call service and capture result
    $result = $this->enrollmentService->enroll(
        $student,
        $request->academic_period_id,
        $grade,
        $attendance,
        $financialStatus,
        $tuition_paid,
        $tuition_total
    );

    if ($result === null) {
        return back()->with('error', 'Student already enrolled in this term.');
    }

    return back()->with('success', 'Student enrolled successfully!');
}
}