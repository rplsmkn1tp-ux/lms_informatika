<?php

namespace App\Livewire\Siswa;

use App\Models\Announcement;
use App\Models\LessonProgress;
use App\Models\ScheduleEntry;
use App\Models\BellPeriod;
use App\Models\AttendanceRecord;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.siswa')]
class Beranda extends Component
{
    public function render()
    {
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;
        $academicYearId = $classroom ? $classroom->academic_year_id : 0;

        // 1. Target Mingguan (Lesson Progress between Monday and Sunday)
        $startOfWeek = Carbon::now('Asia/Jakarta')->startOfWeek();
        $endOfWeek = Carbon::now('Asia/Jakarta')->endOfWeek();
        $weeklyTarget = $classroom->weekly_lesson_target ?? 5;
        
        $completedThisWeek = LessonProgress::where('student_id', $user->id)
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->count();
            
        $weeklyPercentage = $weeklyTarget > 0 ? min(100, round(($completedThisWeek / $weeklyTarget) * 100)) : 100;

        // 2. Hari Beruntun (Streak)
        $streak = $this->calculateStreak($user->id);

        // 3. Pengumuman
        $announcements = Announcement::published()
            ->where(function($q) use ($classroomId) {
                $q->whereNull('classroom_id')
                  ->orWhere('classroom_id', $classroomId);
            })
            ->latest('published_at')
            ->take(5)
            ->get();

        // 4. Lanjutkan Belajar (Last accessed lesson)
        $lastProgress = LessonProgress::where('student_id', $user->id)
            ->with(['lesson.learningObjective.element'])
            ->orderBy('updated_at', 'desc')
            ->first();

        // 5. Elemen Belajar
        $elements = \App\Models\Element::orderBy('code')->get();

        // 6. Teks Sambutan & Subtitle dari Pengaturan
        $siswaTitleTemplate = \App\Models\Setting::getValue('dashboard_siswa_title', 'Halo, {nama}. Siap lanjut belajar?');
        $fullName = $user->name;
        $firstName = explode(' ', $user->name)[0];
        $greetingTitle = str_replace(
            ['{nama}', '{name}', '{nama_lengkap}', '{fullname}', '{nama_depan}', '{nama_panggilan}'],
            [$fullName, $fullName, $fullName, $fullName, $firstName, $firstName],
            $siswaTitleTemplate
        );

        $siswaSubtitle = \App\Models\Setting::getValue('dashboard_siswa_subtitle', '');

        // 7. Tugas Mendekati Tenggat & Sudah Dinilai
        $urgentTasks = collect();
        $gradedTasks = collect();

        if ($classroom) {
            $nowWib = Carbon::now('Asia/Jakarta');

            // Tugas yang perlu dikerjakan dan mendekati tenggat (dalam 3 hari ke depan atau sudah lewat jika allow_late)
            $urgentTasks = \App\Models\Assessment::where('status', 'published')
                ->whereHas('classrooms', function ($q) use ($classroom) {
                    $q->where('classrooms.id', $classroom->id);
                })
                ->where(function ($q) use ($nowWib) {
                    $q->whereNotNull('due_at')
                      ->where('due_at', '>=', $nowWib->copy()->subDay())
                      ->where('due_at', '<=', $nowWib->copy()->addDays(4));
                })
                ->with(['submissions' => function ($q) use ($user) {
                    $q->where('student_id', $user->id);
                }])
                ->orderBy('due_at', 'asc')
                ->get()
                ->filter(function ($task) {
                    $sub = $task->submissions->first();
                    return !$sub || in_array($sub->status, ['perlu_revisi', 'draft', 'belum_mengumpulkan']);
                })
                ->take(4);

            // Tugas yang sudah dinilai atau perlu revisi
            $gradedTasks = \App\Models\Submission::where('student_id', $user->id)
                ->whereIn('status', ['dinilai', 'perlu_revisi'])
                ->with('assessment')
                ->latest('updated_at')
                ->take(4)
                ->get();
        }

        return view('livewire.siswa.beranda', [
            'weeklyPercentage' => $weeklyPercentage,
            'completedThisWeek' => $completedThisWeek,
            'weeklyTarget' => $weeklyTarget,
            'streak' => $streak,
            'announcements' => $announcements,
            'classroom' => $classroom,
            'lastProgress' => $lastProgress,
            'elements' => $elements,
            'greetingTitle' => $greetingTitle,
            'siswaSubtitle' => $siswaSubtitle,
            'urgentTasks' => $urgentTasks,
            'gradedTasks' => $gradedTasks,
        ]);
    }

    private function calculateStreak($userId)
    {
        $dates = LessonProgress::where('student_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(fn($p) => Carbon::parse($p->completed_at)->setTimezone('Asia/Jakarta')->format('Y-m-d'))
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $yesterday = Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d');
        
        $checkDate = $today;
        
        // If they haven't done anything today, we check if they did something yesterday to keep streak
        if ($dates->first() !== $today) {
            if ($dates->first() === $yesterday) {
                $checkDate = $yesterday;
            } else {
                return 0; // Streak broken
            }
        }

        $currentDate = Carbon::parse($checkDate);
        foreach ($dates as $dateStr) {
            if ($dateStr === $currentDate->format('Y-m-d')) {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}
