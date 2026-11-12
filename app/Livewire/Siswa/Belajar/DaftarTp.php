<?php

namespace App\Livewire\Siswa\Belajar;

use App\Models\Element;
use App\Models\LearningObjective;
use Livewire\Component;

class DaftarTp extends Component
{
    public Element $element;

    public function mount(Element $element)
    {
        $this->element = $element;
    }

    public function render()
    {
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;

        // Get teacher IDs assigned to this classroom
        $teacherIds = \App\Models\TeachingAssignment::where('classroom_id', $classroomId)
            ->pluck('teacher_id')
            ->toArray();

        // Get TPs for this element created by those teachers
        $tps = LearningObjective::where('element_id', $this->element->id)
            ->whereIn('created_by', $teacherIds)
            ->orderBy('sort_order')
            ->with(['lessons' => function ($query) use ($classroomId) {
                $query->where('status', 'published')
                    ->with(['classrooms' => function ($q) use ($classroomId) {
                        $q->where('classrooms.id', $classroomId);
                    }])
                    ->whereHas('classrooms', function ($q) use ($classroomId) {
                        $q->where('classrooms.id', $classroomId);
                    })
                    ->orderBy('sort_order');
            }])
            ->get();

        return view('livewire.siswa.belajar.daftar-tp', [
            'tps' => $tps,
        ])->layout('components.layouts.siswa');
    }
}
