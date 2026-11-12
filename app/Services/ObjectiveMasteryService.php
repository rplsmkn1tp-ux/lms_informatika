<?php

namespace App\Services;

use App\Models\ObjectiveMastery;
use App\Models\Submission;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

class ObjectiveMasteryService
{
    /**
     * Hitung ulang skor terbaik siswa untuk sebuah TP, lalu update status ketercapaiannya.
     * Dipanggil setiap kali ada penilaian (tugas manual) atau kuis selesai.
     */
    public function updateMastery(int $studentId, int $learningObjectiveId, int $classroomId): ObjectiveMastery
    {
        return DB::transaction(function () use ($studentId, $learningObjectiveId, $classroomId) {
            $classroom = \App\Models\Classroom::find($classroomId);
            $academicYearId = $classroom?->academic_year_id ?? 1;

            $gradeService = app(\App\Services\SiswaGradeService::class);
            $allGrades = $gradeService->getStudentGrades($studentId, $classroomId, $academicYearId);

            $connected = $allGrades->filter(function ($item) use ($learningObjectiveId) {
                $ids = $item->learning_objective_ids ?? [];
                return in_array($learningObjectiveId, $ids);
            });

            $graded = $connected->filter(fn($item) => !empty($item->is_graded) && $item->score !== null && $item->normalized_score !== null);
            $count = $graded->count();

            $tp = \App\Models\LearningObjective::find($learningObjectiveId);
            $defaultKktp = (float) \App\Models\Setting::getValue('default_kktp', 75);
            $kktp = ($tp && $tp->kktp && (float) $tp->kktp > 0) ? (float) $tp->kktp : $defaultKktp;

            if ($count === 0) {
                // Fallback untuk submission langsung siswa jika asesmen belum terhubung ke relasi kelas
                $directSubs = Submission::where('student_id', $studentId)
                    ->whereHas('assessment', function ($query) use ($learningObjectiveId) {
                        $query->where('learning_objective_id', $learningObjectiveId)
                            ->orWhereHas('learningObjectives', fn($q2) => $q2->where('learning_objectives.id', $learningObjectiveId));
                    })
                    ->where('status', Submission::STATUS_GRADED)
                    ->whereNotNull('score')
                    ->get();

                if ($directSubs->isNotEmpty()) {
                    $bestScore = round((float) $directSubs->avg('score'), 1);
                    $count = $directSubs->count();
                } else {
                    $bestScore = null;
                }
            } else {
                $bestScore = round($graded->avg('normalized_score'), 1);
            }

            if ($count === 0 || $bestScore === null) {
                $status = 'belum';
                $bestScore = null;
            } else {
                if ($bestScore < $kktp) {
                    $status = 'remedial';
                } elseif ($bestScore >= 85) {
                    $status = 'pengayaan';
                } else {
                    $status = 'tercapai';
                }
            }

            // Update or create
            $mastery = ObjectiveMastery::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'learning_objective_id' => $learningObjectiveId,
                    'classroom_id' => $classroomId,
                ],
                [
                    'best_score' => $bestScore,
                    'status' => $status,
                ]
            );

            // Cek apakah semua TP dalam elemen ini sudah tercapai/pengayaan
            if ($tp) {
                $totalTps = \App\Models\LearningObjective::where('element_id', $tp->element_id)->count();
                $masteredTps = ObjectiveMastery::where('student_id', $studentId)
                    ->where('classroom_id', $classroomId)
                    ->whereHas('learningObjective', function($q) use ($tp) {
                        $q->where('element_id', $tp->element_id);
                    })
                    ->whereIn('status', ['tercapai', 'pengayaan'])
                    ->count();

                if ($totalTps > 0 && $masteredTps === $totalTps) {
                    // Cari sumatif untuk elemen ini
                    $sumatif = Assessment::where('element_id', $tp->element_id)
                        ->where('type', 'sumatif')
                        ->first();
                        
                    if ($sumatif) {
                        \App\Models\AssessmentAttemptGrant::updateOrCreate([
                            'student_id' => $studentId,
                            'assessment_id' => $sumatif->id,
                        ], [
                            'reason' => 'sumatif_terbuka'
                        ]);
                    }
                }
            }
            
            return $mastery;
        });
    }
}
