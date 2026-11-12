<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherAccountApproved;
use App\Mail\TeacherAccountRejected;

use Livewire\Attributes\Layout;

#[Layout('components.admin.layout')]
class Verification extends Component
{
    use WithPagination;

    public $rejectionReason = '';
    public $rejectingUserId = null;

    public function approve($userId)
    {
        $user = User::findOrFail($userId);
        $user->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        Mail::to($user->email)->send(new TeacherAccountApproved($user));

        session()->flash('success', 'Akun guru berhasil disetujui.');
    }

    public function confirmReject($userId)
    {
        $this->rejectingUserId = $userId;
        $this->rejectionReason = '';
    }

    public function reject($userId = null)
    {
        $id = $userId ?? $this->rejectingUserId;
        $user = User::findOrFail($id);

        $this->validate([
            'rejectionReason' => 'required|string|min:5',
        ]);

        $user->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
        ]);

        Mail::to($user->email)->send(new TeacherAccountRejected($user, $this->rejectionReason));

        $this->rejectingUserId = null;
        $this->rejectionReason = '';

        session()->flash('success', 'Akun guru berhasil ditolak.');
    }

    public function render()
    {
        $pendingTeachers = User::where('role', 'guru')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        $verifiedTeachers = User::where('role', 'guru')
            ->whereIn('status', ['active', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.admin.verification', [
            'teachers' => $pendingTeachers,
            'verifiedTeachers' => $verifiedTeachers,
            'pendingCount' => $pendingTeachers->total(),
        ]);
    }
}
