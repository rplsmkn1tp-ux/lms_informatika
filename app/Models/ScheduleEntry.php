<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleEntry extends Model
{
    protected $fillable = [
        'teaching_assignment_id',
        'day_of_week',
        'start_period_no',
        'end_period_no',
        'room',
        'note',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }
}
