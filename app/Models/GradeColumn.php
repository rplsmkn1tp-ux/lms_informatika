<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeColumn extends Model
{
    protected $fillable = [
        'classroom_id',
        'academic_year_id',
        'group',
        'seq',
        'label',
        'max_score',
        'learning_objective_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function entries()
    {
        return $this->hasMany(GradeEntry::class, 'column_id');
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class, 'learning_objective_id');
    }

    public function learningObjectives()
    {
        return $this->belongsToMany(LearningObjective::class, 'grade_column_learning_objective');
    }

    /**
     * Dapatkan semua ID Tujuan Pembelajaran yang terhubung ke kolom ini
     * (menggabungkan single learning_objective_id dan many-to-many learningObjectives).
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

    /**
     * Sinkronisasikan ID Tujuan Pembelajaran ke kolom nilai
     */
    public function syncLearningObjectives(array $tpIds): void
    {
        $tpIds = array_values(array_unique(array_filter(array_map('intval', $tpIds))));
        $this->learning_objective_id = !empty($tpIds) ? $tpIds[0] : null;
        $this->save();

        $this->learningObjectives()->sync($tpIds);
    }
}
