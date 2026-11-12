<?php

namespace App\Livewire\Siswa;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\ObjectiveMastery;
use App\Models\Setting;
use App\Models\TeachingAssignment;
use App\Traits\CalculatesGrades;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.siswa')]
class Rapor extends Component
{
    use CalculatesGrades;

    public $academicYears;
    public $selectedYear;
    public $classrooms;
    public $selectedClass;
    public string $activeTab = 'semua'; // 'semua', 'tugas', 'ujian', 'tp', 'buku_nilai'

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Pilih tahun ajaran aktif, atau yang terbaru
        $activeYear = $this->academicYears->firstWhere('is_active', true) ?? $this->academicYears->first();
        if ($activeYear) {
            $this->selectedYear = $activeYear->id;
            $this->loadClassrooms();
        } else {
            $this->classrooms = collect();
        }
    }

    public function updatedSelectedYear()
    {
        $this->loadClassrooms();
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function loadClassrooms()
    {
        $student = Auth::user();
        if (!$student) {
            $this->classrooms = collect();
            $this->selectedClass = null;
            return;
        }

        if ($this->selectedYear) {
            // Ambil kelas siswa pada tahun ajaran ini
            $classes = $student->classrooms()
                ->where('classrooms.academic_year_id', $this->selectedYear)
                ->get();

            if ($classes->isEmpty()) {
                try {
                    $classes = $student->classrooms()->wherePivot('academic_year_id', $this->selectedYear)->get();
                } catch (\Throwable $e) {
                }
            }

            if ($classes->isEmpty()) {
                $classes = $student->classrooms()->get();
            }

            $this->classrooms = $classes;
            $this->selectedClass = $this->classrooms->first()?->id;
        } else {
            $this->classrooms = collect();
            $this->selectedClass = null;
        }
    }

    public function render()
    {
        $student = Auth::user();
        if (!$student || $student->role !== 'siswa') {
            abort(403, 'Akses terbatas untuk siswa.');
        }

        $reportData = null;
        $classroom = null;
        $assessmentItems = collect();
        $summary = [
            'total_assessments' => 0,
            'graded_count' => 0,
            'ungraded_count' => 0,
            'achieved_count' => 0,
            'unachieved_count' => 0,
            'average_score' => null,
            'attendance_percentage' => 0,
        ];
        $elementsWithTp = collect();
        $gradeColumns = collect();
        $gradeEntries = collect();
        $defaultKktp = (int) Setting::getValue('default_kktp', 75);

        if ($this->selectedClass && $this->selectedYear) {
            // Verifikasi kelas milik siswa (keamanan backend)
            $classroom = $student->classrooms()->where('classrooms.id', $this->selectedClass)->first();

            if ($classroom) {
                // 1. Data Rapor / Perhitungan Nilai Akhir
                try {
                    $results = $this->calculateGradesForClassroom($this->selectedClass, $this->selectedYear, collect([$student->id]));
                    $reportData = $results[$student->id] ?? null;
                    if ($reportData) {
                        $summary['attendance_percentage'] = $reportData['attendance_score'] ?? 0;
                    }
                } catch (\Throwable $e) {
                    $reportData = null;
                }

                // 2. Ambil seluruh data penilaian siswa melalui SiswaGradeService (satu sumber data)
                $gradeService = app(\App\Services\SiswaGradeService::class);
                $allGradeItems = $gradeService->getStudentGrades($student->id, $this->selectedClass, $this->selectedYear);

                // Filter tabs untuk tampilan daftar asesmen
                if ($this->activeTab === 'tugas') {
                    $displayedAssessments = $allGradeItems->where('filter_category', 'tugas');
                } elseif ($this->activeTab === 'ujian') {
                    $displayedAssessments = $allGradeItems->where('filter_category', 'ujian');
                } else {
                    $displayedAssessments = $allGradeItems;
                }

                // 3. Ringkasan Statistik (menggunakan data yang sama)
                $summary = $gradeService->getGradeSummary($allGradeItems, $reportData);

                // 4. Ketercapaian TP (Learning Objectives) dihitung dari satu service bersama
                $tpData = $gradeService->getStudentTpMasteries($student->id, $this->selectedClass, $this->selectedYear, $allGradeItems);
                $elementsWithTp = $tpData['elements'];
                $masteries = $tpData['masteries'];

                // 5. Data Kolom Buku Nilai (menggunakan sumber data yang sama dengan allGradeItems)
                $gradeColumns = GradeColumn::where('classroom_id', $this->selectedClass)
                    ->where('academic_year_id', $this->selectedYear)
                    ->orderBy('group')
                    ->orderBy('seq')
                    ->get();

                $gradeEntries = GradeEntry::whereIn('column_id', $gradeColumns->pluck('id'))
                    ->where('student_id', $student->id)
                    ->get()
                    ->keyBy('column_id');
            }
        }

        return view('livewire.siswa.rapor', [
            'student' => $student,
            'selectedYear' => $this->selectedYear,
            'selectedClass' => $this->selectedClass,
            'activeTab' => $this->activeTab,
            'academicYears' => $this->academicYears,
            'classrooms' => $this->classrooms,
            'classroom' => $classroom,
            'reportData' => $reportData,
            'assessments' => $displayedAssessments ?? $allGradeItems ?? collect(),
            'allAssessments' => $allGradeItems ?? collect(),
            'summary' => $summary,
            'elements' => $elementsWithTp,
            'masteries' => $masteries ?? collect(),
            'gradeColumns' => $gradeColumns,
            'gradeEntries' => $gradeEntries,
            'defaultKktp' => $defaultKktp,
        ])->layout('components.layouts.siswa');
    }
}
