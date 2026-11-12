<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['name', 'join_code', 'academic_year_id', 'grade', 'major', 'join_code_active', 'weekly_lesson_target'];

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_classroom')->withPivot('available_from')->withTimestamps();
    }

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class, 'assessment_classroom')->withPivot('opens_at', 'due_at')->withTimestamps();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_student', 'classroom_id', 'student_id')->withTimestamps();
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class, 'classroom_id');
    }

    public static function generateJoinCode()
    {
        do {
            $code = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
        } while (self::where('join_code', $code)->exists());
        
        return $code;
    }
}
