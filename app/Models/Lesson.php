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
        'video_url', 'estimated_minutes', 'sort_order', 'status', 'scheduled_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'estimated_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return $this->where($field, $value)->first();
        }

        return $this->where('code', $value)
            ->orWhere('id', $value)
            ->first();
    }

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
        return $this->hasMany(LessonAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(LessonAttachment::class)
            ->where('type', 'pdf')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(LessonAttachment::class)
            ->where('type', 'youtube')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'lesson_classroom')
            ->withPivot('available_from')
            ->withTimestamps();
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->where('type', 'tugas');
    }

    public function allAssessments(): HasMany
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

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isPublished(): bool
    {
        if ($this->status === 'published') {
            return true;
        }

        if ($this->status === 'scheduled') {
            return $this->scheduled_at && $this->scheduled_at->isPast();
        }

        return false;
    }

    /** Subbab terbit/terjadwal yang sudah dibuka untuk kelas tertentu. */
    public function scopeAvailableFor(Builder $query, int $classroomId): void
    {
        $query->where(function (Builder $sq) {
                $sq->where('status', 'published')
                   ->orWhere(function (Builder $sched) {
                       $sched->where('status', 'scheduled')
                             ->whereNotNull('scheduled_at')
                             ->where('scheduled_at', '<=', now());
                   });
            })
            ->whereHas('classrooms', function (Builder $q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId)
                    ->where(fn (Builder $w) => $w
                        ->whereNull('lesson_classroom.available_from')
                        ->orWhere('lesson_classroom.available_from', '<=', now()));
            });
    }
}
