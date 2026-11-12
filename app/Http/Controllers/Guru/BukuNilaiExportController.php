<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\GradeColumn;
use App\Models\GradeWeight;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuNilaiExportController extends Controller
{
    use AuthorizesRequests;

    public function pdf(Classroom $classroom, AcademicYear $academicYear, GradeCalculationService $gradeCalculationService)
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $classroom]);

        $columns = GradeColumn::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('seq')
            ->get();

        $students = $classroom->students()->orderBy('name')->get();

        $gradeData = [];
        foreach ($students as $student) {
            foreach ($columns as $col) {
                $entry = $col->entries()->where('student_id', $student->id)->first();
                $gradeData[$student->id][$col->id] = $entry ? $entry->score : null;
            }
        }

        $weights = GradeWeight::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $calculations = [];
        foreach ($students as $student) {
            $structuredData = [
                'uh' => [], 'tugas' => [], 'mid' => [], 'uas' => []
            ];
            foreach ($columns as $col) {
                if (isset($gradeData[$student->id][$col->id])) {
                    $structuredData[$col->group][] = $gradeData[$student->id][$col->id];
                }
            }
            $calculations[$student->id] = $gradeCalculationService->calculateFinalGrade(
                $student->id, $classroom, $academicYear, $structuredData
            );
        }

        $pdf = Pdf::loadView('exports.buku-nilai', compact('classroom', 'academicYear', 'columns', 'students', 'gradeData', 'weights', 'calculations'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("Buku_Nilai_{$classroom->name}_{$academicYear->semester}_{$academicYear->year}.pdf");
    }

    public function excel(Classroom $classroom, AcademicYear $academicYear, GradeCalculationService $gradeCalculationService)
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $classroom]);

        $columns = GradeColumn::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('seq')
            ->get();

        $students = $classroom->students()->orderBy('name')->get();

        $gradeData = [];
        foreach ($students as $student) {
            foreach ($columns as $col) {
                $entry = $col->entries()->where('student_id', $student->id)->first();
                $gradeData[$student->id][$col->id] = $entry ? $entry->score : null;
            }
        }

        $weights = GradeWeight::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $calculations = [];
        foreach ($students as $student) {
            $structuredData = [
                'uh' => [], 'tugas' => [], 'mid' => [], 'uas' => []
            ];
            foreach ($columns as $col) {
                if (isset($gradeData[$student->id][$col->id])) {
                    $structuredData[$col->group][] = $gradeData[$student->id][$col->id];
                }
            }
            $calculations[$student->id] = $gradeCalculationService->calculateFinalGrade(
                $student->id, $classroom, $academicYear, $structuredData
            );
        }

        $filename = "Buku_Nilai_{$classroom->name}_{$academicYear->semester}_{$academicYear->year}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students, $columns, $gradeData, $weights, $calculations) {
            $file = fopen('php://output', 'w');
            
            // Header 1
            $header1 = ['NIS', 'Nama Siswa'];
            foreach ($columns as $col) {
                $header1[] = $col->label;
            }
            $header1[] = 'Avg UH (' . ($weights->weight_uh ?? 30) . '%)';
            $header1[] = 'Avg Tugas (' . ($weights->weight_tugas ?? 30) . '%)';
            $header1[] = 'Avg UAS/MID (' . ($weights->weight_uas_mid ?? 30) . '%)';
            $header1[] = 'Nilai Akhir';
            $header1[] = 'Kehadiran (' . ($weights->weight_absensi ?? 10) . '%)';

            fputcsv($file, $header1);

            foreach ($students as $student) {
                $row = [
                    $student->nis ?? '-',
                    $student->name,
                ];

                foreach ($columns as $col) {
                    $row[] = $gradeData[$student->id][$col->id] ?? '';
                }

                $row[] = $calculations[$student->id]['avg_uh'] ?? '';
                $row[] = $calculations[$student->id]['avg_tugas'] ?? '';
                $row[] = $calculations[$student->id]['avg_mid_uas'] ?? '';
                $row[] = $calculations[$student->id]['final_score'] ?? '';
                $row[] = ($calculations[$student->id]['attendance_percentage'] ?? 0) . '%';

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
