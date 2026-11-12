<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentGroup extends Model
{
    protected $fillable = [
        'assessment_id',
        'classroom_id',
        'name',
        'leader_id',
        'contribution_notes',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assessment_group_members', 'assessment_group_id', 'student_id')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'assessment_group_id');
    }

    public function hasMember(int $userId): bool
    {
        return $this->members()->where('users.id', $userId)->exists();
    }

    public function isLeader(int $userId): bool
    {
        return (int) $this->leader_id === (int) $userId;
    }

    public function isFull(): bool
    {
        $max = $this->assessment?->max_group_members ?? 4;
        return $this->members()->count() >= $max;
    }
}
