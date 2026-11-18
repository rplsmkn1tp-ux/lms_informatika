<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonCheckpoint extends Model
{
    protected $fillable = [
        'lesson_id',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Helper to verify if the given answer matches the correct answer
     */
    public function isCorrect(?string $answer): bool
    {
        if ($answer === null) {
            return false;
        }

        return strtoupper(trim($answer)) === strtoupper(trim($this->correct_answer));
    }
}
