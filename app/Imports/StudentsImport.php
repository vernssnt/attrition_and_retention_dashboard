<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $user = Auth::user();

        // ===============================
        // Convert Excel date properly
        // ===============================
        $dob = null;

        if (!empty($row['date_of_birth'])) {
            if (is_numeric($row['date_of_birth'])) {
                $dob = Date::excelToDateTimeObject($row['date_of_birth'])->format('Y-m-d');
            } else {
                $dob = date('Y-m-d', strtotime($row['date_of_birth']));
            }
        }

        // ===============================
        // Handle Scholarship Boolean
        // ===============================
        $hasScholarship = 0;

        if (!empty($row['has_scholarship'])) {
            $value = strtolower(trim($row['has_scholarship']));
            $hasScholarship = ($value === 'yes' || $value === '1') ? 1 : 0;
        }

        // ===============================
        // Validate Scholarship Type
        // ===============================
        $allowedTypes = ['Indigency', 'Partnership', 'SHS Graduate'];
        $scholarshipType = null;

        if (!empty($row['scholarship_type']) && in_array($row['scholarship_type'], $allowedTypes)) {
            $scholarshipType = $row['scholarship_type'];
        }

        // ===============================
        // CASHIER IMPORT (Financial Only)
        // ===============================
        if ($user && $user->role === 'cashier') {

            $student = Student::where('student_no', $row['student_no'])->first();

            // Only update existing student
            if ($student) {
                $student->update([
                    'tuition_total' => $row['tuition_total'] ?? $student->tuition_total,
                    'tuition_paid'  => $row['tuition_paid'] ?? $student->tuition_paid,
                ]);
            }

            return null; // Prevent creating new students
        }

        // ===============================
        // REGISTRAR IMPORT (Full Control)
        // ===============================
        return Student::updateOrCreate(
            ['student_no' => $row['student_no']],

            [
                'first_name'      => $row['first_name'] ?? null,
                'middle_name'     => $row['middle_name'] ?? null,
                'last_name'       => $row['last_name'] ?? null,
                'gender'          => $row['gender'] ?? null,
                'date_of_birth'   => $dob,
                'program'         => $row['program'] ?? null,
                'year_level'      => $row['year_level'] ?? null,
                'attendance'      => $row['attendance'] ?? null,
                'grade'           => $row['grade'] ?? null,
                'status'          => $row['status'] ?? 'active',
                'enrollment_year' => $row['enrollment_year'] ?? null,
                'tuition_total'   => $row['tuition_total'] ?? 0,
                'tuition_paid'    => $row['tuition_paid'] ?? 0,

                // Scholarship Fields
                'has_scholarship' => $hasScholarship,
                'scholarship_type'=> $scholarshipType,
            ]
        );
    }
}