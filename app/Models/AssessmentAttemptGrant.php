<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAttemptGrant extends Model
{
    protected $fillable = [
        'student_id',
        'assessment_id',
        'reason',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
