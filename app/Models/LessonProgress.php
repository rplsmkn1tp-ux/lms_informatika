<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = ['student_id', 'lesson_id', 'completed_at', 'completed_items'];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'completed_items' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function isItemCompleted(string $itemKey): bool
    {
        return in_array($itemKey, $this->completed_items ?? []);
    }

    public function toggleItem(string $itemKey, array $allRequiredKeys = []): bool
    {
        $items = $this->completed_items ?? [];
        if (in_array($itemKey, $items)) {
            $items = array_values(array_diff($items, [$itemKey]));
        } else {
            $items[] = $itemKey;
            $items = array_values(array_unique($items));
        }
        $this->completed_items = $items;

        if (!empty($allRequiredKeys)) {
            $allDone = empty(array_diff($allRequiredKeys, $items));
            $this->completed_at = $allDone ? now() : null;
        }

        $this->save();
        return in_array($itemKey, $items);
    }
}
