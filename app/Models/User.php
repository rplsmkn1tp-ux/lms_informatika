<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nisn',
        'nip_nuptk',
        'subject',
        'role',
        'status',
        'needs_password_reset',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'needs_password_reset' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function initials(): string
    {
        return str($this->name)->explode(' ')->map(fn ($segment) => str($segment)->substr(0, 1))->take(2)->join('');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id')->withTimestamps();
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_id');
    }

    public function taughtClassrooms()
    {
        return $this->belongsToMany(Classroom::class, 'teaching_assignments', 'teacher_id', 'classroom_id');
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class, 'student_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'student_id');
    }

    public function activeClassroom(): ?Classroom
    {
        return $this->classrooms()
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->first();
    }

    public function attendanceSessionsAsTeacher()
    {
        return $this->hasMany(AttendanceSession::class, 'teacher_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }
}
