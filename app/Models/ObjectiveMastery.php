<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectiveMastery extends Model
{
    protected $table = 'objective_mastery';

    protected $fillable = ['student_id', 'learning_objective_id', 'classroom_id', 'best_score', 'status'];

    protected function casts(): array
    {
        return ['best_score' => 'decimal:2'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
