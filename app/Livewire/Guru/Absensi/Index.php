<?php

namespace App\Livewire\Guru\Absensi;

use Livewire\Component;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class Index extends Component
{
    #[Layout('components.layouts.guru')]
    public function render()
    {
        $classrooms = Auth::user()->taughtClassrooms()->with('academicYear')->get();

        return view('livewire.guru.absensi.index', [
            'classrooms' => $classrooms
        ]);
    }
}
