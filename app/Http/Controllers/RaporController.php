<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\CalculatesGrades;
use Illuminate\Support\Facades\Auth;

class RaporController extends Controller
{
    use CalculatesGrades;

    public function cetakSiswa(Request $request, User $student, Classroom $classroom, AcademicYear $academicYear)
    {
        $results = $this->calculateGradesForClassroom($classroom->id, $academicYear->id, collect([$student->id]));
        $reportData = $results[$student->id] ?? null;

        if (!$reportData) {
            abort(404, 'Data rapor tidak ditemukan.');
        }

        $pdf = Pdf::loadView('pdf.rapor-siswa', [
            'student' => $student,
            'classroom' => $classroom,
            'academicYear' => $academicYear,
            'reportData' => $reportData
        ]);

        return $pdf->stream('Rapor_' . str_replace(' ', '_', $student->name) . '_' . $classroom->name . '.pdf');
    }

    public function cetakSiswaSendiri(Request $request, Classroom $classroom, AcademicYear $year)
    {
        $student = Auth::user();
        
        $results = $this->calculateGradesForClassroom($classroom->id, $year->id, collect([$student->id]));
        $reportData = $results[$student->id] ?? null;

        if (!$reportData || !($reportData['is_published'] ?? true)) {
            abort(403, 'Rapor belum diterbitkan atau data tidak ditemukan.');
        }

        $pdf = Pdf::loadView('pdf.rapor-siswa', [
            'student' => $student,
            'classroom' => $classroom,
            'academicYear' => $year,
            'reportData' => $reportData
        ]);

        return $pdf->stream('Rapor_' . str_replace(' ', '_', $student->name) . '_' . $classroom->name . '.pdf');
    }

    public function cetakKelas(Request $request, Classroom $classroom, AcademicYear $academicYear)
    {
        $students = $classroom->students()->orderBy('name')->get();
        $results = $this->calculateGradesForClassroom($classroom->id, $academicYear->id, $students->pluck('id'));

        $pdf = Pdf::loadView('pdf.rapor-kelas', [
            'classroom' => $classroom,
            'academicYear' => $academicYear,
            'students' => $students,
            'results' => $results
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rapor_Kelas_' . str_replace(' ', '_', $classroom->name) . '.pdf');
    }
}
