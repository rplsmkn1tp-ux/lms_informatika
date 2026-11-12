<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    public const STATUS_IN_PROGRESS = 'dikerjakan';
    public const STATUS_SUBMITTED = 'dikumpulkan';
    public const STATUS_GRADED = 'dinilai';
    public const STATUS_REVISION = 'perlu_revisi';

    protected $fillable = [
        'assessment_id', 'assessment_group_id', 'student_id', 'attempt', 'started_at', 'submitted_at',
        'file_path', 'text_content', 'link_url', 'is_late', 'score', 'status', 'feedback', 'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'attempt' => 'integer',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'is_late' => 'boolean',
        ];
    }

    public function getFileDisk(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($this->file_path)) {
            return 'local';
        }
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->file_path)) {
            return 'public';
        }
        return null;
    }

    public function getFileNameAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        $basename = basename($this->file_path);
        // Hapus prefix angka/timestamp jika ada (misal: 17283921_tugas.pdf -> tugas.pdf)
        $clean = preg_replace('/^\d+_/', '', $basename);
        return $clean ?: $basename;
    }

    public function getFileSizeFormattedAttribute(): ?string
    {
        $disk = $this->getFileDisk();
        if (!$disk) {
            return null;
        }

        try {
            $bytes = \Illuminate\Support\Facades\Storage::disk($disk)->size($this->file_path);
            if ($bytes >= 1048576) {
                return round($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return round($bytes / 1024, 1) . ' KB';
            }
            return $bytes . ' B';
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isPreviewable(): bool
    {
        if (!$this->file_path) {
            return false;
        }
        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AssessmentGroup::class, 'assessment_group_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }
}
