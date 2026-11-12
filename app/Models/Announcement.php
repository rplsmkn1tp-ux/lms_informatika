<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = ['classroom_id', 'user_id', 'title', 'body', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Pengumuman kelas tertentu + pengumuman seluruh sekolah (classroom_id null). */
    public function scopeForClassroom(Builder $query, ?int $classroomId): void
    {
        $query->where(function (Builder $q) use ($classroomId) {
            $q->whereNull('classroom_id');
            if ($classroomId) {
                $q->orWhere('classroom_id', $classroomId);
            }
        });
    }
}
