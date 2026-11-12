<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class TugasExportController extends Controller
{
    public function downloadZip(Assessment $assessment)
    {
        $user = auth()->user();

        // Keamanan: Cek hak akses guru atau admin
        if ($user->role !== 'admin' && ($user->role !== 'guru' || $assessment->created_by !== $user->id)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh jawaban tugas ini.');
        }

        $submissions = $assessment->submissions()
            ->whereNotNull('file_path')
            ->whereIn('status', [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION])
            ->with(['student.classrooms'])
            ->get();

        if ($submissions->isEmpty()) {
            return back()->with('error', 'Belum ada file jawaban yang dikumpulkan oleh siswa.');
        }

        if (!class_exists('ZipArchive')) {
            return back()->with('error', 'Fitur ZIP tidak didukung di server.');
        }

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipFileName = 'Tugas_' . Str::slug($assessment->title, '_') . '_' . date('Ymd_His') . '.zip';
        $zipFilePath = $tempDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file arsip ZIP.');
        }

        $addedCount = 0;
        foreach ($submissions as $sub) {
            $filePath = null;
            if (Storage::disk('local')->exists($sub->file_path)) {
                $filePath = Storage::disk('local')->path($sub->file_path);
            } elseif (Storage::disk('public')->exists($sub->file_path)) {
                $filePath = Storage::disk('public')->path($sub->file_path);
            }

            if ($filePath && file_exists($filePath)) {
                $studentName = Str::slug($sub->student->name, '_');
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $entryName = $studentName . '_att' . $sub->attempt . '.' . $ext;
                $zip->addFile($filePath, $entryName);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0 || !file_exists($zipFilePath)) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }
            return back()->with('error', 'Tidak ada file fisik yang ditemukan untuk diarsipkan.');
        }

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function downloadSubmissionFile(Submission $submission)
    {
        return $this->serveSubmissionFile($submission, false);
    }

    public function previewSubmissionFile(Submission $submission)
    {
        return $this->serveSubmissionFile($submission, true);
    }

    public function serveSubmissionFile(Submission $submission, bool $preview = false)
    {
        $user = auth()->user();

        // Keamanan: Cek apakah user adalah admin, guru pembuat tugas, atau siswa pemilik tugas
        $isOwnerTeacher = ($user->role === 'guru' && $submission->assessment->created_by === $user->id);
        $isStudentOwner = ($user->role === 'siswa' && $submission->student_id === $user->id);
        $isAdmin = ($user->role === 'admin');

        if (!$isOwnerTeacher && !$isStudentOwner && !$isAdmin) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengakses berkas ini.');
        }

        if (!$submission->file_path) {
            abort(404, 'Berkas jawaban tidak ditemukan.');
        }

        $disk = null;
        if (Storage::disk('local')->exists($submission->file_path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($submission->file_path)) {
            $disk = 'public';
        }

        if (!$disk) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        $fullPath = Storage::disk($disk)->path($submission->file_path);
        $filename = basename($submission->file_path);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($preview) {
            $allowedPreview = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            if (in_array($ext, $allowedPreview)) {
                $mimeType = match($ext) {
                    'pdf' => 'application/pdf',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    default => mime_content_type($fullPath) ?: 'application/octet-stream',
                };

                return response()->file($fullPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }
        }

        $origBasename = basename($submission->file_path);
        $displayName = preg_replace('/^\d+_/', '', $origBasename);
        $cleanFilename = Str::slug($submission->student->name ?? 'siswa', '_') . '_' . ($displayName ?: 'jawaban.' . $ext);
        return Storage::disk($disk)->download($submission->file_path, $cleanFilename);
    }

    public function downloadTeacherAttachment(Assessment $assessment, $index)
    {
        $user = auth()->user();

        // Siswa boleh download jika kelasnya terdaftar di tugas ini dan status published
        if ($user->role === 'siswa') {
            $classroomId = $user->activeClassroom()?->id;
            $hasAccess = $assessment->status === 'published' && $assessment->classrooms()->where('classrooms.id', $classroomId)->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke lampiran tugas ini.');
            }
        } elseif ($user->role === 'guru') {
            if ($assessment->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke lampiran tugas ini.');
            }
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $attachments = $assessment->teacher_attachments ?? [];
        if (!isset($attachments[$index])) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        $att = $attachments[$index];
        if (($att['type'] ?? '') !== 'file' || empty($att['path'])) {
            abort(404, 'Lampiran bukan berupa file.');
        }

        if (Storage::disk('local')->exists($att['path'])) {
            return Storage::disk('local')->download($att['path'], $att['name'] ?? 'lampiran');
        }

        if (Storage::disk('public')->exists($att['path'])) {
            return Storage::disk('public')->download($att['path'], $att['name'] ?? 'lampiran');
        }

        abort(404, 'File lampiran tidak ditemukan.');
    }
}
