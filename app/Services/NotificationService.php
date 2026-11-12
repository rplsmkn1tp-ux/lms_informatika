<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Announcement;
use App\Models\Lesson;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Get all aggregated notifications for a user (Guru or Siswa)
     *
     * @param User $user
     * @return Collection
     */
    public function getNotifications(User $user): Collection
    {
        $items = collect();

        if ($user->role === 'siswa') {
            $items = $this->getSiswaNotifications($user);
        } elseif ($user->role === 'guru') {
            $items = $this->getGuruNotifications($user);
        }

        // Fetch read keys for this user
        $readKeys = DB::table('notification_reads')
            ->where('user_id', $user->id)
            ->pluck('notification_key')
            ->toArray();

        return $items->map(function ($item) use ($readKeys) {
            $item['is_read'] = in_array($item['key'], $readKeys);
            return $item;
        })->sortByDesc('created_at')->values();
    }

    /**
     * Get total unread notifications count
     *
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $this->getNotifications($user)->where('is_read', false)->count();
    }

    /**
     * Mark a specific notification key as read
     *
     * @param User $user
     * @param string $key
     * @return void
     */
    public function markAsRead(User $user, string $key): void
    {
        DB::table('notification_reads')->updateOrInsert(
            ['user_id' => $user->id, 'notification_key' => $key],
            ['read_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );
    }

    /**
     * Mark all notifications as read for this user
     *
     * @param User $user
     * @return void
     */
    public function markAllAsRead(User $user): void
    {
        $notifications = $this->getNotifications($user);
        
        $records = [];
        $now = now();
        foreach ($notifications as $n) {
            if (!$n['is_read']) {
                $records[] = [
                    'user_id' => $user->id,
                    'notification_key' => $n['key'],
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($records)) {
            DB::table('notification_reads')->insertOrIgnore($records);
        }
    }

    /**
     * Notifications specifically tailored for Students
     */
    protected function getSiswaNotifications(User $user): Collection
    {
        $notifications = collect();
        $classrooms = $user->classrooms()->pluck('classrooms.id')->toArray();

        // 1. Graded / Revision requested submissions
        $submissions = Submission::where('student_id', $user->id)
            ->whereIn('status', ['dinilai', 'perlu_revisi'])
            ->with('assessment')
            ->latest('updated_at')
            ->take(8)
            ->get();

        foreach ($submissions as $sub) {
            $isRevision = $sub->status === 'perlu_revisi';
            $title = $isRevision 
                ? 'Perlu Revisi: ' . ($sub->assessment?->title ?? 'Tugas')
                : 'Tugas Dinilai: ' . ($sub->assessment?->title ?? 'Tugas');

            $msg = $isRevision
                ? 'Guru meminta perbaikan tugas. ' . ($sub->feedback ? '"' . Str::limit($sub->feedback, 60) . '"' : 'Silakan periksa catatan revisi.')
                : 'Nilai Anda: ' . round($sub->score, 1) . '/100. ' . ($sub->feedback ? '"' . Str::limit($sub->feedback, 60) . '"' : '');

            $notifications->push([
                'key' => "sub-graded-{$sub->id}-" . ($sub->updated_at?->timestamp ?? 0),
                'title' => $title,
                'message' => $msg,
                'url' => route('siswa.tugas.kerjakan', $sub->assessment_id),
                'type' => $isRevision ? 'revision' : 'grade',
                'color' => $isRevision ? '#D97706' : '#059669', // amber or emerald
                'bg' => $isRevision ? '#FEF3C7' : '#D1FAE5',
                'created_at' => $sub->updated_at ?? now(),
            ]);
        }

        // 2. Active tasks needing submission
        $urgentAssessments = Assessment::where('status', 'published')
            ->whereHas('classrooms', fn($q) => $q->whereIn('classrooms.id', $classrooms))
            ->where(function($q) {
                $q->whereNull('due_at')
                  ->orWhere('due_at', '>=', now()->subDay());
            })
            ->with(['submissions' => fn($q) => $q->where('student_id', $user->id)])
            ->latest('id')
            ->take(8)
            ->get();

        foreach ($urgentAssessments as $task) {
            $sub = $task->submissions->first();
            // Only notify if student hasn't submitted yet
            if (!$sub || in_array($sub->status, ['draft', 'belum_mengumpulkan'])) {
                $dueCarbon = $task->due_at ? Carbon::parse($task->due_at)->setTimezone('Asia/Jakarta') : null;
                $dueText = $dueCarbon ? 'Tenggat: ' . $dueCarbon->translatedFormat('d M, H:i') . ' (' . $dueCarbon->diffForHumans() . ')' : 'Belum dikerjakan';

                $notifications->push([
                    'key' => "task-todo-{$task->id}",
                    'title' => 'Tugas: ' . $task->title,
                    'message' => $dueText,
                    'url' => route('siswa.tugas.kerjakan', $task->id),
                    'type' => 'task',
                    'color' => '#2563EB', // blue
                    'bg' => '#DBEAFE',
                    'created_at' => $task->created_at ?? now(),
                ]);
            }
        }

        // 3. New Lessons in student's class
        $lessons = Lesson::where('status', 'published')
            ->whereHas('classrooms', fn($q) => $q->whereIn('classrooms.id', $classrooms))
            ->where('created_at', '>=', now()->subDays(14))
            ->with(['learningObjective.element'])
            ->latest('created_at')
            ->take(5)
            ->get();

        foreach ($lessons as $lesson) {
            $el = $lesson->learningObjective?->element;
            $tp = $lesson->learningObjective;
            $url = ($el && $tp) 
                ? route('siswa.belajar.materi', ['element' => $el->code, 'tp' => $tp->code, 'lesson' => $lesson->id])
                : route('siswa.belajar');

            $notifications->push([
                'key' => "lesson-new-{$lesson->id}",
                'title' => 'Materi Baru: ' . $lesson->title,
                'message' => 'Modul ' . ($tp?->code ?? '') . ' siap untuk dipelajari.',
                'url' => $url,
                'type' => 'lesson',
                'color' => '#7C3AED', // purple
                'bg' => '#EDE9FE',
                'created_at' => $lesson->created_at ?? now(),
            ]);
        }

        // 4. Announcements
        $announcements = Announcement::where(function($q) use ($classrooms) {
                $q->whereNull('classroom_id')
                  ->orWhereIn('classroom_id', $classrooms);
            })
            ->where('published_at', '<=', now())
            ->where('published_at', '>=', now()->subDays(14))
            ->latest('published_at')
            ->take(4)
            ->get();

        foreach ($announcements as $ann) {
            $notifications->push([
                'key' => "ann-{$ann->id}",
                'title' => 'Pengumuman: ' . $ann->title,
                'message' => Str::limit(strip_tags($ann->body), 75),
                'url' => route('siswa.beranda'),
                'type' => 'announcement',
                'color' => '#D97706', // amber
                'bg' => '#FEF3C7',
                'created_at' => $ann->published_at ?? $ann->created_at ?? now(),
            ]);
        }

        return $notifications;
    }

    /**
     * Notifications specifically tailored for Teachers
     */
    protected function getGuruNotifications(User $user): Collection
    {
        $notifications = collect();

        // 1. Submissions awaiting grading
        $submissions = Submission::whereIn('status', ['submitted', 'dikumpulkan'])
            ->whereHas('assessment', fn($q) => $q->where('created_by', $user->id))
            ->with(['student', 'assessment'])
            ->latest('submitted_at')
            ->take(12)
            ->get();

        foreach ($submissions as $sub) {
            $studentName = $sub->student?->name ?? 'Siswa';
            $taskTitle = $sub->assessment?->title ?? 'Tugas';

            $notifications->push([
                'key' => "guru-sub-{$sub->id}",
                'title' => 'Jawaban Masuk: ' . $studentName,
                'message' => 'Mengumpulkan tugas "' . $taskTitle . '". Klik untuk memeriksa dan menilai.',
                'url' => route('guru.tugas.detail', $sub->assessment_id),
                'type' => 'submission',
                'color' => '#059669', // emerald
                'bg' => '#D1FAE5',
                'created_at' => $sub->submitted_at ?? $sub->updated_at ?? now(),
            ]);
        }

        // 2. Tasks with passed deadline
        $dueAssessments = Assessment::where('created_by', $user->id)
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now())
            ->where('due_at', '>=', now()->subDays(7))
            ->withCount('submissions')
            ->latest('due_at')
            ->take(5)
            ->get();

        foreach ($dueAssessments as $task) {
            $notifications->push([
                'key' => "guru-due-{$task->id}",
                'title' => 'Tenggat Berakhir: ' . $task->title,
                'message' => "Waktu pengumpulan tugas telah ditutup ({$task->submissions_count} submisi terkumpul).",
                'url' => route('guru.tugas.detail', $task->id),
                'type' => 'due',
                'color' => '#DC2626', // red
                'bg' => '#FEE2E2',
                'created_at' => $task->due_at,
            ]);
        }

        // 3. Announcements
        $announcements = Announcement::whereNull('classroom_id')
            ->where('published_at', '<=', now())
            ->where('published_at', '>=', now()->subDays(14))
            ->latest('published_at')
            ->take(4)
            ->get();

        foreach ($announcements as $ann) {
            $notifications->push([
                'key' => "guru-ann-{$ann->id}",
                'title' => 'Pengumuman: ' . $ann->title,
                'message' => Str::limit(strip_tags($ann->body), 75),
                'url' => route('guru.dashboard'),
                'type' => 'announcement',
                'color' => '#2563EB',
                'bg' => '#DBEAFE',
                'created_at' => $ann->published_at ?? $ann->created_at ?? now(),
            ]);
        }

        return $notifications;
    }
}

