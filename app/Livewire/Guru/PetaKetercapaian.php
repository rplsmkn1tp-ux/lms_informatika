<?php

namespace App\Livewire\Guru;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class PetaKetercapaian extends Component
{
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

    public function render()
    {
        $students = collect();
        $columns = collect();
        $matrix = [];
        
        if ($this->selectedClass && $this->selectedYear) {
            $classroom = Classroom::find($this->selectedClass);
            
            if ($classroom) {
                $students = $classroom->students()->orderBy('name')->get();
                
                // Ambil semua kolom UH sebagai representasi TP
                $columns = GradeColumn::where('classroom_id', $this->selectedClass)
                    ->where('academic_year_id', $this->selectedYear)
                    ->where('group', 'uh')
                    ->orderBy('seq')
                    ->get();
                    
                $entries = GradeEntry::whereIn('column_id', $columns->pluck('id'))
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get();
                    
                foreach ($students as $student) {
                    $studentRow = [
                        'student' => $student,
                        'scores' => []
                    ];
                    
                    foreach ($columns as $col) {
                        $entry = $entries->where('column_id', $col->id)->where('student_id', $student->id)->first();
                        $studentRow['scores'][$col->id] = $entry ? $entry->score : null;
                    }
                    
                    $matrix[] = $studentRow;
                }
            }
        }

        return view('livewire.guru.peta-ketercapaian', [
            'students' => $students,
            'columns' => $columns,
            'matrix' => $matrix,
        ]);
    }
}
