<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'assessment_id', 'type', 'body', 'options', 'answer_key', 'explanation', 'points', 'sort_order',
    ];

    protected $hidden = ['answer_key'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'pilihan_ganda';
    }

    /** Pilihan ganda dinilai otomatis; jenis lain dinilai guru (null). */
    public function check(?string $answer): ?bool
    {
        if (! $this->isMultipleChoice()) {
            return null;
        }

        return $answer !== null && strcasecmp(trim($answer), trim((string) $this->answer_key)) === 0;
    }
}
