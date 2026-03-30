<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\RiskCalculator;

class StudentsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function chunkSize(): int
{
    return 200;
}
    private $academicPeriods = [];
    public function collection(Collection $rows)
{
    // ✅ SORT FIRST (VERY IMPORTANT)
    $rows = $rows->sortBy(function ($row) {
        return $row['academic_year'] . '-' . (int) filter_var($row['term'], FILTER_SANITIZE_NUMBER_INT);
    });



    try {

        foreach ($rows as $row) {

            // ================= DATE FORMAT =================
            $dob = null;
            if (!empty($row['date_of_birth'])) {
                if (is_numeric($row['date_of_birth'])) {
                    $dob = Date::excelToDateTimeObject($row['date_of_birth'])->format('Y-m-d');
                } else {
                    $dob = date('Y-m-d', strtotime($row['date_of_birth']));
                }
            }

            // ================= SCHOLARSHIP =================
            $hasScholarship = 0;
            if (!empty($row['has_scholarship'])) {
                $val = strtolower(trim($row['has_scholarship']));
                $hasScholarship = ($val === 'yes' || $val === '1') ? 1 : 0;
            }

            $allowedTypes = ['Indigency', 'Partnership', 'SHS Graduate'];
            $scholarshipType = in_array($row['scholarship_type'] ?? '', $allowedTypes)
                ? $row['scholarship_type']
                : null;

            // ================= ENROLLMENT YEAR (FIXED) =================
            $enrollmentYear = isset($row['enrollment_year']) && $row['enrollment_year'] !== ''
                ? (int) $row['enrollment_year']
                : null;

            // ================= STUDENT =================
            $studentData = [
                'first_name'      => $row['first_name'] ?? null,
                'middle_name'     => $row['middle_name'] ?? null,
                'last_name'       => $row['last_name'] ?? null,
                'gender'          => $row['gender'] ?? null,
                'date_of_birth'   => $dob,
                'program'         => $row['program'] ?? null,
                'year_level'      => $row['year_level'] ?? null,
                'status'          => $row['status'] ?? 'Active',
                'has_scholarship' => $hasScholarship,
                'scholarship_type'=> $scholarshipType,
            ];

            // ✅ Only set if present in Excel (prevents overwrite)
            if (!empty($enrollmentYear)) {
                $studentData['enrollment_year'] = $enrollmentYear;
            }

            $student = Student::updateOrCreate(
                ['student_no' => $row['student_no']],
                $studentData
            );

            // ================= VALIDATION =================
            if (empty($row['academic_year']) || empty($row['term'])) {
                continue;
            }

            // ================= ACADEMIC PERIOD =================
            $key = $row['academic_year'] . '-' . $row['term'];

if (!isset($this->academicPeriods[$key])) {

    $existing = DB::table('academic_periods')
        ->where('academic_year', $row['academic_year'])
        ->where('term', $row['term'])
        ->first();

    if ($existing) {
        $this->academicPeriods[$key] = $existing->id;
    } else {
        $this->academicPeriods[$key] = DB::table('academic_periods')->insertGetId([
            'academic_year' => $row['academic_year'],
            'term' => $row['term'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

$academicPeriodId = $this->academicPeriods[$key];

            // ================= ENROLLMENT DATA =================
            $grade = $row['grade'] ?? null;
            $attendance = $row['attendance'] ?? null;
            $tuition_total = $row['tuition_total'] ?? 0;
            $tuition_paid = $row['tuition_paid'] ?? 0;

            // ✅ COMPUTE FINANCIAL STATUS
            $financialStatus = ($tuition_paid >= $tuition_total) ? 'Paid' : 'Low';

            // ================= USE RISK ENGINE =================
            app(\App\Services\EnrollmentService::class)->enroll(
                $student,
    $academicPeriodId,
    $grade,
    $attendance,
    $financialStatus,
    $tuition_paid,
    $tuition_total
);
        }


    } catch (\Exception $e) {
    \Log::error('Import Error: ' . $e->getMessage());
}
}
}