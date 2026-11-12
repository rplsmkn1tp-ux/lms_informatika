<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\Submission;
use App\Models\User;

class GradeBookSyncService
{
    /**
     * Sinkronisasikan nilai submission satu siswa ke buku nilai kelas siswa yang bersangkutan.
     */
    public function syncSubmission(Submission $submission, ?int $classroomId = null): ?GradeEntry
    {
        if ($submission->score === null) {
            return null;
        }

        $assessment = $submission->assessment;
        if (!$assessment) {
            return null;
        }

        // Dapatkan siswa
        $student = $submission->student;
        if (!$student) {
            return null;
        }

        // Tentukan kelas
        $classroom = null;
        if ($classroomId) {
            $classroom = Classroom::find($classroomId);
        }

        if (!$classroom) {
            $classroom = $student->classrooms()
                ->whereIn('classrooms.id', $assessment->classrooms->pluck('id'))
                ->first() 
                ?? $student->activeClassroom();
        }

        if (!$classroom) {
            return null;
        }

        // Tentukan grup dan nomor urut kolom nilai
        $group = $assessment->grade_group ?: 'tugas';
        $seq = $this->resolveAssessmentSeq($assessment, $classroom->id, $group);

        if (!$seq) {
            return null;
        }

        // Dapatkan atau buat kolom nilai di kelas tersebut
        $column = $this->getOrCreateGradeColumn($classroom, $group, $seq, $assessment);

        // Skala nilai (normalisasi jika max_score tugas != 100)
        $maxScore = $assessment->max_score > 0 ? (float) $assessment->max_score : 100.0;
        $score = ($maxScore == 100.0) 
            ? (float) $submission->score 
            : round(((float) $submission->score / $maxScore) * 100.0, 2);

        // Simpan / perbarui GradeEntry
        $entry = GradeEntry::updateOrCreate(
            [
                'column_id' => $column->id,
                'student_id' => $student->id,
            ],
            [
                'score' => $score,
            ]
        );

        return $entry;
    }

    /**
     * Sinkronisasi menyeluruh untuk suatu kelas:
     * Mengambil seluruh tugas yang ditugaskan ke kelas ini dan memasukkan nilai submission siswa yang sudah dinilai.
     */
    public function syncClassroom(Classroom $classroom): void
    {
        // Ambil semua tugas/asesmen yang ditugaskan ke kelas ini
        $assessments = Assessment::whereHas('classrooms', fn($q) => $q->where('classrooms.id', $classroom->id))
            ->whereIn('type', ['tugas', 'formatif', 'sumatif'])
            ->orderBy('id')
            ->get();

        foreach ($assessments as $assessment) {
            $group = $assessment->grade_group ?: ($assessment->type === 'tugas' ? 'tugas' : 'uh');
            $seq = $this->resolveAssessmentSeq($assessment, $classroom->id, $group);

            if (!$seq) {
                continue;
            }

            $column = $this->getOrCreateGradeColumn($classroom, $group, $seq, $assessment);

            // Ambil semua submissions yang sudah memiliki score untuk asesmen ini
            $submissions = Submission::where('assessment_id', $assessment->id)
                ->whereNotNull('score')
                ->with('student')
                ->get()
                ->groupBy('student_id');

            $maxScore = $assessment->max_score > 0 ? (float) $assessment->max_score : 100.0;

            foreach ($submissions as $studentId => $studentSubs) {
                // Ambil submission terakhir
                $latestSub = $studentSubs->sortByDesc('attempt')->sortByDesc('id')->first();
                if (!$latestSub || $latestSub->score === null) {
                    continue;
                }

                $score = ($maxScore == 100.0) 
                    ? (float) $latestSub->score 
                    : round(((float) $latestSub->score / $maxScore) * 100.0, 2);

                GradeEntry::updateOrCreate(
                    [
                        'column_id' => $column->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'score' => $score,
                    ]
                );
            }
        }
    }

    /**
     * Cari urutan seq tugas untuk kelas tertentu
     */
    public function resolveAssessmentSeq(Assessment $assessment, int $classroomId, string $group = 'tugas'): int
    {
        if ($assessment->grade_seq && $assessment->grade_seq > 0) {
            return (int) $assessment->grade_seq;
        }

        // Jika grade_seq belum di-set eksplisit, hitung urutan otomatis berdasarkan daftar tugas kelas ini
        $allTasksInClass = Assessment::where('type', 'tugas')
            ->whereHas('classrooms', fn($q) => $q->where('classrooms.id', $classroomId))
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $index = array_search($assessment->id, $allTasksInClass);
        if ($index !== false) {
            return $index + 1;
        }

        return 1;
    }

    /**
     * Dapatkan atau buat GradeColumn sesuai grup dan seq
     */
    public function getOrCreateGradeColumn(Classroom $classroom, string $group, int $seq, ?Assessment $assessment = null): GradeColumn
    {
        $label = match ($group) {
            'tugas' => "Tugas {$seq}",
            'uh' => "UH {$seq}",
            'mid' => 'MID',
            'uas' => 'UAS',
            default => strtoupper($group) . " {$seq}",
        };

        $column = GradeColumn::firstOrCreate(
            [
                'classroom_id' => $classroom->id,
                'academic_year_id' => $classroom->academic_year_id,
                'group' => $group,
                'seq' => $seq,
            ],
            [
                'label' => $label,
                'max_score' => 100,
            ]
        );

        if ($assessment) {
            $tpIds = $assessment->getAllLearningObjectiveIds();
            if (!empty($tpIds)) {
                if (!$column->learning_objective_id) {
                    $column->learning_objective_id = $tpIds[0];
                    $column->save();
                }
                $column->syncLearningObjectives($tpIds);
            }
        }

        return $column;
    }

    /**
     * Sinkronisasi dua arah: jika nilai diubah langsung di spreadsheet Buku Nilai,
     * perbarui juga score di submission terkait jika ada.
     */
    public function syncGradeEntryToSubmission(int $columnId, int $studentId, ?float $score): void
    {
        $column = GradeColumn::find($columnId);
        if (!$column) {
            return;
        }

        $assessments = Assessment::whereHas('classrooms', fn($q) => $q->where('classrooms.id', $column->classroom_id))
            ->get();

        foreach ($assessments as $ass) {
            $seq = $this->resolveAssessmentSeq($ass, $column->classroom_id, $column->group);
            if (($ass->grade_group ?: 'tugas') === $column->group && $seq === $column->seq) {
                $sub = Submission::where('assessment_id', $ass->id)
                    ->where('student_id', $studentId)
                    ->latest('attempt')
                    ->first();

                if ($sub) {
                    $sub->update([
                        'score' => $score,
                        'status' => $score !== null ? Submission::STATUS_GRADED : $sub->status,
                    ]);
                }
                break;
            }
        }
    }
}
