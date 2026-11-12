<?php

namespace App\Traits;

use App\Models\GradeWeight;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Support\Collection;

trait CalculatesGrades
{
    /**
     * Menghitung nilai akhir untuk setiap siswa di sebuah kelas pada tahun ajaran tertentu.
     * Mengembalikan array dengan key = student_id, dan value = array data nilai.
     */
    public function calculateGradesForClassroom($classroomId, $academicYearId, Collection $studentIds)
    {
        $weight = GradeWeight::where('classroom_id', $classroomId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        // Default weights jika tidak ada
        $wUh = $weight ? $weight->weight_uh : 30;
        $wTugas = $weight ? $weight->weight_tugas : 30;
        $wMidUas = $weight ? $weight->weight_uas_mid : 30;
        $wAbsen = $weight ? $weight->weight_absensi : 10;
        $isPublished = $weight ? $weight->is_published : false;

        $columns = GradeColumn::where('classroom_id', $classroomId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $entries = GradeEntry::whereIn('column_id', $columns->pluck('id'))
            ->whereIn('student_id', $studentIds)
            ->get();

        // Absensi
        $sessions = AttendanceSession::where('classroom_id', $classroomId)
            ->where('academic_year_id', $academicYearId)
            ->get();
        
        $attendances = AttendanceRecord::whereIn('session_id', $sessions->pluck('id'))
            ->whereIn('student_id', $studentIds)
            ->get();

        $results = [];

        foreach ($studentIds as $studentId) {
            $studentEntries = $entries->where('student_id', $studentId);
            
            // Hitung rata-rata UH
            $uhColumns = $columns->where('group', 'uh');
            $uhScore = $this->calculateAverage($studentEntries, $uhColumns);
            
            // Hitung rata-rata Tugas
            $tugasColumns = $columns->where('group', 'tugas');
            $tugasScore = $this->calculateAverage($studentEntries, $tugasColumns);
            
            // Hitung rata-rata MID/UAS
            $midUasColumns = $columns->whereIn('group', ['mid', 'uas']);
            $midUasScore = $this->calculateAverage($studentEntries, $midUasColumns);
            
            // Hitung persentase Absensi (Hadir / Total Sesi * 100)
            $studentAtt = $attendances->where('student_id', $studentId);
            $attendanceCounts = [
                'Hadir' => $studentAtt->where('status', 'Hadir')->count() + $studentAtt->where('status', 'hadir')->count(),
                'Izin' => $studentAtt->where('status', 'Izin')->count() + $studentAtt->where('status', 'izin')->count(),
                'Sakit' => $studentAtt->where('status', 'Sakit')->count() + $studentAtt->where('status', 'sakit')->count(),
                'Alpa' => $studentAtt->where('status', 'Alpa')->count() + $studentAtt->where('status', 'alpa')->count(),
            ];
            
            $absenScore = 100;
            if ($sessions->count() > 0) {
                $absenScore = ($attendanceCounts['Hadir'] / $sessions->count()) * 100;
            }

            // Nilai Akhir
            $finalScore = ($uhScore * $wUh / 100) +
                          ($tugasScore * $wTugas / 100) +
                          ($midUasScore * $wMidUas / 100) +
                          ($absenScore * $wAbsen / 100);

            // Predikat
            $predicate = $this->getPredicate($finalScore);

            $results[$studentId] = [
                'avg_uh' => round($uhScore, 2),
                'avg_tugas' => round($tugasScore, 2),
                'avg_uas_mid' => round($midUasScore, 2),
                'attendance_score' => round($absenScore, 2),
                'attendance' => $attendanceCounts,
                'final_score' => round($finalScore, 2),
                'predicate' => $predicate,
                'is_published' => $isPublished,
            ];
        }

        return $results;
    }

    private function calculateAverage($entries, $columns)
    {
        if ($columns->isEmpty()) {
            return 0; // Atau 100 tergantung kebijakan, tapi 0 lebih aman
        }

        $totalScore = 0;
        $count = 0;

        foreach ($columns as $col) {
            $entry = $entries->where('column_id', $col->id)->first();
            $score = $entry ? $entry->score : 0; // Jika kosong anggap 0? Atau abaikan? Biasanya 0
            
            // Normalisasi score jika max_score bukan 100
            $max = $col->max_score > 0 ? $col->max_score : 100;
            $normalizedScore = ($score / $max) * 100;
            
            $totalScore += $normalizedScore;
            $count++;
        }

        return $count > 0 ? ($totalScore / $count) : 0;
    }

    private function getPredicate($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }
}
