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
        'is_gated', 'completion_rule', 'prerequisite_lesson_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'estimated_minutes' => 'integer',
            'sort_order' => 'integer',
            'is_gated' => 'boolean',
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

    public function getSiswaUrlAttribute(): ?string
    {
        $tp = $this->learningObjective;
        $element = $tp?->element;
        if (!$element || !$tp) {
            return null;
        }

        return route('siswa.belajar.materi', [
            'element' => $element->code,
            'tp' => $tp->code,
            'lesson' => $this->code ?: $this->id,
        ]);
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

    public function checkpoints(): HasMany
    {
        return $this->hasMany(LessonCheckpoint::class)->orderBy('sort_order')->orderBy('id');
    }

    public function prerequisiteLesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'prerequisite_lesson_id');
    }

    /**
     * Cari materi prasyarat efektif:
     * 1. Jika guru menentukan langsung `prerequisite_lesson_id`.
     * 2. Atau jika tidak ditentukan, cari materi sebelumnya dalam TP yang sama yang memiliki `is_gated = true`.
     */
    public function getEffectivePrerequisite(): ?Lesson
    {
        if ($this->prerequisite_lesson_id) {
            return $this->prerequisiteLesson;
        }

        return static::where('learning_objective_id', $this->learning_objective_id)
            ->where('sort_order', '<', $this->sort_order)
            ->where('is_gated', true)
            ->orderByDesc('sort_order')
            ->first();
    }

    /**
     * Memeriksa apakah materi ini sudah terbuka (unlocked) bagi siswa tertentu.
     */
    public function isUnlockedFor(User $student): bool
    {
        if ($student->role !== 'siswa') {
            return true;
        }

        $prereq = $this->getEffectivePrerequisite();
        if (!$prereq) {
            return true;
        }

        return $prereq->isCompletedBy($student);
    }

    /**
     * Memeriksa apakah siswa sudah menyelesaikan tahap membaca / literasi materi sebelum diperbolehkan mengerjakan tugas.
     */
    public function isReadingCompletedBy(User $student): bool
    {
        if ($student->role !== 'siswa') {
            return true;
        }

        $progress = $this->progress()->where('student_id', $student->id)->first();

        // 1. Jika materi memiliki kuis checkpoint literasi, siswa WAJIB lulus kuis (100% benar)
        if ($this->checkpoints()->count() > 0) {
            return (bool) ($progress && $progress->hasPassedCheckpoint());
        }

        // 2. Jika ditandai tuntas atau menyelesaikan semua item bacaan
        if ($progress && ($progress->completed_at !== null || count($progress->completed_items ?? []) > 0)) {
            return true;
        }

        // 3. Jika materi memiliki lampiran PDF/bacaan atau isi materi, siswa minimal harus sudah membuka materi
        if ($this->attachments()->count() > 0 || !empty($this->body)) {
            return $progress !== null;
        }

        return true;
    }

    /**
     * Memeriksa apakah siswa sudah menyelesaikan seluruh kewajiban belajar pada materi ini.
     */
    public function isCompletedBy(User $student): bool
    {
        $progress = $this->progress()->where('student_id', $student->id)->first();
        $rule = $this->completion_rule ?? 'standard';

        // 1. Syarat Kuis Pemantik Literasi
        $passedCheckpoint = false;
        if ($this->checkpoints()->count() === 0) {
            $passedCheckpoint = true; // Jika tidak ada checkpoint kuis, otomatis dianggap lolos
        } elseif ($progress && $progress->hasPassedCheckpoint()) {
            $passedCheckpoint = true;
        }

        // 2. Syarat Tugas / Asesmen Akhir
        $submittedTask = false;
        $assessmentIds = $this->allAssessments()->pluck('id');
        if ($assessmentIds->isEmpty()) {
            $submittedTask = true; // Jika tidak ada asesmen tertaut, otomatis dianggap lolos
        } else {
            $submittedTask = Submission::whereIn('assessment_id', $assessmentIds)
                ->where('student_id', $student->id)
                ->whereIn('status', [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION])
                ->exists();
        }

        // Evaluasi berdasarkan aturan yang ditetapkan guru:
        return match ($rule) {
            'checkpoint_and_task' => $passedCheckpoint && $submittedTask,
            'checkpoint_only'     => $passedCheckpoint,
            'task_only'           => $submittedTask,
            default               => $progress && $progress->completed_at !== null,
        };
    }
}
