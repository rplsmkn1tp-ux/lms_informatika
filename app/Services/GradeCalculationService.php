<?php

namespace App\Services;

class GradeCalculationService
{
    /**
     * Menghitung nilai rapor untuk satu siswa di kelas tertentu.
     * Mengembalikan array yang berisi rata-rata per grup, rekap absensi, dan nilai akhir.
     */
    public function calculateFinalGrade(int $studentId, \App\Models\Classroom $classroom, \App\Models\AcademicYear $academicYear, array $gradeData = []): array
    {
        // $gradeData is an array structured like:
        // ['uh' => [80, 90], 'tugas' => [75, null, 85], 'mid' => [80], 'uas' => [85]]
        
        $avgUh = $this->calculateAverage($gradeData['uh'] ?? []);
        $avgTugas = $this->calculateAverage($gradeData['tugas'] ?? []);
        
        // Gabungkan nilai MID dan UAS, lalu cari rata-ratanya
        $midUasScores = array_merge($gradeData['mid'] ?? [], $gradeData['uas'] ?? []);
        $avgMidUas = $this->calculateAverage($midUasScores);

        // Get weights
        $weights = \App\Models\GradeWeight::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $wUh = $weights ? $weights->weight_uh : 30;
        $wTugas = $weights ? $weights->weight_tugas : 30;
        $wUasMid = $weights ? $weights->weight_uas_mid : 30;
        $wAbsensi = $weights ? $weights->weight_absensi : 10;

        // Get attendance data
        $attendanceService = app(AttendanceService::class);
        $attendance = $attendanceService->getStudentAttendancePercentage($studentId, $classroom->id, $academicYear->id);

        $finalScore = null;

        // Jika salah satu dari (UH, Tugas, UAS/MID, Absensi) ada
        if ($avgUh !== null || $avgTugas !== null || $avgMidUas !== null || $attendance > 0) {
            $totalWeight = 0;
            $weightedSum = 0;

            if ($avgUh !== null) {
                $weightedSum += $avgUh * $wUh;
                $totalWeight += $wUh;
            }
            if ($avgTugas !== null) {
                $weightedSum += $avgTugas * $wTugas;
                $totalWeight += $wTugas;
            }
            if ($avgMidUas !== null) {
                $weightedSum += $avgMidUas * $wUasMid;
                $totalWeight += $wUasMid;
            }
            // For attendance, we consider it present if calculated. We use $attendance (percentage).
            if ($attendance !== null && $wAbsensi > 0) {
                $weightedSum += $attendance * $wAbsensi;
                $totalWeight += $wAbsensi;
            }

            if ($totalWeight > 0) {
                $finalScoreRaw = ($weightedSum / $totalWeight);
                $finalScore = round($finalScoreRaw, 0, PHP_ROUND_HALF_UP);
            }
        }

        return [
            'avg_uh' => $avgUh !== null ? round($avgUh, 2) : null,
            'avg_tugas' => $avgTugas !== null ? round($avgTugas, 2) : null,
            'avg_mid_uas' => $avgMidUas !== null ? round($avgMidUas, 2) : null,
            'final_score' => $finalScore,
            'attendance_percentage' => $attendance,
        ];
    }

    /**
     * Menghitung rata-rata dari sekumpulan nilai, mengabaikan null.
     */
    protected function calculateAverage(array $scores): ?float
    {
        $validScores = array_filter($scores, function($score) {
            return $score !== null;
        });

        if (count($validScores) === 0) {
            return null;
        }

        return array_sum($validScores) / count($validScores);
    }
}
