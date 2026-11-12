<?php

namespace App\Livewire\Guru;

use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class ClassroomDetail extends Component
{
    public Classroom $classroom;

    public function mount(Classroom $classroom)
    {
        $this->classroom = $classroom;
        $this->checkAccess();
    }

    private function checkAccess()
    {
        $isTeaching = \App\Models\TeachingAssignment::where('teacher_id', Auth::id())
                        ->where('classroom_id', $this->classroom->id)
                        ->exists();

        if (!$isTeaching) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
    }

    public function regenerateJoinCode()
    {
        $this->checkAccess();
        $this->classroom->join_code = \App\Models\Classroom::generateJoinCode();
        $this->classroom->save();
        session()->flash('success', 'Kode kelas berhasil diperbarui.');
    }

    public function toggleJoinCodeActive()
    {
        $this->checkAccess();
        $this->classroom->join_code_active = !$this->classroom->join_code_active;
        $this->classroom->save();
        session()->flash('success', 'Status pendaftaran kelas berhasil diubah.');
    }

    public function render()
    {
        // Load jumlah siswa
        $this->classroom->loadCount('students');
        
        return view('livewire.guru.classroom-detail');
    }
}



