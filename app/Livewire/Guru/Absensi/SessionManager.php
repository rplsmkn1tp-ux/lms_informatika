<?php

namespace App\Livewire\Guru\Absensi;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;

class SessionManager extends Component
{
    use AuthorizesRequests;

    public Classroom $classroom;

    #[Layout('components.layouts.guru')]
    public function render()
    {
        // Pastikan guru ngajar di kelas ini
        if (!Auth::user()->teachingAssignments()->where('classroom_id', $this->classroom->id)->exists()) {
            abort(403, 'Anda tidak berwenang mengelola absensi kelas ini.');
        }

        $sessions = AttendanceSession::where('classroom_id', $this->classroom->id)
            ->withCount('records')
            ->orderBy('meeting_no', 'desc')
            ->get();

        return view('livewire.guru.absensi.session-manager', [
            'sessions' => $sessions
        ]);
    }
}
