<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'siswa') return $attendanceRecord->student_id === $user->id;
        if ($user->role === 'guru') {
            return $user->teachingAssignments()->where('classroom_id', $attendanceRecord->session->classroom_id)->exists();
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'guru';
    }

    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->role === 'guru' && $user->teachingAssignments()->where('classroom_id', $attendanceRecord->session->classroom_id)->exists();
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->role === 'admin' || ($user->role === 'guru' && $user->teachingAssignments()->where('classroom_id', $attendanceRecord->session->classroom_id)->exists());
    }

    public function restore(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return false;
    }

    public function forceDelete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return false;
    }
}
