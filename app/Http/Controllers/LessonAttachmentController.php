<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonAttachmentController extends Controller
{
    public function streamPdf(Lesson $lesson, LessonAttachment $attachment)
    {
        $this->authorizeAccess($lesson, $attachment);

        $disk = null;
        if (Storage::disk('local')->exists($attachment->file_path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($attachment->file_path)) {
            $disk = 'public';
        }

        if (!$disk) {
            abort(404, 'File materi tidak ditemukan di penyimpanan server.');
        }

        $fullPath = Storage::disk($disk)->path($attachment->file_path);
        $filename = $attachment->label ? Str::slug($attachment->label, '_') . '.pdf' : basename($attachment->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function downloadPdf(Lesson $lesson, LessonAttachment $attachment)
    {
        $this->authorizeAccess($lesson, $attachment);

        $disk = null;
        if (Storage::disk('local')->exists($attachment->file_path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($attachment->file_path)) {
            $disk = 'public';
        }

        if (!$disk) {
            abort(404, 'File materi tidak ditemukan di penyimpanan server.');
        }

        $filename = $attachment->label ? Str::slug($attachment->label, '_') . '.pdf' : basename($attachment->file_path);
        return Storage::disk($disk)->download($attachment->file_path, $filename);
    }

    protected function authorizeAccess(Lesson $lesson, LessonAttachment $attachment)
    {
        if ($attachment->lesson_id !== $lesson->id) {
            abort(404, 'Lampiran tidak sesuai dengan materi.');
        }

        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        // Admin memiliki akses penuh
        if ($user->role === 'admin') {
            return true;
        }

        // Guru hanya materi yang dibuatnya atau diajar di kelasnya
        if ($user->role === 'guru') {
            if ($lesson->created_by === $user->id) {
                return true;
            }

            $taughtClassIds = $user->taughtClassrooms()->pluck('classrooms.id')->toArray();
            if ($lesson->classrooms()->whereIn('classrooms.id', $taughtClassIds)->exists()) {
                return true;
            }

            abort(403, 'Anda tidak memiliki hak akses ke materi ini.');
        }

        // Siswa: hanya jika materi status terbit dan kelasnya memiliki akses
        if ($user->role === 'siswa') {
            if (!$lesson->isPublished()) {
                abort(403, 'Materi ini belum diterbitkan.');
            }

            $classroom = $user->activeClassroom();
            $classroomId = $classroom ? $classroom->id : 0;

            if (!$classroomId) {
                abort(403, 'Anda belum memiliki kelas aktif.');
            }

            $hasAccess = $lesson->classrooms()
                ->where('classrooms.id', $classroomId)
                ->where(function ($w) {
                    $w->whereNull('lesson_classroom.available_from')
                      ->orWhere('lesson_classroom.available_from', '<=', now());
                })
                ->exists();

            if (!$hasAccess) {
                abort(403, 'Materi ini belum tersedia untuk kelas Anda.');
            }

            return true;
        }

        abort(403, 'Peran pengguna tidak diizinkan.');
    }
}
