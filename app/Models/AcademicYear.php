<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'semester', 'is_active'];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function getActive()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('active_academic_year', function () {
            return self::where('is_active', true)->first();
        });
    }

    protected static function booted()
    {
        static::saved(function ($academicYear) {
            \Illuminate\Support\Facades\Cache::forget('active_academic_year');
        });
        
        static::deleted(function ($academicYear) {
            \Illuminate\Support\Facades\Cache::forget('active_academic_year');
        });
    }
}
