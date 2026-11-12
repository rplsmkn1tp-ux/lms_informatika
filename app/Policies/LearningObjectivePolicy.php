<?php

namespace App\Policies;

use App\Models\LearningObjective;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LearningObjectivePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'guru']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LearningObjective $learningObjective): bool
    {
        if ($user->role === 'admin') return true;
        return $user->id === $learningObjective->created_by;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'guru']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LearningObjective $learningObjective): bool
    {
        if ($user->role === 'admin') return true;
        return $user->id === $learningObjective->created_by;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LearningObjective $learningObjective): bool
    {
        if ($user->role === 'admin') return true;
        return $user->id === $learningObjective->created_by;
    }
}
