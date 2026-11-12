<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeEntry extends Model
{
    protected $fillable = [
        'column_id',
        'student_id',
        'score',
    ];

    public function column()
    {
        return $this->belongsTo(GradeColumn::class, 'column_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
