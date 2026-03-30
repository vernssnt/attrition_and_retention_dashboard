<?php

namespace App\Services;

class RiskCalculator
{
    public static function calculate($grade, $attendance, $tuition_total, $tuition_paid)
    {
        $score = 0;

        // ===== ACADEMIC =====
        if (!is_null($grade)) {
            if ($grade >= 3.0) $score += 4;
            elseif ($grade >= 2.5) $score += 2;
        }

        if (!is_null($attendance)) {
            if ($attendance < 70) $score += 4;
            elseif ($attendance <= 85) $score += 2;
        }

        // ===== FINANCIAL =====
        if ($tuition_total > 0) {
            $ratio = $tuition_paid / $tuition_total;

            if ($ratio < 0.50) $score += 2;
            elseif ($ratio < 0.75) $score += 1;
        }

        // ===== LEVEL =====
        if ($score >= 8) {
            $level = 'High';
        } elseif ($score >= 5) {
            $level = 'Medium';
        } else {
            $level = 'Low';
        }

        return [
            'final_risk' => $score,
            'risk_level' => $level
        ];
    }
}