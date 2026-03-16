<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_no',
        'first_name',
        'middle_name',
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
        'tuition_paid',
        'has_scholarship',
        'scholarship_type',
    ];

    protected $appends = [
        'risk_score',
        'risk_level',
        'financial_status',
        'intervention_recommendation',
        'tuition_balance',
        'unpaid_percentage',
        'full_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Basic Computed Fields
    |--------------------------------------------------------------------------
    */

    public function getAgeAttribute()
    {
        return $this->date_of_birth
            ? Carbon::parse($this->date_of_birth)->age
            : null;
    }

    public function getFullNameAttribute()
    {
        return trim(
            $this->first_name . ' ' .
            ($this->middle_name ? $this->middle_name . ' ' : '') .
            $this->last_name
        );
    }

    public function getTuitionBalanceAttribute()
    {
        return max(0, (float)$this->tuition_total - (float)$this->tuition_paid);
    }

    public function getUnpaidPercentageAttribute()
    {
        if ((float)$this->tuition_total <= 0) {
            return 0;
        }

        return round(($this->tuition_balance / $this->tuition_total) * 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Academic Risk Score (Attendance 40% + Grade 40%)
    |--------------------------------------------------------------------------
    */

    private function academicScore()
    {
        $score = 0;

        $attendance = (float) ($this->attendance ?? 0);
        $grade = (float) ($this->grade ?? 0);

        // Attendance Risk
        if ($attendance < 70) {
            $score += 4;
        } elseif ($attendance <= 85) {
            $score += 2;
        }

        // Grade Risk (GPA scale)
        if ($grade >= 3.0) {
            $score += 4;
        } elseif ($grade >= 2.5) {
            $score += 2;
        }

        return $score;
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Risk Score (20%)
    |--------------------------------------------------------------------------
    */

    private function financialScore()
{
    $total = (float) ($this->tuition_total ?? 0);
    $paid  = (float) ($this->tuition_paid ?? 0);

    if ($total <= 0) {
        return 0;
    }

    $paidRatio = $paid / $total;

    if ($paidRatio < 0.50) {
        return 2;   // High financial risk
    }

    if ($paidRatio < 0.75) {
        return 1;   // Medium financial risk
    }

    return 0;       // Low financial risk
}

    /*
    |--------------------------------------------------------------------------
    | Combined Risk Score (Max = 10)
    |--------------------------------------------------------------------------
    */

    public function getRiskScoreAttribute()
    {
        $score = $this->academicScore() + $this->financialScore();

        return min($score, 10);
    }

    public function getRiskLevelAttribute()
    {
        $score = $this->academicScore() + $this->financialScore();

        if ($score >= 8) {
            return 'High Risk';
        }

        if ($score >= 5) {
            return 'Medium Risk';
        }

        return 'Low Risk';
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Status
    |--------------------------------------------------------------------------
    */

    public function getFinancialStatusAttribute()
    {
        $unpaid = $this->unpaid_percentage;

        if ($unpaid == 0) return 'Fully Paid';
        if ($unpaid >= 75) return 'Critical';
        if ($unpaid >= 40) return 'Partial';

        return 'Low Balance';
    }

    /*
    |--------------------------------------------------------------------------
    | Intervention Engine
    |--------------------------------------------------------------------------
    */

    public function getInterventionRecommendationAttribute()
    {
        $academicScore = $this->academicScore();
        $financialScore = $this->financialScore();

        $academicRisk = $academicScore >= 5;
        $financialRisk = $financialScore > 0;
        $financialCritical = $this->unpaid_percentage >= 75;

        if ($academicRisk && $financialCritical) {
            return 'Immediate Financial & Academic Intervention Required';
        }

        if ($academicRisk) {
            return 'Immediate Academic Counseling Required';
        }

        if ($financialRisk) {
            return 'Financial Monitoring Required';
        }

        return 'No Immediate Action Required';
    }
}