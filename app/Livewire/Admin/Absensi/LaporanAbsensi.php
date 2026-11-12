<?php

namespace App\Livewire\Admin\Absensi;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Livewire\Attributes\Layout;
use App\Services\AttendanceService;

class LaporanAbsensi extends Component
{
    public $academicYearId;
    public $classroomId;

    #[Layout('components.admin.layout')]
    public function render(AttendanceService $service)
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $classrooms = [];
        $students = [];

        if (!$this->academicYearId && $academicYears->where('is_active', true)->first()) {
            $this->academicYearId = $academicYears->where('is_active', true)->first()->id;
        }

        if ($this->academicYearId) {
            $classrooms = Classroom::where('academic_year_id', $this->academicYearId)->get();
        }

        if ($this->classroomId) {
            $classroom = Classroom::find($this->classroomId);
            if ($classroom) {
                // Eager load with sessions to avoid N+1 if we want to display more details later
                $students = $classroom->students()->orderBy('name')->get()->map(function($student) use ($classroom, $service) {
                    $student->attendance_percentage = $service->getStudentAttendancePercentage($student->id, $classroom->id, $this->academicYearId);
                    return $student;
                });
            }
        }

        return view('livewire.admin.absensi.laporan-absensi', [
            'academicYears' => $academicYears,
            'classrooms' => $classrooms,
            'students' => $students,
        ]);
    }
}
