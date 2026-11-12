<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\Classroom;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\LearningObjective;
use Illuminate\Support\Facades\Auth;

class GlobalSearch extends Component
{
    public string $query = '';
    public string $role = '';
    public string $placeholder = '';
    public bool $isOpen = false;
    public bool $compact = false;

    public function mount(?string $role = null, ?string $placeholder = null, bool $compact = false)
    {
        $this->role = $role ?: (Auth::user()?->role ?? 'siswa');
        $this->compact = $compact;

        if ($placeholder) {
            $this->placeholder = $placeholder;
        } else {
            $this->placeholder = match ($this->role) {
                'admin' => 'Cari pengguna, kelas, kurikulum…',
                'guru' => 'Cari tugas, materi, kelas, siswa…',
                default => 'Cari materi, tugas, nilai…',
            };
        }
    }

    public function clear(): void
    {
        $this->query = '';
    }

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function getQuickMenusProperty(): array
    {
        $user = Auth::user();

        if ($this->role === 'admin') {
            return [
                ['title' => 'Dashboard Admin', 'subtitle' => 'Statistik dan ringkasan sistem', 'icon' => 'home', 'url' => route('admin.dashboard'), 'keywords' => 'dashboard admin beranda utama'],
                ['title' => 'Verifikasi Guru', 'subtitle' => 'Persetujuan pendaftaran akun guru', 'icon' => 'check-badge', 'url' => route('admin.verifikasi'), 'keywords' => 'verifikasi guru persetujuan akun pending daftar'],
                ['title' => 'Manajemen Pengguna', 'subtitle' => 'Kelola akun siswa, guru, dan admin', 'icon' => 'users', 'url' => route('admin.users'), 'keywords' => 'pengguna user siswa guru admin murid akun'],
                ['title' => 'Tahun Ajaran', 'subtitle' => 'Atur semester & tahun pelajaran aktif', 'icon' => 'calendar', 'url' => route('admin.academic-years'), 'keywords' => 'tahun ajaran semester periode kalender'],
                ['title' => 'Manajemen Kelas', 'subtitle' => 'Daftar kelas, rombel, dan kode gabung', 'icon' => 'academic-cap', 'url' => route('admin.classrooms'), 'keywords' => 'kelas rombel tingkat kode gabung classroom'],
                ['title' => 'Plot Mengajar', 'subtitle' => 'Penugasan guru mengajar di kelas', 'icon' => 'clipboard-check', 'url' => route('admin.teaching-assignments'), 'keywords' => 'plot mengajar pembagian tugas guru mapel'],
                ['title' => 'Jam Pelajaran', 'subtitle' => 'Master waktu bel & jam sesi tatap muka', 'icon' => 'clock', 'url' => route('admin.jadwal.waktu'), 'keywords' => 'jam pelajaran waktu bel jam ke period sesi'],
                ['title' => 'Kurikulum & Materi', 'subtitle' => 'Struktur elemen, materi & capaian', 'icon' => 'book-open', 'url' => route('admin.curriculum'), 'keywords' => 'kurikulum materi elemen capaian pelajaran'],
                ['title' => 'Laporan Absensi', 'subtitle' => 'Rekap kehadiran siswa per kelas', 'icon' => 'chart-bar', 'url' => route('admin.laporan-absensi'), 'keywords' => 'laporan absensi presensi rekap kehadiran siswa'],
                ['title' => 'Laporan Kelulusan', 'subtitle' => 'Status dan kelulusan siswa', 'icon' => 'document-chart-bar', 'url' => route('admin.laporan-kelulusan'), 'keywords' => 'laporan kelulusan tamat lulus siswa alumni'],
                ['title' => 'Pengaturan Sekolah', 'subtitle' => 'Identitas instansi, logo, dan KKTP', 'icon' => 'cog-8-tooth', 'url' => route('admin.settings'), 'keywords' => 'pengaturan setting instansi sekolah logo default kktp'],
                ['title' => 'Profil Saya', 'subtitle' => 'Pengaturan akun dan profil admin', 'icon' => 'user', 'url' => route('profil'), 'keywords' => 'profil admin akun password'],
            ];
        }

        if ($this->role === 'guru') {
            return [
                ['title' => 'Dashboard Guru', 'subtitle' => 'Ikhtisar aktivitas mengajar & tugas', 'icon' => 'home', 'url' => route('guru.dashboard'), 'keywords' => 'dashboard beranda guru awal'],
                ['title' => 'Kelola Tugas', 'subtitle' => 'Daftar dan status tugas siswa', 'icon' => 'clipboard-check', 'url' => route('guru.tugas.index'), 'keywords' => 'tugas buat periksa nilai assignment asesmen'],
                ['title' => 'Buat Tugas Baru', 'subtitle' => 'Formulir pembuatan tugas atau asesmen', 'icon' => 'plus-circle', 'url' => route('guru.tugas.create'), 'keywords' => 'buat tugas baru bikin add tambah'],
                ['title' => 'Materi Pembelajaran', 'subtitle' => 'Kelola subbab & publikasi materi', 'icon' => 'book-open', 'url' => route('guru.lessons'), 'keywords' => 'materi modul subbab pelajaran bab'],
                ['title' => 'Tujuan Pembelajaran (TP)', 'subtitle' => 'Kelola TP dan alur tujuan', 'icon' => 'bookmark', 'url' => route('guru.tp'), 'keywords' => 'tujuan pembelajaran tp atp kktp kurikulum'],
                ['title' => 'Buku Nilai', 'subtitle' => 'Pengisian dan rekap nilai per kelas', 'icon' => 'table-cells', 'url' => route('guru.buku-nilai.index'), 'keywords' => 'buku nilai ledger skor rekap spreadsheet'],
                ['title' => 'Absensi Siswa', 'subtitle' => 'Buka sesi & catat presensi kelas', 'icon' => 'calendar-days', 'url' => route('guru.absensi.index'), 'keywords' => 'absensi presensi kehadiran hadir sesi kelas'],
                ['title' => 'Agenda Mengajar', 'subtitle' => 'Jurnal dan catatan pertemuan ajar', 'icon' => 'calendar', 'url' => route('guru.agenda.index'), 'keywords' => 'agenda jurnal pertemuan mengajar catatan harian'],
                ['title' => 'Jadwal Pelajaran', 'subtitle' => 'Jadwal tatap muka mingguan', 'icon' => 'clock', 'url' => route('guru.jadwal'), 'keywords' => 'jadwal jam pelajaran waktu sesi mengajar'],
                ['title' => 'Peta Ketercapaian', 'subtitle' => 'Visualisasi penguasaan TP siswa', 'icon' => 'chart-bar', 'url' => route('guru.peta-ketercapaian'), 'keywords' => 'peta ketercapaian grafik capaian evaluasi'],
                ['title' => 'Laporan Rapor', 'subtitle' => 'Cetak dan rekap rapor siswa', 'icon' => 'document-text', 'url' => route('guru.laporan-rapor'), 'keywords' => 'rapor cetak laporan akhir semester'],
                ['title' => 'Profil Guru', 'subtitle' => 'Data pribadi dan pengaturan akun', 'icon' => 'user', 'url' => route('profil'), 'keywords' => 'profil akun user guru sandi password'],
            ];
        }

        // Siswa
        return [
            ['title' => 'Beranda', 'subtitle' => 'Dashboard ringkasan siswa', 'icon' => 'home', 'url' => route('siswa.beranda'), 'keywords' => 'beranda home dashboard awal'],
            ['title' => 'Materi Belajar', 'subtitle' => 'Daftar materi dan modul pelajaran', 'icon' => 'book-open', 'url' => route('siswa.belajar'), 'keywords' => 'materi modul elemen belajar pelajaran'],
            ['title' => 'Tugas Siswa', 'subtitle' => 'Daftar tugas mandiri dan kelompok', 'icon' => 'clipboard-check', 'url' => route('siswa.tugas'), 'keywords' => 'tugas pr penugasan submission asesmen assessment'],
            ['title' => 'Rapor & Capaian', 'subtitle' => 'Laporan capaian belajar dan nilai akhir', 'icon' => 'document-text', 'url' => route('siswa.rapor'), 'keywords' => 'rapor nilai capaian angka hasil'],
            ['title' => 'Daftar Nilai', 'subtitle' => 'Rincian perolehan nilai tugas & kuis', 'icon' => 'table-cells', 'url' => route('siswa.nilai'), 'keywords' => 'nilai daftar asesmen rincian skor'],
            ['title' => 'Kehadiran / Absensi', 'subtitle' => 'Riwayat kehadiran kelas Anda', 'icon' => 'calendar-days', 'url' => route('siswa.absensi'), 'keywords' => 'absensi hadir kehadiran presensi izin sakit'],
            ['title' => 'Buku Nilai', 'subtitle' => 'Buku nilai digital siswa', 'icon' => 'bookmark', 'url' => route('siswa.buku-nilai'), 'keywords' => 'buku nilai ledger kktp'],
            ['title' => 'Profil Saya', 'subtitle' => 'Kelola akun dan ganti kata sandi', 'icon' => 'user', 'url' => route('profil'), 'keywords' => 'profil akun user ganti password sandi'],
        ];
    }

    public function getResultsProperty(): array
    {
        $term = trim($this->query);
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // When query is empty, return quick navigation
        if (mb_strlen($term) < 1) {
            return [
                'Navigasi Cepat' => array_slice($this->quickMenus, 0, 6)
            ];
        }

        $results = [];
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $term);

        // Filter Quick Menus
        $matchingMenus = collect($this->quickMenus)->filter(function ($item) use ($term) {
            return str_contains(mb_strtolower($item['title']), mb_strtolower($term))
                || str_contains(mb_strtolower($item['subtitle']), mb_strtolower($term))
                || str_contains(mb_strtolower($item['keywords']), mb_strtolower($term));
        })->take(4)->values()->toArray();

        if (!empty($matchingMenus)) {
            $results['Menu & Navigasi'] = $matchingMenus;
        }

        if ($this->role === 'siswa') {
            $classroomIds = $user->classrooms()->pluck('classrooms.id')->toArray();

            // 1. Tugas (Assessments)
            $tasks = Assessment::query()
                ->where(function ($q) use ($classroomIds) {
                    if (!empty($classroomIds)) {
                        $q->whereHas('classrooms', fn ($c) => $c->whereIn('classrooms.id', $classroomIds));
                    }
                })
                ->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                      ->orWhere('instructions', 'like', "%{$escaped}%");
                })
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($task) use ($user) {
                    $submission = $task->submissions()->where('student_id', $user->id)->first();
                    $badge = $submission 
                        ? ($submission->status === 'graded' ? 'Dinilai: ' . $submission->score : 'Terkumpul') 
                        : ($task->due_at && $task->due_at->isPast() ? 'Lewat Tenggat' : 'Tugas');

                    $badgeColor = $submission 
                        ? ($submission->status === 'graded' ? 'green' : 'blue') 
                        : ($task->due_at && $task->due_at->isPast() ? 'red' : 'amber');

                    $dueText = $task->due_at ? 'Tenggat: ' . $task->due_at->translatedFormat('d M Y, H:i') : 'Tanpa tenggat';

                    return [
                        'id' => 'task-' . $task->id,
                        'title' => $task->title,
                        'subtitle' => $dueText . ($submission && $submission->score !== null ? ' • Nilai: ' . $submission->score : ''),
                        'icon' => 'clipboard-check',
                        'badge' => $badge,
                        'badge_color' => $badgeColor,
                        'url' => route('siswa.tugas.kerjakan', ['assessment' => $task->id]),
                    ];
                })->toArray();

            if (!empty($tasks)) {
                $results['Tugas & Penugasan'] = $tasks;
            }

            // 2. Materi (Lessons)
            $lessons = Lesson::query()
                ->where(function ($q) use ($classroomIds) {
                    if (!empty($classroomIds)) {
                        $q->whereHas('classrooms', fn ($c) => $c->whereIn('classrooms.id', $classroomIds))
                          ->orWhere('status', 'published');
                    } else {
                        $q->where('status', 'published');
                    }
                })
                ->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                      ->orWhere('summary', 'like', "%{$escaped}%");
                })
                ->with(['learningObjective.element'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($lesson) {
                    $el = $lesson->learningObjective?->element;
                    $tp = $lesson->learningObjective;
                    $url = ($el && $tp)
                        ? route('siswa.belajar.materi', ['element' => $el->code, 'tp' => $tp->code, 'lesson' => $lesson->id])
                        : route('siswa.belajar');

                    return [
                        'id' => 'lesson-' . $lesson->id,
                        'title' => $lesson->title,
                        'subtitle' => ($el ? $el->name . ' • ' : '') . ($tp ? $tp->code : 'Materi Pembelajaran'),
                        'icon' => 'book-open',
                        'badge' => 'Materi',
                        'badge_color' => 'blue',
                        'url' => $url,
                    ];
                })->toArray();

            if (!empty($lessons)) {
                $results['Materi Pelajaran'] = $lessons;
            }

            // 3. Tujuan Pembelajaran (TP)
            $tps = LearningObjective::query()
                ->where(function ($q) use ($escaped) {
                    $q->where('code', 'like', "%{$escaped}%")
                      ->orWhere('title', 'like', "%{$escaped}%")
                      ->orWhere('description', 'like', "%{$escaped}%");
                })
                ->with('element')
                ->take(4)
                ->get()
                ->map(function ($tp) {
                    $el = $tp->element;
                    $url = $el ? route('siswa.belajar.tp', ['element' => $el->code]) : route('siswa.belajar');

                    return [
                        'id' => 'tp-' . $tp->id,
                        'title' => $tp->code . ': ' . ($tp->title ?: \Illuminate\Support\Str::limit($tp->description, 60)),
                        'subtitle' => $el ? 'Elemen: ' . $el->name : 'Tujuan Pembelajaran',
                        'icon' => 'bookmark',
                        'badge' => 'TP',
                        'badge_color' => 'purple',
                        'url' => $url,
                    ];
                })->toArray();

            if (!empty($tps)) {
                $results['Tujuan Pembelajaran (TP)'] = $tps;
            }
        } elseif ($this->role === 'guru') {
            $teacherClassroomIds = $user->taughtClassrooms()->pluck('classrooms.id')->toArray();

            // 1. Tugas (Assessments)
            $tasks = Assessment::query()
                ->where(function ($q) use ($user, $teacherClassroomIds) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('classrooms', fn ($c) => $c->whereIn('classrooms.id', $teacherClassroomIds));
                })
                ->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                      ->orWhere('instructions', 'like', "%{$escaped}%");
                })
                ->with('classrooms')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($task) {
                    $classNames = $task->classrooms->pluck('name')->join(', ');
                    $dueText = $task->due_at ? 'Tenggat: ' . $task->due_at->translatedFormat('d M Y, H:i') : 'Tanpa tenggat';

                    return [
                        'id' => 'task-' . $task->id,
                        'title' => $task->title,
                        'subtitle' => ($classNames ? 'Kelas: ' . $classNames . ' • ' : '') . $dueText,
                        'icon' => 'clipboard-check',
                        'badge' => $task->status === 'published' ? 'Terbit' : 'Draft',
                        'badge_color' => $task->status === 'published' ? 'green' : 'gray',
                        'url' => route('guru.tugas.detail', ['assessment' => $task->id]),
                    ];
                })->toArray();

            if (!empty($tasks)) {
                $results['Tugas'] = $tasks;
            }

            // 2. Kelas Ajar
            $classrooms = Classroom::query()
                ->where(function ($q) use ($teacherClassroomIds) {
                    if (!empty($teacherClassroomIds)) {
                        $q->whereIn('id', $teacherClassroomIds);
                    }
                })
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                      ->orWhere('join_code', 'like', "%{$escaped}%");
                })
                ->withCount('students')
                ->take(4)
                ->get()
                ->map(function ($classroom) {
                    return [
                        'id' => 'class-' . $classroom->id,
                        'title' => 'Kelas ' . $classroom->name,
                        'subtitle' => 'Kode: ' . $classroom->join_code . ' • ' . $classroom->students_count . ' Siswa',
                        'icon' => 'academic-cap',
                        'badge' => 'Kelas',
                        'badge_color' => 'blue',
                        'url' => route('guru.classroom', ['classroom' => $classroom->id]),
                    ];
                })->toArray();

            if (!empty($classrooms)) {
                $results['Kelas Ajar'] = $classrooms;
            }

            // 3. Siswa (Enrolled in teacher's classes)
            $students = User::query()
                ->where('role', 'siswa')
                ->where(function ($q) use ($teacherClassroomIds) {
                    if (!empty($teacherClassroomIds)) {
                        $q->whereHas('classrooms', fn ($c) => $c->whereIn('classrooms.id', $teacherClassroomIds));
                    }
                })
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                      ->orWhere('nisn', 'like', "%{$escaped}%")
                      ->orWhere('email', 'like', "%{$escaped}%");
                })
                ->with('classrooms')
                ->take(5)
                ->get()
                ->map(function ($student) {
                    $cls = $student->classrooms->first();
                    $url = $cls ? route('guru.classroom', ['classroom' => $cls->id]) : route('guru.buku-nilai.index');

                    return [
                        'id' => 'student-' . $student->id,
                        'title' => $student->name,
                        'subtitle' => ($student->nisn ? 'NISN: ' . $student->nisn . ' • ' : '') . ($cls ? 'Kelas: ' . $cls->name : $student->email),
                        'icon' => 'user',
                        'badge' => 'Siswa',
                        'badge_color' => 'purple',
                        'url' => $url,
                    ];
                })->toArray();

            if (!empty($students)) {
                $results['Siswa'] = $students;
            }

            // 4. Materi
            $lessons = Lesson::query()
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('status', 'published');
                })
                ->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                      ->orWhere('summary', 'like', "%{$escaped}%");
                })
                ->with(['learningObjective.element'])
                ->latest()
                ->take(4)
                ->get()
                ->map(function ($lesson) {
                    return [
                        'id' => 'lesson-' . $lesson->id,
                        'title' => $lesson->title,
                        'subtitle' => $lesson->learningObjective?->element?->name ?? 'Materi Pembelajaran',
                        'icon' => 'book-open',
                        'badge' => $lesson->status === 'published' ? 'Terbit' : 'Draft',
                        'badge_color' => $lesson->status === 'published' ? 'green' : 'gray',
                        'url' => route('guru.lessons.publish', ['lesson' => $lesson->id]),
                    ];
                })->toArray();

            if (!empty($lessons)) {
                $results['Materi Pembelajaran'] = $lessons;
            }

            // 5. TP
            $tps = LearningObjective::query()
                ->where(function ($q) use ($escaped) {
                    $q->where('code', 'like', "%{$escaped}%")
                      ->orWhere('title', 'like', "%{$escaped}%")
                      ->orWhere('description', 'like', "%{$escaped}%");
                })
                ->with('element')
                ->take(4)
                ->get()
                ->map(function ($tp) {
                    return [
                        'id' => 'tp-' . $tp->id,
                        'title' => $tp->code . ': ' . ($tp->title ?: \Illuminate\Support\Str::limit($tp->description, 60)),
                        'subtitle' => $tp->element?->name ?? 'Tujuan Pembelajaran',
                        'icon' => 'bookmark',
                        'badge' => 'TP',
                        'badge_color' => 'purple',
                        'url' => route('guru.tp'),
                    ];
                })->toArray();

            if (!empty($tps)) {
                $results['Tujuan Pembelajaran'] = $tps;
            }
        } elseif ($this->role === 'admin') {
            // 1. Pengguna (All roles)
            $users = User::query()
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                      ->orWhere('email', 'like', "%{$escaped}%")
                      ->orWhere('nisn', 'like', "%{$escaped}%")
                      ->orWhere('nip_nuptk', 'like', "%{$escaped}%");
                })
                ->latest()
                ->take(6)
                ->get()
                ->map(function ($u) {
                    $roleLabel = match ($u->role) {
                        'admin' => 'Admin',
                        'guru' => 'Guru',
                        default => 'Siswa',
                    };
                    $roleColor = match ($u->role) {
                        'admin' => 'red',
                        'guru' => 'blue',
                        default => 'green',
                    };

                    $meta = $u->email;
                    if ($u->nisn) $meta .= ' • NISN: ' . $u->nisn;
                    if ($u->nip_nuptk) $meta .= ' • NIP: ' . $u->nip_nuptk;

                    return [
                        'id' => 'user-' . $u->id,
                        'title' => $u->name,
                        'subtitle' => $meta,
                        'icon' => 'user',
                        'badge' => $roleLabel,
                        'badge_color' => $roleColor,
                        'url' => route('admin.users', ['search' => $u->name, 'activeTab' => $u->role]),
                    ];
                })->toArray();

            if (!empty($users)) {
                $results['Pengguna'] = $users;
            }

            // 2. Kelas
            $classrooms = Classroom::query()
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                      ->orWhere('join_code', 'like', "%{$escaped}%")
                      ->orWhere('grade', 'like', "%{$escaped}%")
                      ->orWhere('major', 'like', "%{$escaped}%");
                })
                ->withCount('students')
                ->take(4)
                ->get()
                ->map(function ($classroom) {
                    return [
                        'id' => 'class-' . $classroom->id,
                        'title' => 'Kelas ' . $classroom->name,
                        'subtitle' => 'Tingkat ' . $classroom->grade . ' • Kode: ' . $classroom->join_code . ' • ' . $classroom->students_count . ' Siswa',
                        'icon' => 'academic-cap',
                        'badge' => 'Kelas',
                        'badge_color' => 'blue',
                        'url' => route('admin.classrooms'),
                    ];
                })->toArray();

            if (!empty($classrooms)) {
                $results['Kelas'] = $classrooms;
            }

            // 3. Tahun Ajaran
            $years = AcademicYear::query()
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                      ->orWhere('semester', 'like', "%{$escaped}%");
                })
                ->latest()
                ->take(4)
                ->get()
                ->map(function ($year) {
                    return [
                        'id' => 'year-' . $year->id,
                        'title' => 'Tahun Ajaran ' . $year->name,
                        'subtitle' => 'Semester: ' . ucfirst($year->semester) . ($year->is_active ? ' • Sedang Aktif' : ''),
                        'icon' => 'calendar',
                        'badge' => $year->is_active ? 'Aktif' : 'Nonaktif',
                        'badge_color' => $year->is_active ? 'green' : 'gray',
                        'url' => route('admin.academic-years'),
                    ];
                })->toArray();

            if (!empty($years)) {
                $results['Tahun Ajaran'] = $years;
            }

            // 4. Verifikasi Guru
            $pendingTeachers = User::query()
                ->where('role', 'guru')
                ->where('status', 'pending')
                ->where(function ($q) use ($escaped) {
                    $q->where('name', 'like', "%{$escaped}%")
                      ->orWhere('email', 'like', "%{$escaped}%");
                })
                ->take(4)
                ->get()
                ->map(function ($t) {
                    return [
                        'id' => 'pending-' . $t->id,
                        'title' => $t->name,
                        'subtitle' => $t->email . ' • Pendaftaran Guru',
                        'icon' => 'check-badge',
                        'badge' => 'Menunggu',
                        'badge_color' => 'amber',
                        'url' => route('admin.verifikasi'),
                    ];
                })->toArray();

            if (!empty($pendingTeachers)) {
                $results['Verifikasi Guru Pending'] = $pendingTeachers;
            }

            // 5. Materi / Kurikulum
            $lessons = Lesson::query()
                ->where(function ($q) use ($escaped) {
                    $q->where('title', 'like', "%{$escaped}%")
                      ->orWhere('summary', 'like', "%{$escaped}%");
                })
                ->with(['learningObjective.element'])
                ->latest()
                ->take(4)
                ->get()
                ->map(function ($lesson) {
                    return [
                        'id' => 'lesson-' . $lesson->id,
                        'title' => $lesson->title,
                        'subtitle' => $lesson->learningObjective?->element?->name ?? 'Kurikulum Informatika',
                        'icon' => 'book-open',
                        'badge' => 'Materi',
                        'badge_color' => 'blue',
                        'url' => route('admin.curriculum'),
                    ];
                })->toArray();

            if (!empty($lessons)) {
                $results['Kurikulum & Materi'] = $lessons;
            }
        }

        return $results;
    }

    public function render()
    {
        return view('livewire.global-search', [
            'results' => $this->results,
        ]);
    }
}
