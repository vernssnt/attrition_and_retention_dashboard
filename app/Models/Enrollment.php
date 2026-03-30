<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AcademicPeriod;

class Enrollment extends Model
{
    protected $fillable = [
    'student_id',
    'academic_period_id',
    'year_level',
    'grade',
    'attendance',
    'tuition_total',
    'tuition_paid',
    'adjustment',
    'status',
    'final_risk',
    'risk_level',
    'intervention',
    
];

    // 🔥 Relationship to Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // 🔥 Relationship to Academic Period
    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }
}