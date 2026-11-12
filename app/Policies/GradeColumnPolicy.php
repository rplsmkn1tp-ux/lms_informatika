<?php

namespace App\Policies;

use App\Models\User;

class GradeColumnPolicy
{
    public function viewAny(User $user)
    {
        return $user->role === 'admin' || $user->role === 'guru';
    }

    public function manageClassroom(User $user, \App\Models\Classroom $classroom)
    {
        if ($user->role === 'admin') return true;
        
        if ($user->role === 'guru') {
            return \App\Models\TeachingAssignment::where('teacher_id', $user->id)
                ->where('classroom_id', $classroom->id)
                ->exists();
        }

        return false;
    }

    public function viewOwnGrades(User $user, \App\Models\Classroom $classroom)
    {
        if ($user->role === 'admin') return true;

        if ($user->role === 'siswa') {
            return \App\Models\ClassroomStudent::where('student_id', $user->id)
                ->where('classroom_id', $classroom->id)
                ->exists();
        }

        return $this->manageClassroom($user, $classroom);
    }
}
