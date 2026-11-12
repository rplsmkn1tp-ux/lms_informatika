<?php

namespace App\Livewire\Siswa\Nilai;

use App\Models\Element;
use App\Models\ObjectiveMastery;
use Livewire\Component;

class DaftarNilai extends Component
{
    public function render()
    {
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;
        $academicYearId = $classroom?->academic_year_id ?? 1;

        $gradeService = app(\App\Services\SiswaGradeService::class);
        $tpData = $gradeService->getStudentTpMasteries($user->id, $classroomId, $academicYearId);

        $defaultKktp = \App\Models\Setting::getValue('default_kktp', 75);

        return view('livewire.siswa.nilai.daftar-nilai', [
            'elements' => $tpData['elements'],
            'masteries' => $tpData['masteries'],
            'defaultKktp' => $defaultKktp,
        ])->layout('components.layouts.siswa');
    }
}
