<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'learning_objective_id', 'element_id', 'lesson_id', 'attendance_session_id', 'meeting_no', 'type', 'title', 'instructions',
        'teacher_attachments', 'submission_types', 'allow_late', 'allow_resubmit', 'opens_at', 'due_at',
        'kktp', 'max_score', 'max_attempts', 'time_limit_minutes', 'status', 'created_by',
        'grade_group', 'grade_seq',
    ];

    protected function casts(): array
    {
        return [
            'teacher_attachments' => 'array',
            'submission_types' => 'array',
            'allow_late' => 'boolean',
            'allow_resubmit' => 'boolean',
            'opens_at' => 'datetime',
            'due_at' => 'datetime',
            'grade_seq' => 'integer',
            'meeting_no' => 'integer',
        ];
    }

    public function effectiveMeetingNo(): ?int
    {
        return $this->meeting_no ?: $this->attendanceSession?->meeting_no;
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function learningObjectives(): BelongsToMany
    {
        return $this->belongsToMany(LearningObjective::class, 'assessment_learning_objective');
    }

    /**
     * Dapatkan semua ID Tujuan Pembelajaran yang terhubung ke asesmen ini.
     *
     * @return array<int>
     */
    public function getAllLearningObjectiveIds(): array
    {
        $ids = [];
        if ($this->learning_objective_id) {
            $ids[] = (int) $this->learning_objective_id;
        }

        if ($this->relationLoaded('learningObjectives')) {
            $ids = array_merge($ids, $this->learningObjectives->pluck('id')->map(fn($id) => (int) $id)->toArray());
        } else {
            $ids = array_merge($ids, $this->learningObjectives()->pluck('learning_objectives.id')->map(fn($id) => (int) $id)->toArray());
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public function syncLearningObjectives(array $tpIds): void
    {
        $tpIds = array_values(array_unique(array_filter(array_map('intval', $tpIds))));
        $this->learning_objective_id = !empty($tpIds) ? $tpIds[0] : null;
        if ($this->learning_objective_id && !$this->element_id) {
            $tp = LearningObjective::find($this->learning_objective_id);
            if ($tp) {
                $this->element_id = $tp->element_id;
            }
        }
        $this->save();

        $this->learningObjectives()->sync($tpIds);
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'assessment_classroom')
            ->withPivot('opens_at', 'due_at')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function isUpload(): bool
    {
        return $this->type === 'tugas';
    }

    /** KKTP asesmen, jatuh ke default_kktp sekolah bila kosong. */
    public function effectiveKktp(): int
    {
        return (int) ($this->kktp ?? Setting::getValue('default_kktp', 75));
    }

    public function dueAtForClassroom($classroomId = null)
    {
        if ($classroomId) {
            $pivot = $this->classrooms->firstWhere('id', $classroomId)?->pivot;
            if ($pivot && $pivot->due_at) {
                return $pivot->due_at;
            }
        }
        return $this->due_at;
    }

    public function opensAtForClassroom($classroomId = null)
    {
        if ($classroomId) {
            $pivot = $this->classrooms->firstWhere('id', $classroomId)?->pivot;
            if ($pivot && $pivot->opens_at) {
                return $pivot->opens_at;
            }
        }
        return $this->opens_at;
    }
}
