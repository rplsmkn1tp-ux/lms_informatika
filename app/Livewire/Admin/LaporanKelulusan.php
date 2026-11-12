<?php

namespace App\Livewire\Admin;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\ObjectiveMastery;
use Livewire\Component;

class LaporanKelulusan extends Component
{
    public $academicYears;
    public $selectedYear;

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('name', 'desc')->get();
        if ($this->academicYears->isNotEmpty()) {
            $this->selectedYear = $this->academicYears->first()->id;
        }
    }

    public function render()
    {
        $classrooms = collect();
        if ($this->selectedYear) {
            $classrooms = Classroom::withCount('students')
                ->where('academic_year_id', $this->selectedYear)
                ->get();
        }

        $elements = Element::withCount('learningObjectives')->get();
        $totalElements = $elements->count();
        $totalTps = $elements->sum('learning_objectives_count');

        $reportData = [];

        foreach ($classrooms as $class) {
            $studentsCount = $class->students_count;
            if ($studentsCount == 0) {
                continue;
            }

            // Get all masteries for this class
            $masteries = ObjectiveMastery::where('classroom_id', $class->id)->get();
            
            // A student is considered "Lulus" if they have mastered ALL TPs (tercapai or pengayaan)
            $studentMasteries = $masteries->groupBy('student_id');
            $lulusCount = 0;

            foreach ($studentMasteries as $studentId => $studentM) {
                $masteredTps = $studentM->filter(function($m) {
                    return in_array($m->status, ['tercapai', 'pengayaan']);
                })->count();

                if ($masteredTps == $totalTps && $totalTps > 0) {
                    $lulusCount++;
                }
            }

            $percentage = $studentsCount > 0 ? round(($lulusCount / $studentsCount) * 100, 2) : 0;

            $reportData[] = [
                'class' => $class,
                'students_count' => $studentsCount,
                'lulus_count' => $lulusCount,
                'percentage' => $percentage
            ];
        }

        return view('livewire.admin.laporan-kelulusan', [
            'reportData' => $reportData,
        ])->layout('components.admin.layout');
    }
}
