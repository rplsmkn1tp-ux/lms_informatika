<?php

namespace App\Livewire\Guru\Agenda;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterClassroom = '';
    public $dateStart = '';
    public $dateEnd = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterClassroom()
    {
        $this->resetPage();
    }

    public function updatedDateStart()
    {
        $this->resetPage();
    }

    public function updatedDateEnd()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterClassroom', 'dateStart', 'dateEnd']);
        $this->resetPage();
    }

    #[Layout('components.layouts.guru')]
    public function render()
    {
        $teacherId = Auth::id();

        $classrooms = Classroom::whereHas('attendanceSessions', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->get();

        $query = AttendanceSession::where('teacher_id', $teacherId)
            ->with(['classroom', 'records']);

        if ($this->filterClassroom) {
            $query->where('classroom_id', $this->filterClassroom);
        }

        if ($this->dateStart) {
            $query->whereDate('meeting_date', '>=', $this->dateStart);
        }

        if ($this->dateEnd) {
            $query->whereDate('meeting_date', '<=', $this->dateEnd);
        }

        if ($this->search) {
            $query->where('topic', 'like', '%' . $this->search . '%');
        }

        $agendas = $query->orderBy('meeting_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.guru.agenda.index', [
            'agendas' => $agendas,
            'classrooms' => $classrooms
        ]);
    }
}
