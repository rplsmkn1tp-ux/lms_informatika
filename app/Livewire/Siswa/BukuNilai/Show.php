<?php

namespace App\Livewire\Siswa\BukuNilai;

use App\Models\Classroom;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.siswa')]
class Show extends Component
{
    public $academicYears;
    public $selectedYear;
    public $classrooms = [];
    public $selectedClass;

    public function mount()
    {
        $this->academicYears = \App\Models\AcademicYear::orderBy('name', 'desc')->get();
        if ($this->academicYears->isNotEmpty()) {
            $this->selectedYear = $this->academicYears->first()->id;
            $this->loadClassrooms();
        }
    }

    public function updatedSelectedYear()
    {
        $this->loadClassrooms();
        $this->selectedClass = null;
    }

    public function loadClassrooms()
    {
        if ($this->selectedYear) {
            $this->classrooms = Auth::user()->classrooms()
                ->where('classrooms.academic_year_id', $this->selectedYear)
                ->get();
            if ($this->classrooms->isNotEmpty()) {
                $this->selectedClass = $this->classrooms->first()->id;
            }
        } else {
            $this->classrooms = collect();
        }
    }

    public function render(\App\Services\SiswaGradeService $gradeService)
    {
        $gradeItems = collect();
        $finalGrades = null;

        if ($this->selectedClass) {
            $classroom = Classroom::find($this->selectedClass);
            if ($classroom) {
                $gradeItems = $gradeService->getStudentGrades(Auth::id(), (int) $this->selectedClass, (int) $this->selectedYear);
                
                $gradeData = ['uh' => [], 'tugas' => [], 'mid' => [], 'uas' => []];
                foreach ($gradeItems as $item) {
                    $group = strtolower($item->grade_group);
                    if (isset($gradeData[$group])) {
                        $gradeData[$group][] = $item->score;
                    }
                }

                $service = new GradeCalculationService();
                $finalGrades = $service->calculateFinalGrade(
                    Auth::id(),
                    $classroom,
                    $classroom->academicYear,
                    $gradeData
                );
            }
        }

        return view('livewire.siswa.buku-nilai.show', [
            'gradeItems' => $gradeItems,
            'finalGrades' => $finalGrades,
        ]);
    }
}
