<?php

namespace App\Livewire\Guru\BukuNilai;

use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class Index extends Component
{
    public $academicYears;
    public $selectedYear;
    public $classrooms = [];

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
    }

    public function loadClassrooms()
    {
        if ($this->selectedYear) {
            $this->classrooms = Auth::user()->taughtClassrooms()
                ->where('academic_year_id', $this->selectedYear)
                ->get();
        } else {
            $this->classrooms = collect();
        }
    }

    public function render()
    {
        return view('livewire.guru.buku-nilai.index');
    }
}
