<?php

namespace App\Livewire\Siswa\Absensi;

use Livewire\Component;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use App\Services\AttendanceService;

class KehadiranSaya extends Component
{
    #[Layout('components.layouts.siswa')]
    public function render(AttendanceService $service)
    {
        $student = Auth::user();
        $classroom = $student->activeClassroom();

        $records = [];
        $percentage = 100;
        $summary = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];

        if ($classroom) {
            $records = AttendanceRecord::where('student_id', $student->id)
                ->whereHas('session', function($q) use ($classroom) {
                    $q->where('classroom_id', $classroom->id)
                      ->where('academic_year_id', $classroom->academic_year_id)
                      ->where('status', 'closed');
                })
                ->with('session')
                ->get()
                ->sortByDesc(fn($record) => $record->session->meeting_no);
                
            foreach ($records as $record) {
                $summary[$record->status]++;
            }

            $percentage = $service->getStudentAttendancePercentage($student->id, $classroom->id, $classroom->academic_year_id);
        }

        return view('livewire.siswa.absensi.kehadiran-saya', [
            'classroom' => $classroom,
            'records' => $records,
            'percentage' => $percentage,
            'summary' => $summary
        ]);
    }
}
