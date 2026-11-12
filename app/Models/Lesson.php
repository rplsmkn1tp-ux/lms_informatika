<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'learning_objective_id', 'code', 'title', 'summary', 'content', 'interactive_component',
        'video_url', 'estimated_minutes', 'sort_order', 'status', 'created_by',
    ];

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'lesson_classroom')
            ->withPivot('available_from')
            ->withTimestamps();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    /** Subbab terbit yang sudah dibuka untuk kelas tertentu. */
    public function scopeAvailableFor(Builder $query, int $classroomId): void
    {
        $query->where('status', 'published')
            ->whereHas('classrooms', function (Builder $q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId)
                    ->where(fn (Builder $w) => $w
                        ->whereNull('lesson_classroom.available_from')
                        ->orWhere('lesson_classroom.available_from', '<=', now()));
            });
    }
}
