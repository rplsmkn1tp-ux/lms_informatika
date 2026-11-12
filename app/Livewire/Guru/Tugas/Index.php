<?php

namespace App\Livewire\Guru\Tugas;

use App\Models\Assessment;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $classFilter = '';
    public $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClassFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function duplicate($id)
    {
        $assessment = Assessment::with('classrooms')->findOrFail($id);

        if ($assessment->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $newAssessment = Assessment::create([
            'learning_objective_id' => $assessment->learning_objective_id,
            'element_id' => $assessment->element_id,
            'lesson_id' => $assessment->lesson_id,
            'attendance_session_id' => $assessment->attendance_session_id,
            'type' => 'tugas',
            'title' => '[Salinan] ' . $assessment->title,
            'instructions' => $assessment->instructions,
            'teacher_attachments' => $assessment->teacher_attachments,
            'submission_types' => $assessment->submission_types,
            'allow_late' => $assessment->allow_late,
            'allow_resubmit' => $assessment->allow_resubmit,
            'opens_at' => $assessment->opens_at,
            'due_at' => $assessment->due_at,
            'kktp' => $assessment->kktp,
            'max_score' => $assessment->max_score,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Duplikat pivot kelas
        foreach ($assessment->classrooms as $classroom) {
            $newAssessment->classrooms()->attach($classroom->id, [
                'opens_at' => $classroom->pivot->opens_at,
                'due_at' => $classroom->pivot->due_at,
            ]);
        }

        session()->flash('success', 'Tugas berhasil diduplikasi menjadi draft.');
    }

    public function delete($id)
    {
        $assessment = Assessment::findOrFail($id);

        if ($assessment->created_by !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        // Cek apakah sudah ada pengumpulan dari siswa
        $hasSubmissions = $assessment->submissions()
            ->whereIn('status', [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION])
            ->exists();

        if ($hasSubmissions) {
            session()->flash('error', 'Tugas tidak dapat dihapus karena sudah ada siswa yang mengumpulkan jawaban.');
            return;
        }

        // Hapus submission yang masih 'dikerjakan' jika ada
        $assessment->submissions()->delete();
        $assessment->classrooms()->detach();
        $assessment->delete();

        session()->flash('success', 'Tugas berhasil dihapus.');
    }

    public function render()
    {
        $teacher = auth()->user();
        $classrooms = $teacher->taughtClassrooms()->get();

        $query = Assessment::where('created_by', $teacher->id)
            ->where('type', 'tugas')
            ->with(['classrooms', 'submissions'])
            ->latest('id');

        if ($this->search) {
            $query->where('title', 'like', '%' . trim($this->search) . '%');
        }

        if ($this->classFilter) {
            $query->whereHas('classrooms', function ($q) {
                $q->where('classrooms.id', $this->classFilter);
            });
        }

        $now = Carbon::now('Asia/Jakarta');

        if ($this->statusFilter === 'draft') {
            $query->where('status', 'draft');
        } elseif ($this->statusFilter === 'aktif') {
            $query->where('status', 'published')
                ->where(function ($q) use ($now) {
                    $q->whereNull('due_at')
                      ->orWhere('due_at', '>=', $now);
                });
        } elseif ($this->statusFilter === 'lewat') {
            $query->where('status', 'published')
                ->whereNotNull('due_at')
                ->where('due_at', '<', $now);
        }

        $tugasList = $query->paginate(10);

        // Pre-calculate statistics for each task
        $tugasData = $tugasList->through(function ($tugas) use ($now) {
            $classroomIds = $tugas->classrooms->pluck('id')->toArray();
            
            // Total unique students in assigned classrooms or who submitted
            $totalStudents = User::where('role', 'siswa')
                ->where(function ($q) use ($classroomIds, $tugas) {
                    $q->whereHas('classrooms', function ($cq) use ($classroomIds) {
                        $cq->whereIn('classrooms.id', $classroomIds);
                    })->orWhereHas('submissions', function ($sq) use ($tugas) {
                        $sq->where('assessment_id', $tugas->id);
                    });
                })->count();

            // Total submitted students
            $submittedCount = $tugas->submissions
                ->whereIn('status', [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION])
                ->pluck('student_id')
                ->unique()
                ->count();

            // Graded count
            $gradedCount = $tugas->submissions
                ->where('status', Submission::STATUS_GRADED)
                ->pluck('student_id')
                ->unique()
                ->count();

            // Status label
            if ($tugas->status === 'draft') {
                $statusLabel = 'Draft';
                $statusBadge = 'bg-zinc-100 text-zinc-700 border-zinc-200';
            } elseif ($tugas->due_at && Carbon::parse($tugas->due_at)->setTimezone('Asia/Jakarta')->lt($now)) {
                $statusLabel = 'Lewat Tenggat';
                $statusBadge = 'bg-rose-50 text-rose-700 border-rose-200';
            } else {
                $statusLabel = 'Aktif';
                $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }

            $tugas->totalStudents = $totalStudents;
            $tugas->submittedCount = $submittedCount;
            $tugas->gradedCount = $gradedCount;
            $tugas->statusLabel = $statusLabel;
            $tugas->statusBadge = $statusBadge;
            $tugas->progressPercentage = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100) : 0;

            return $tugas;
        });

        return view('livewire.guru.tugas.index', [
            'tugasList' => $tugasData,
            'classrooms' => $classrooms,
        ]);
    }
}
