<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Lesson;

#[Layout('components.admin.layout')]
class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'siswa' => User::where('role', 'siswa')->count(),
            'guru' => User::where('role', 'guru')->count(),
            'kelas' => Classroom::count(),
            'materi' => Lesson::count(),
        ];

        $pendingVerifications = User::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'pendingVerifications' => $pendingVerifications
        ]);
    }
}
