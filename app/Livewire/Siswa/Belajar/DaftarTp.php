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

        // Get TPs for this element created by those teachers or templates, or having lessons assigned to this classroom
        $tps = LearningObjective::where('element_id', $this->element->id)
            ->where(function ($q) use ($teacherIds, $classroomId) {
                if (!empty($teacherIds)) {
                    $q->whereIn('created_by', $teacherIds)
                      ->orWhere('is_template', true);
                } else {
                    $q->where('is_template', true);
                }

                if ($classroomId) {
                    $q->orWhereHas('lessons', function ($lq) use ($classroomId) {
                        $lq->whereIn('status', ['published', 'scheduled'])
                           ->whereHas('classrooms', function ($cq) use ($classroomId) {
                               $cq->where('classrooms.id', $classroomId);
                           });
                    });
                }
            })
            ->orderBy('sort_order')
            ->with(['lessons' => function ($query) use ($classroomId) {
                $query->whereIn('status', ['published', 'scheduled'])
                    ->whereHas('classrooms', function ($q) use ($classroomId) {
                        $q->where('classrooms.id', $classroomId);
                    })
                    ->with(['classrooms' => function ($q) use ($classroomId) {
                        $q->where('classrooms.id', $classroomId);
                    }])
                    ->orderBy('sort_order');
            }])
            ->get();

        return view('livewire.siswa.belajar.daftar-tp', [
            'tps' => $tps,
        ])->layout('components.layouts.siswa');
    }
}
