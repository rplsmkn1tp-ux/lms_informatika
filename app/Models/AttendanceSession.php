<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'classroom_id',
        'teacher_id',
        'academic_year_id',
        'meeting_date',
        'meeting_no',
        'topic',
        'status',
        'opened_at',
        'closed_at',
        'edit_reason',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }
}
