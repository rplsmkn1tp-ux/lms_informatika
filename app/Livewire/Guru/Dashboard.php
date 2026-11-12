<?php

namespace App\Livewire\Guru;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TeachingAssignment;
use App\Models\Classroom;
use App\Models\User;

#[Layout('components.layouts.guru')]
class Dashboard extends Component
{
    public $activeScheduleIndex = 0;

    public function render()
    {
        $teacher = auth()->user();
        
        $assignments = TeachingAssignment::with(['classroom' => function ($query) {
            $query->withCount('students');
        }])
            ->where('teacher_id', $teacher->id)
            ->get();
            
        $classrooms = $assignments->pluck('classroom');
        $classroomIds = $classrooms->pluck('id')->toArray();
        
        // Peta Akses Siswa
        // Get all students belonging to these classrooms
        $totalStudents = User::where('role', 'siswa')
            ->whereHas('classrooms', function ($q) use ($classroomIds) {
                $q->whereIn('classrooms.id', $classroomIds);
            })->count();
            
        $activeLast7Days = User::where('role', 'siswa')
            ->whereHas('classrooms', function ($q) use ($classroomIds) {
                $q->whereIn('classrooms.id', $classroomIds);
            })
            ->where('last_login_at', '>=', now()->subDays(7))
            ->count();
            
        $inactiveStudents = User::where('role', 'siswa')
            ->whereHas('classrooms', function ($q) use ($classroomIds) {
                $q->whereIn('classrooms.id', $classroomIds);
            })
            ->where(function($q) {
                $q->where('last_login_at', '<', now()->subDays(7))
                  ->orWhereNull('last_login_at');
            })
            ->with(['classrooms' => function($q) use ($classroomIds) {
                $q->whereIn('classrooms.id', $classroomIds);
            }])
            ->orderBy('last_login_at', 'asc')
            ->take(5)
            ->get();

        $stats = [
            'total_kelas' => $classrooms->count(),
            'total_siswa' => $totalStudents,
            'aktif_7_hari' => $activeLast7Days,
            'persentase_aktif' => $totalStudents > 0 ? round(($activeLast7Days / $totalStudents) * 100) : 0,
        ];

        // Hitung Perlu Tindakan
        $inactiveStudentsCount = User::where('role', 'siswa')
            ->whereHas('classrooms', function ($q) use ($classroomIds) {
                $q->whereIn('classrooms.id', $classroomIds);
            })
            ->where(function($q) {
                $q->where('last_login_at', '<', now()->subDays(7))
                  ->orWhereNull('last_login_at');
            })
            ->count();

        $ungradedSubmissions = \App\Models\Submission::whereNull('score')
            ->whereHas('assessment', function($q) use ($teacher) {
                $q->where('created_by', $teacher->id);
            })->count();

        $draftLessons = \App\Models\Lesson::where('created_by', $teacher->id)
            ->where('status', 'draft')->count();

        $draftSummative = \App\Models\Assessment::where('created_by', $teacher->id)
            ->where('type', 'sumatif')
            ->where('status', 'draft')->count();

        $actionItems = [
            'tugas_perlu_dinilai' => $ungradedSubmissions,
            'siswa_perlu_perhatian' => $inactiveStudentsCount,
            'subbab_draf' => $draftLessons,
            'sumatif_belum_buka' => $draftSummative,
        ];

        $now = now()->timezone('Asia/Jakarta');
        $todayIso = $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $currentTimeFloat = (float) $now->format('H.i');

        $getPeriodTime = function($day, $period) {
            if ($day <= 4) {
                $startTimes = [
                    1 => '07.00', 2 => '07.45', 3 => '08.30', 4 => '09.15', 5 => '10.15',
                    6 => '11.00', 7 => '12.30', 8 => '13.15', 9 => '14.00', 10 => '14.45', 11 => '15.30', 12 => '16.15'
                ];
                $endTimes = [
                    1 => '07.45', 2 => '08.30', 3 => '09.15', 4 => '10.00', 5 => '11.00',
                    6 => '11.45', 7 => '13.15', 8 => '14.00', 9 => '14.45', 10 => '15.30', 11 => '16.15', 12 => '17.00'
                ];
            } else {
                $startTimes = [
                    2 => '07.45', 3 => '08.30', 4 => '09.30', 5 => '10.15'
                ];
                $endTimes = [
                    2 => '08.30', 3 => '09.15', 4 => '10.15', 5 => '11.00'
                ];
            }
            return ['start' => $startTimes[$period] ?? '?', 'end' => $endTimes[$period] ?? '?'];
        };

        $todaySchedule = [];

        foreach ($assignments as $assignment) {
            foreach ($assignment->scheduleEntries as $entry) {
                if ($entry->day_of_week == $todayIso) {
                    $startInfo = $getPeriodTime($entry->day_of_week, $entry->start_period_no);
                    $endInfo = $getPeriodTime($entry->day_of_week, $entry->end_period_no);
                    
                    $startTime = $startInfo['start'];
                    $endTime = $endInfo['end'];
                    $jpCount = ($entry->end_period_no - $entry->start_period_no) + 1;

                    // Convert to float for comparison e.g. '10.15' -> 10.15
                    $startFloat = (float) $startTime;
                    $endFloat = (float) $endTime;
                    
                    $status = 'Nanti';
                    if ($currentTimeFloat > $endFloat) {
                        $status = 'Selesai';
                    } elseif ($currentTimeFloat >= $startFloat && $currentTimeFloat <= $endFloat) {
                        $status = 'Sedang berlangsung';
                    }

                    // Get topic and attendance
                    $attendanceSession = \App\Models\AttendanceSession::where('classroom_id', $assignment->classroom->id)
                        ->whereDate('meeting_date', $now->toDateString())
                        ->with('records')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($attendanceSession) {
                        $topicTitle = $attendanceSession->topic ?? 'Pertemuan ' . $attendanceSession->meeting_no;
                        $topicLink = route('guru.absensi.session', $assignment->classroom->id);
                        
                        // Summary
                        $h = $attendanceSession->records->where('status', 'H')->count();
                        $i = $attendanceSession->records->where('status', 'I')->count();
                        $s = $attendanceSession->records->where('status', 'S')->count();
                        $a = $attendanceSession->records->where('status', 'A')->count();
                        $attendanceSummary = "H: $h, I: $i, S: $s, A: $a";
                        $hasAttendance = true;
                        $attendanceId = $attendanceSession->id;
                    } else {
                        // Find active lesson
                        $activeLesson = \App\Models\Lesson::availableFor($assignment->classroom->id)
                            ->orderBy('sort_order', 'desc')
                            ->first();
                            
                        $topicTitle = $activeLesson ? $activeLesson->code . ' - ' . $activeLesson->title : 'Belum ada materi';
                        $topicLink = route('guru.lessons');
                        $attendanceSummary = null;
                        $hasAttendance = false;
                        $attendanceId = null;
                    }

                    $todaySchedule[] = [
                        'class' => $assignment->classroom->name,
                        'class_id' => $assignment->classroom->id,
                        'time' => "{$startTime}–{$endTime}",
                        'jp' => $jpCount,
                        'start_period' => $entry->start_period_no,
                        'status' => $status,
                        'start_float' => $startFloat,
                        'topic' => $topicTitle,
                        'topic_link' => $topicLink,
                        'has_attendance' => $hasAttendance,
                        'attendance_summary' => $attendanceSummary,
                        'attendance_id' => $attendanceId,
                    ];
                }
            }
        }
        
        // Sort by start period
        usort($todaySchedule, function($a, $b) {
            return $a['start_period'] <=> $b['start_period'];
        });

        // Find which is 'Berikutnya' if there are 'Nanti'
        $foundBerikutnya = false;
        foreach ($todaySchedule as &$ts) {
            if ($ts['status'] === 'Nanti' && !$foundBerikutnya) {
                $ts['status'] = 'Berikutnya';
                $foundBerikutnya = true;
            }
        }

        // Default active schedule selection
        $activeIdx = 0;
        foreach ($todaySchedule as $i => $ts) {
            if (in_array($ts['status'], ['Sedang berlangsung', 'Berikutnya'])) {
                $activeIdx = $i;
                break;
            }
        }
        $this->activeScheduleIndex = $this->activeScheduleIndex ?? $activeIdx;
        
        if ($this->activeScheduleIndex >= count($todaySchedule)) {
            $this->activeScheduleIndex = 0;
        }

        $currentDate = $now->translatedFormat('l, j F Y');
        
        $guruSubtitleSetting = \App\Models\Setting::getValue('dashboard_guru_subtitle', 'Semester Ganjil 2026/2027');
        $academicYear = $guruSubtitleSetting !== null && $guruSubtitleSetting !== '' ? $guruSubtitleSetting : '';

        $guruTitleTemplate = \App\Models\Setting::getValue('dashboard_guru_title', 'Halo, Guru {nama}. Siap mengajar hari ini?');
        $firstName = explode(' ', auth()->user()->name)[0];
        $fullName = auth()->user()->name;
        $greetingTitle = str_replace(
            ['{nama}', '{name}', '{nama_depan}', '{nama_panggilan}', '{nama_lengkap}', '{fullname}'],
            [$firstName, $firstName, $firstName, $firstName, $fullName, $fullName],
            $guruTitleTemplate
        );

        return view('livewire.guru.dashboard', [
            'classrooms' => $classrooms,
            'stats' => $stats,
            'actionItems' => $actionItems,
            'todaySchedule' => $todaySchedule,
            'inactiveStudents' => $inactiveStudents,
            'currentDate' => strtoupper($currentDate),
            'academicYear' => strtoupper($academicYear),
            'greetingTitle' => $greetingTitle,
            'todayName' => $now->translatedFormat('l'),
        ]);
    }
}
