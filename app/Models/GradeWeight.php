<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeWeight extends Model
{
    protected $fillable = [
        'classroom_id',
        'academic_year_id',
        'weight_uh',
        'weight_tugas',
        'weight_uas_mid',
        'weight_absensi',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
