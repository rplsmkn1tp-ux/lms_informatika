<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\LearningObjective;
use App\Models\ObjectiveMastery;
use App\Models\Setting;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class SiswaGradeService
{
    /**
     * Mengambil semua nilai siswa yang login untuk kelas dan tahun ajaran tertentu.
     * Menggabungkan kolom penilaian buku nilai guru (UH, MID, UAS, Tugas)
     * dan asesmen LMS, menggunakan satu aturan KKTP yang konsisten.
     *
     * @param int $studentId ID siswa yang sedang login (keamanan backend)
     * @param int $classroomId ID kelas siswa
     * @param int $academicYearId ID tahun ajaran
     * @return Collection
     */
    public function getStudentGrades(int $studentId, int $classroomId, int $academicYearId): Collection
    {
        $student = User::find($studentId);
        $classroom = Classroom::find($classroomId);

        if (!$student || !$classroom) {
            return collect();
        }

        // Verifikasi bahwa siswa terdaftar di kelas ini (keamanan backend)
        $isEnrolled = $student->classrooms()->where('classrooms.id', $classroomId)->exists();
        if (!$isEnrolled) {
            return collect();
        }

        // Sinkronkan tugas kelas ke Buku Nilai secara otomatis
        try {
            app(GradeBookSyncService::class)->syncClassroom($classroom);
        } catch (\Throwable $e) {
            // Abaikan kesalahan non-fatal saat sinkronisasi
        }

        $defaultKktp = (float) Setting::getValue('default_kktp', 75);

        // Ambil semua kolom nilai kelas
        $columns = GradeColumn::where('classroom_id', $classroomId)
            ->where('academic_year_id', $academicYearId)
            ->with(['learningObjectives', 'learningObjective.element'])
            ->orderBy('group')
            ->orderBy('seq')
            ->get();

        // Ambil nilai milik siswa yang bersangkutan saja (keamanan backend)
        $entries = GradeEntry::whereIn('column_id', $columns->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('column_id');

        // Ambil asesmen kelas untuk melengkapi metadata (TP, Elemen, Tanggal/Tenggat, Feedback submission)
        $assessments = Assessment::whereHas('classrooms', function ($q) use ($classroomId) {
            $q->where('classrooms.id', $classroomId);
        })
        ->with(['learningObjective.element', 'learningObjectives', 'element', 'creator'])
        ->with(['submissions' => function ($q) use ($studentId) {
            $q->where('student_id', $studentId)->latest('attempt');
        }])
        ->get();

        // Buat map asesmen berdasarkan group dan seq (sesuai logika GradeBookSyncService)
        $syncService = app(GradeBookSyncService::class);
        $assessmentMap = [];
        $matchedAssessmentIds = [];

        foreach ($assessments as $ass) {
            $group = strtolower($ass->grade_group ?: ($ass->type === 'tugas' ? 'tugas' : 'uh'));
            $seq = $syncService->resolveAssessmentSeq($ass, $classroomId, $group);
            $key = "{$group}_{$seq}";
            if (!isset($assessmentMap[$key])) {
                $assessmentMap[$key] = $ass;
            }
        }

        $items = collect();

        foreach ($columns as $col) {
            $group = strtolower($col->group);
            $key = "{$group}_{$col->seq}";
            $assessment = $assessmentMap[$key] ?? null;

            if ($assessment) {
                $matchedAssessmentIds[] = $assessment->id;
            }

            $submission = $assessment?->submissions->first();
            $entry = $entries->get($col->id);

            // Nilai diperoleh: prioritaskan entry buku nilai, atau submission score
            $score = null;
            if ($entry && $entry->score !== null) {
                $score = (float) $entry->score;
            } elseif ($submission && $submission->score !== null) {
                $score = (float) $submission->score;
            }

            $maxScore = $col->max_score > 0 ? (float) $col->max_score : 100.0;
            $kktp = $assessment ? (float) $assessment->effectiveKktp() : $defaultKktp;

            // Normalisasi ke skala 100 untuk perbandingan KKTP yang konsisten
            $normalizedScore = $score !== null ? (($score / $maxScore) * 100.0) : null;
            $isGraded = ($score !== null);
            $isAchieved = $isGraded ? ($normalizedScore >= $kktp) : null;

            // Status KKTP
            $statusKktp = $isGraded ? ($isAchieved ? 'Tercapai' : 'Belum Tercapai') : 'Belum dinilai';

            // Status label & badge
            $statusKey = 'belum_dinilai';
            $statusLabel = 'Belum dinilai';
            $badgeClass = 'bg-zinc-100 text-zinc-600 border-zinc-200';

            if ($submission) {
                if ($isGraded) {
                    $statusKey = 'dinilai';
                    $statusLabel = 'Sudah Dinilai';
                    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                } elseif ($submission->status === 'dikumpulkan') {
                    $statusKey = 'dikumpulkan';
                    $statusLabel = 'Menunggu Penilaian';
                    $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                } elseif ($submission->status === 'perlu_revisi') {
                    $statusKey = 'perlu_revisi';
                    $statusLabel = 'Perlu Revisi';
                    $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                } else {
                    $statusKey = 'dikerjakan';
                    $statusLabel = 'Draft Dikerjakan';
                    $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                }
            } elseif ($isGraded) {
                $statusKey = 'dinilai';
                $statusLabel = 'Sudah Dinilai';
                $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }

            // Tenggat / Tanggal
            $rawDue = $assessment?->dueAtForClassroom($classroomId);
            $dueAt = null;
            if ($rawDue) {
                try {
                    $dueAt = $rawDue instanceof \Carbon\CarbonInterface ? $rawDue : \Carbon\Carbon::parse($rawDue);
                } catch (\Throwable $e) {
                    $dueAt = null;
                }
            }
            $date = $dueAt ?? $submission?->submitted_at ?? $entry?->updated_at ?? $col->created_at;

            // Kategori filter tab:
            // "tugas" => tugas
            // "ujian" => uh, mid, uas, kuis, ujian
            $filterCategory = 'lainnya';
            if ($group === 'tugas') {
                $filterCategory = 'tugas';
            } elseif (in_array($group, ['uh', 'mid', 'uas', 'kuis', 'ujian'])) {
                $filterCategory = 'ujian';
            }

            $namaKolom = $col->label;
            $title = $assessment?->title ? ($col->label . ' - ' . $assessment->title) : $col->label;

            // Kaitan TP (gabungkan TP kolom buku nilai dan TP asesmen)
            $tpIds = array_values(array_unique(array_filter(array_merge(
                $col->getAllLearningObjectiveIds(),
                $assessment ? $assessment->getAllLearningObjectiveIds() : []
            ))));

            $tpCode = $assessment?->learningObjective?->code ?? $col->learningObjective?->code;
            $tpDesc = $assessment?->learningObjective?->description ?? $col->learningObjective?->description;
            $elementName = $assessment?->element?->name ?? $col->learningObjective?->element?->name;

            $items->push((object) [
                'id' => 'col_' . $col->id,
                'column_id' => $col->id,
                'assessment_id' => $assessment?->id,
                'nama_kolom' => $namaKolom,
                'title' => $title,
                'raw_title' => $assessment?->title ?: $col->label,
                'jenis' => $group,
                'grade_group' => $group,
                'seq' => $col->seq,
                'max_score' => $maxScore,
                'score' => $score,
                'normalized_score' => $normalizedScore,
                'kktp' => $kktp,
                'is_graded' => $isGraded,
                'is_achieved' => $isAchieved,
                'status_kktp' => $statusKktp,
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'feedback' => $submission?->feedback,
                'submitted_at' => $submission?->submitted_at,
                'due_at' => $dueAt,
                'date' => $date,
                'learning_objective_ids' => $tpIds,
                'tp_code' => $tpCode,
                'tp_desc' => $tpDesc,
                'element_name' => $elementName,
                'filter_category' => $filterCategory,
            ]);
        }

        // Asesmen yang belum terpetakan ke kolom manapun (safety fallback)
        foreach ($assessments as $ass) {
            if (in_array($ass->id, $matchedAssessmentIds)) {
                continue;
            }

            $submission = $ass->submissions->first();
            $score = ($submission && $submission->score !== null) ? (float) $submission->score : null;
            $maxScore = $ass->max_score > 0 ? (float) $ass->max_score : 100.0;
            $kktp = (float) $ass->effectiveKktp();
            $normalizedScore = $score !== null ? (($score / $maxScore) * 100.0) : null;
            $isGraded = ($score !== null);
            $isAchieved = $isGraded ? ($normalizedScore >= $kktp) : null;
            $statusKktp = $isGraded ? ($isAchieved ? 'Tercapai' : 'Belum Tercapai') : 'Belum dinilai';

            $group = strtolower($ass->grade_group ?: ($ass->type === 'tugas' ? 'tugas' : 'uh'));
            $filterCategory = in_array($group, ['uh', 'mid', 'uas', 'kuis', 'ujian']) ? 'ujian' : 'tugas';

            $tpIds = $ass->getAllLearningObjectiveIds();

            $items->push((object) [
                'id' => 'ass_' . $ass->id,
                'column_id' => null,
                'assessment_id' => $ass->id,
                'nama_kolom' => $ass->title,
                'title' => $ass->title,
                'raw_title' => $ass->title,
                'jenis' => $group,
                'grade_group' => $group,
                'seq' => $ass->grade_seq ?? 99,
                'max_score' => $maxScore,
                'score' => $score,
                'normalized_score' => $normalizedScore,
                'kktp' => $kktp,
                'is_graded' => $isGraded,
                'is_achieved' => $isAchieved,
                'status_kktp' => $statusKktp,
                'status_key' => $isGraded ? 'dinilai' : 'belum_dinilai',
                'status_label' => $isGraded ? 'Sudah Dinilai' : 'Belum dinilai',
                'badge_class' => $isGraded ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-100 text-zinc-600 border-zinc-200',
                'feedback' => $submission?->feedback,
                'submitted_at' => $submission?->submitted_at,
                'due_at' => $ass->dueAtForClassroom($classroomId),
                'date' => $ass->dueAtForClassroom($classroomId) ?? $submission?->submitted_at,
                'learning_objective_ids' => $tpIds,
                'tp_code' => $ass->learningObjective?->code,
                'tp_desc' => $ass->learningObjective?->description,
                'element_name' => $ass->element?->name,
                'filter_category' => $filterCategory,
            ]);
        }

        // Urutkan item secara konsisten berdasarkan group lalu seq
        return $items->sortBy([
            ['grade_group', 'asc'],
            ['seq', 'asc'],
        ])->values();
    }

    /**
     * Menghitung ringkasan statistik nilai siswa dari daftar grade items.
     *
     * @param Collection $items
     * @param array|null $reportData
     * @return array
     */
    public function getGradeSummary(Collection $items, ?array $reportData = null): array
    {
        $total = $items->count();
        $graded = $items->where('is_graded', true);
        $gradedCount = $graded->count();
        $ungradedCount = $total - $gradedCount;
        $achievedCount = $graded->where('is_achieved', true)->count();
        $unachievedCount = $graded->where('is_achieved', false)->count();

        $avgScore = null;
        if ($gradedCount > 0) {
            $avgScore = round($graded->avg('normalized_score'), 1);
        } elseif ($reportData && ($reportData['final_score'] ?? null) !== null) {
            $avgScore = round($reportData['final_score'], 1);
        }

        return [
            'total_assessments' => $total,
            'graded_count' => $gradedCount,
            'ungraded_count' => $ungradedCount,
            'achieved_count' => $achievedCount,
            'unachieved_count' => $unachievedCount,
            'average_score' => $avgScore,
            'attendance_percentage' => $reportData['attendance_score'] ?? 0,
        ];
    }

    /**
     * Menghitung pencapaian Tujuan Pembelajaran (TP) untuk siswa tertentu
     * berdasarkan semua tugas dan kolom nilai guru yang sudah dinilai dan tersambung ke TP tersebut.
     *
     * Aturan:
     * - Nilai TP = rata-rata nilai (dinormalkan ke 0-100) dari semua tugas dan kolom nilai
     *   yang sudah dinilai dan tersambung ke TP itu, untuk siswa yang login.
     * - Status:
     *     - "Belum Diuji" jika belum ada nilai
     *     - "Tercapai" jika nilai >= KKTP
     *     - "Belum Tercapai" jika di bawah KKTP
     * - KKTP diambil dari KKTP TP itu jika diisi (> 0), jika tidak dari Default KKTP Pengaturan Sekolah.
     * - Menghitung jumlah penilaian yang dievaluasi (misal "3 penilaian").
     * - Menyimpan/memperbarui sinkronisasi ke tabel objective_mastery.
     *
     * @param int $studentId
     * @param int $classroomId
     * @param int $academicYearId
     * @param Collection|null $allGradeItems
     * @return array ['elements' => Collection, 'masteries' => Collection]
     */
    public function getStudentTpMasteries(int $studentId, int $classroomId, int $academicYearId, ?Collection $allGradeItems = null): array
    {
        if ($allGradeItems === null) {
            $allGradeItems = $this->getStudentGrades($studentId, $classroomId, $academicYearId);
        }

        $defaultKktp = (float) Setting::getValue('default_kktp', 75);

        $teacherIds = TeachingAssignment::where('classroom_id', $classroomId)
            ->pluck('teacher_id')
            ->toArray();

        $usedTpIds = $allGradeItems->pluck('learning_objective_ids')->flatten()->unique()->filter()->values()->toArray();

        $elements = Element::with(['learningObjectives' => function ($q) use ($teacherIds, $usedTpIds) {
            $q->where(function ($sub) use ($teacherIds, $usedTpIds) {
                if (!empty($teacherIds)) {
                    $sub->whereIn('created_by', $teacherIds);
                }
                $sub->orWhere('created_by', 1)
                    ->orWhere('is_template', true);
                if (!empty($usedTpIds)) {
                    $sub->orWhereIn('id', $usedTpIds);
                }
            })->orderBy('sort_order');
        }])->orderBy('id')->get();

        $masteriesMap = collect();

        foreach ($elements as $element) {
            foreach ($element->learningObjectives as $tp) {
                $effectiveKktp = ($tp->kktp && (float) $tp->kktp > 0) ? (float) $tp->kktp : $defaultKktp;

                // Cari semua item penilaian (tugas dan kolom nilai) yang tersambung ke TP ini
                $connectedItems = $allGradeItems->filter(function ($item) use ($tp) {
                    $ids = $item->learning_objective_ids ?? [];
                    return in_array($tp->id, $ids);
                })->values();

                // Hanya item yang sudah dinilai dan memiliki skor
                $gradedItems = $connectedItems->filter(function ($item) {
                    return !empty($item->is_graded) && $item->score !== null && $item->normalized_score !== null;
                })->values();

                $gradedCount = $gradedItems->count();
                $avgScore = $gradedCount > 0 ? round($gradedItems->avg('normalized_score'), 1) : null;

                if ($gradedCount > 0) {
                    $isAchieved = ($avgScore >= $effectiveKktp);
                    $statusKey = $isAchieved ? 'tercapai' : 'belum_tercapai';
                    $statusLabel = $isAchieved ? 'Tercapai' : 'Belum Tercapai';
                    $badgeBg = $isAchieved ? '#E7F3EC' : '#FBEDE5';
                    $badgeText = $isAchieved ? '#1D6B45' : '#D32F2F';
                    $badgeBorder = $isAchieved ? '#C4E3D2' : '#F5C6CB';
                    $dbStatus = $isAchieved ? ($avgScore >= 85 ? 'pengayaan' : 'tercapai') : 'remedial';
                } else {
                    $isAchieved = null;
                    $statusKey = 'belum_diuji';
                    $statusLabel = 'Belum Diuji';
                    $badgeBg = '#F4F5F2';
                    $badgeText = '#4A515C';
                    $badgeBorder = '#E2E4DF';
                    $dbStatus = 'belum';
                }

                $countLabel = $gradedCount > 0 ? ($gradedCount . ' penilaian') : '0 penilaian';

                // Sinkronkan ke tabel objective_mastery
                try {
                    ObjectiveMastery::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'learning_objective_id' => $tp->id,
                            'classroom_id' => $classroomId,
                        ],
                        [
                            'best_score' => $avgScore,
                            'status' => $dbStatus,
                        ]
                    );
                } catch (\Throwable $e) {
                }

                // Tempelkan metadata langsung ke instance TP
                $tp->effective_kktp = $effectiveKktp;
                $tp->calculated_score = $avgScore;
                $tp->status_key = $statusKey;
                $tp->calculated_status = $statusLabel;
                $tp->badge_bg = $badgeBg;
                $tp->badge_text = $badgeText;
                $tp->badge_border = $badgeBorder;
                $tp->graded_count = $gradedCount;
                $tp->total_connected_count = $connectedItems->count();
                $tp->count_label = $countLabel;
                $tp->connected_items = $connectedItems;
                $tp->graded_items = $gradedItems;

                $masteriesMap->put($tp->id, (object) [
                    'learning_objective_id' => $tp->id,
                    'tp_code' => $tp->code,
                    'tp_desc' => $tp->description,
                    'best_score' => $avgScore,
                    'score' => $avgScore,
                    'status' => $dbStatus,
                    'status_key' => $statusKey,
                    'status_label' => $statusLabel,
                    'effective_kktp' => $effectiveKktp,
                    'graded_count' => $gradedCount,
                    'count_label' => $countLabel,
                    'badge_bg' => $badgeBg,
                    'badge_text' => $badgeText,
                    'badge_border' => $badgeBorder,
                    'connected_items' => $connectedItems,
                    'graded_items' => $gradedItems,
                ]);
            }
        }

        $elementsWithTp = $elements->filter(fn($e) => $e->learningObjectives->isNotEmpty())->values();

        return [
            'elements' => $elementsWithTp,
            'masteries' => $masteriesMap,
        ];
    }
}
