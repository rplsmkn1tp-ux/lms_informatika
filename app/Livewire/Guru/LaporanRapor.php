<?php

namespace App\Livewire\Guru;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeWeight;
use App\Traits\CalculatesGrades;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux;

#[Layout('components.layouts.guru')]
class LaporanRapor extends Component
{
    use CalculatesGrades;

    public $academicYears;
    public $selectedYear;
    public $classrooms = [];
    public $selectedClass;

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('name', 'desc')->get();
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
            $this->classrooms = Auth::user()->taughtClassrooms()
                ->where('academic_year_id', $this->selectedYear)
                ->get();
            if ($this->classrooms->isNotEmpty()) {
                $this->selectedClass = $this->classrooms->first()->id;
            }
        } else {
            $this->classrooms = collect();
        }
    }

    public function publishRapor()
    {
        if (!$this->selectedClass || !$this->selectedYear) return;

        $weight = GradeWeight::firstOrCreate([
            'classroom_id' => $this->selectedClass,
            'academic_year_id' => $this->selectedYear
        ], [
            'weight_uh' => 30,
            'weight_tugas' => 30,
            'weight_uas_mid' => 30,
            'weight_absensi' => 10,
        ]);

        $weight->update(['is_published' => true]);

        Flux::toast('Rapor berhasil diterbitkan ke siswa.', variant: 'success');
    }

    public function render()
    {
        $students = [];
        $reportData = [];
        $isPublished = false;

        if ($this->selectedClass && $this->selectedYear) {
            $classroom = Classroom::find($this->selectedClass);
            if ($classroom) {
                $students = $classroom->students()->orderBy('name')->get();
                $reportData = $this->calculateGradesForClassroom($this->selectedClass, $this->selectedYear, $students->pluck('id'));
                
                if (count($students) > 0 && isset($reportData[$students[0]->id])) {
                    $isPublished = $reportData[$students[0]->id]['is_published'];
                }
            }
        }

        return view('livewire.guru.laporan-rapor', [
            'students' => $students,
            'reportData' => $reportData,
            'isPublished' => $isPublished,
        ]);
    }
}
