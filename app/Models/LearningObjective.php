<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningObjective extends Model
{
    protected $fillable = ['element_id', 'code', 'title', 'description', 'sort_order', 'kktp', 'is_template', 'created_by'];

    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
        ];
    }

    public function element()
    {
        return $this->belongsTo(Element::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sharedClassroom()
    {
        return $this->belongsTo(Classroom::class, 'shared_to_classroom_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
