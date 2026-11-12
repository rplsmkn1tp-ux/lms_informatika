<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function learningObjectives()
    {
        return $this->hasMany(LearningObjective::class);
    }

    public function tps()
    {
        return $this->learningObjectives();
    }
}
