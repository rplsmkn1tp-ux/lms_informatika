<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BellPeriod extends Model
{
    protected $fillable = [
        'academic_year_id',
        'day_of_week',
        'period_no',
        'starts_at',
        'ends_at',
        'type',
        'label',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
