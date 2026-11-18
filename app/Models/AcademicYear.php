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
        $id = \Illuminate\Support\Facades\Cache::rememberForever('active_academic_year_id', function () {
            return self::where('is_active', true)->value('id');
        });

        return $id ? self::find($id) : self::where('is_active', true)->first();
    }

    protected static function booted()
    {
        static::saved(function ($academicYear) {
            \Illuminate\Support\Facades\Cache::forget('active_academic_year');
            \Illuminate\Support\Facades\Cache::forget('active_academic_year_id');
        });
        
        static::deleted(function ($academicYear) {
            \Illuminate\Support\Facades\Cache::forget('active_academic_year');
            \Illuminate\Support\Facades\Cache::forget('active_academic_year_id');
        });
    }
}
