<?php

namespace App\Livewire\Siswa\Tugas;

use App\Models\Assessment;
use App\Models\Submission;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.siswa')]
class DaftarTugas extends Component
{
    public string $tab = 'semua'; // 'semua', 'perlu_dikerjakan', 'selesai', 'terlewat'
    public string $search = '';

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;
        $now = Carbon::now('Asia/Jakarta');

        $query = Assessment::where('status', 'published')
            ->where('type', 'tugas')
            ->where(function ($mainQ) use ($classroomId, $user) {
                $mainQ->whereHas('classrooms', function ($q) use ($classroomId) {
                    $q->where('classrooms.id', $classroomId)
                      ->where(function ($subq) {
                          $subq->whereNull('assessment_classroom.opens_at')
                               ->orWhere('assessment_classroom.opens_at', '<=', now());
                      });
                })->orWhereHas('submissions', function ($sq) use ($user) {
                    $sq->where('student_id', $user->id);
                });
            })
            ->with(['element', 'learningObjective', 'classrooms'])
            ->latest('id');

        if ($this->search) {
            $query->where('title', 'like', '%' . trim($this->search) . '%');
        }

        $allAssessments = $query->get();

        $submissions = Submission::where('student_id', $user->id)
            ->whereIn('assessment_id', $allAssessments->pluck('id'))
            ->get()
            ->groupBy('assessment_id')
            ->map(fn($group) => $group->sortByDesc('attempt')->first());

        // Process status & deadlines for each assessment
        $filteredAssessments = $allAssessments->map(function ($assessment) use ($submissions, $classroomId, $now) {
            $pivot = $assessment->classrooms->firstWhere('id', $classroomId)?->pivot;
            $dueAt = $pivot?->due_at ?? $assessment->due_at;
            $dueAtCarbon = $dueAt ? Carbon::parse($dueAt)->setTimezone('Asia/Jakarta') : null;
            $submission = $submissions->get($assessment->id);

            $isSubmitted = $submission && in_array($submission->status, [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION]);
            $isGraded = $submission && $submission->status === Submission::STATUS_GRADED;
            $isRevision = $submission && $submission->status === Submission::STATUS_REVISION;
            $isPastDue = $dueAtCarbon && $now->gt($dueAtCarbon);

            // Compute user-friendly status badge and remaining time
            if ($isGraded) {
                $statusType = 'selesai';
                $statusLabel = 'Dinilai (' . round($submission->score, 1) . '/' . $assessment->max_score . ')';
                $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } elseif ($isRevision) {
                $statusType = 'perlu_dikerjakan';
                $statusLabel = 'Perlu Revisi';
                $statusBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
            } elseif ($isSubmitted) {
                $statusType = 'selesai';
                $statusLabel = 'Menunggu Penilaian';
                $statusBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
            } elseif ($isPastDue) {
                $statusType = 'terlewat';
                $statusLabel = $assessment->allow_late ? 'Terlambat (Bisa Dikirim)' : 'Tenggat Berakhir';
                $statusBadgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
            } else {
                $statusType = 'perlu_dikerjakan';
                $statusLabel = 'Belum Dikerjakan';
                $statusBadgeClass = 'bg-zinc-100 text-zinc-700 border-zinc-200';
            }

            // Remaining time
            if ($isSubmitted) {
                $timeText = 'Dikirim ' . Carbon::parse($submission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
            } elseif ($dueAtCarbon) {
                if ($isPastDue) {
                    $timeText = 'Lewat tenggat ' . $dueAtCarbon->diffForHumans();
                } else {
                    $diffDays = $now->diffInDays($dueAtCarbon, false);
                    if ($diffDays == 0) {
                        $timeText = 'Tenggat Hari ini (' . $dueAtCarbon->format('H:i') . ' WIB)';
                    } else {
                        $timeText = 'Sisa ' . $dueAtCarbon->diffForHumans(null, true);
                    }
                }
            } else {
                $timeText = 'Tanpa batas waktu';
            }

            $assessment->userStatusType = $statusType;
            $assessment->userStatusLabel = $statusLabel;
            $assessment->userBadgeClass = $statusBadgeClass;
            $assessment->userTimeText = $timeText;
            $assessment->userSubmission = $submission;
            $assessment->dueAtCarbon = $dueAtCarbon;

            return $assessment;
        })->filter(function ($assessment) {
            if ($this->tab === 'perlu_dikerjakan') {
                return $assessment->userStatusType === 'perlu_dikerjakan';
            } elseif ($this->tab === 'selesai') {
                return $assessment->userStatusType === 'selesai';
            } elseif ($this->tab === 'terlewat') {
                return $assessment->userStatusType === 'terlewat';
            }
            return true;
        });

        return view('livewire.siswa.tugas.daftar-tugas', [
            'assessments' => $filteredAssessments,
            'classroom' => $classroom,
        ]);
    }
}
