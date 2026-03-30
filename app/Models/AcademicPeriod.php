<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'academic_year',
        'term',
        'period_rank',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}