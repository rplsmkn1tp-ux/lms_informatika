<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'guru';
    }

    public function view(User $user, AttendanceSession $attendanceSession): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'guru') {
            return $user->teachingAssignments()->where('classroom_id', $attendanceSession->classroom_id)->exists();
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'guru';
    }

    public function update(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->role === 'guru' && $user->teachingAssignments()->where('classroom_id', $attendanceSession->classroom_id)->exists();
    }

    public function delete(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->role === 'admin' || ($user->role === 'guru' && $user->teachingAssignments()->where('classroom_id', $attendanceSession->classroom_id)->exists());
    }

    public function closeSession(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->role === 'guru' && $user->teachingAssignments()->where('classroom_id', $attendanceSession->classroom_id)->exists();
    }

    public function restore(User $user, AttendanceSession $attendanceSession): bool
    {
        return false;
    }

    public function forceDelete(User $user, AttendanceSession $attendanceSession): bool
    {
        return false;
    }
}
