<?php

namespace App\Services;

use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicPeriod;


class EnrollmentService
{
    


  public function enroll($student, $academicPeriodId, $grades, $attendance, $financialStatus, $tuition_paid, $tuition_total)
{
// 🔹 Prevent duplicate enrollment (SAFETY)
$exists = Enrollment::where('student_id', $student->id)
    ->where('academic_period_id', $academicPeriodId)
    ->exists();

if ($exists) {
    return null; // or throw exception if you want stricter control
}

    // 🔹 Get previous enrollment
   $currentPeriod = AcademicPeriod::find($academicPeriodId);

$previous = Enrollment::with('academicPeriod')
    ->where('student_id', $student->id)
    ->get()
    ->filter(function ($enrollment) use ($currentPeriod) {
        return $enrollment->academicPeriod &&
               $enrollment->academicPeriod->period_rank < $currentPeriod->period_rank;
    })
    ->sortByDesc(function ($enrollment) {
        return $enrollment->academicPeriod->period_rank ?? 0;
    })
    ->first();

    // 🔹 BASE RISK (keep your function)
    $baseRisk = $this->computeRisk($grades, $attendance, $financialStatus);

    // 🔹 PERSISTENCE ADJUSTMENT
 
    $adjustment = 0;
$isNextTerm = false;

if ($previous && $currentPeriod && $previous->academicPeriod) {
    $isNextTerm =
        $previous->academicPeriod->period_rank + 1
        == $currentPeriod->period_rank;
}

    // 🔹 FINAL RISK (FIXED LOGIC)
if ($previous && $previous->final_risk >= 8 && $isNextTerm) {
    $finalRisk = max(0, $previous->final_risk - 1);
} else {
    $finalRisk = $baseRisk;
}

    // 🔹 RISK LEVEL
    $riskLevel = $this->getRiskLevel($finalRisk);

    $intervention = $this->getIntervention($grades, $attendance, $tuition_paid, $tuition_total);

    // 🔹 YEAR LEVEL (keep simple for now)
    $yearLevel = $student->year_level;

    return Enrollment::create([
        'student_id' => $student->id,
        'academic_period_id' => $academicPeriodId,
        'year_level' => $yearLevel,

        'grade' => $grades,
        'attendance' => $attendance,
        'financial_status' => $financialStatus,
           'tuition_paid' => $tuition_paid,      // ✅ ADD THIS
    'tuition_total' => $tuition_total,    // ✅ ADD THIS

        'base_risk' => $baseRisk,
        'final_risk' => $finalRisk,
        'adjustment' => $adjustment,
        'risk_level' => $riskLevel,
    'intervention' => $intervention,
        'status' => 'Active',
    ]);
    
}


    private function computeRisk($grades, $attendance, $financialStatus)
{
    $score = 0;

    // ================= ATTENDANCE =================
    if ($attendance < 70) {
        $score += 4;
    } elseif ($attendance <= 85) {
        $score += 2;
    }

    // ================= GRADES (GPA) =================
    if ($grades >= 3.0) {
        $score += 4;
    } elseif ($grades >= 2.5) {
        $score += 2;
    }

    // ================= FINANCIAL =================
    if ($financialStatus === 'Low') {
        $score += 2;
    }

    return min($score, 10); // max 10
}

    private function getRiskLevel($score)
{
    if ($score >= 8) return 'High';
    if ($score >= 5) return 'Medium';
    return 'Low';
}
// In EnrollmentService.php
public function getIntervention($grades, $attendance, $tuition_paid, $tuition_total)
{
    $interventions = [];

    $grades     = ($grades !== null && $grades !== '') ? (float) $grades : null;
    $attendance = ($attendance !== null && $attendance !== '') ? (float) $attendance : null;
    $tuition_paid  = (float) ($tuition_paid ?? 0);
    $tuition_total = (float) ($tuition_total ?? 0);

    if (!is_null($grades) && $grades >= 2.5) {
        $interventions[] = 'academic';
    }

    if (!is_null($attendance) && $attendance <= 85) {
        $interventions[] = 'academic';
    }

    if ($tuition_total > 0 && $tuition_paid < $tuition_total) {
        $interventions[] = 'financial';
    }

    return !empty($interventions)
        ? implode(',', array_unique($interventions))
        : null;
}
}
