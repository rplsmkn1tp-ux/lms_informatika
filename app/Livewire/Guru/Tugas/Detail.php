<?php

namespace App\Livewire\Guru\Tugas;

use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\User;
use App\Services\ObjectiveMasteryService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class Detail extends Component
{
    use WithPagination;

    public Assessment $assessment;

    // Filters
    public ?int $selectedClassroomId = null;
    public string $statusFilter = '';
    public string $search = '';

    // Grading Modal
    public bool $showGradingModal = false;
    public ?int $selectedStudentId = null;
    public ?User $selectedStudent = null;
    public ?Submission $selectedSubmission = null;
    public $submissionHistory = [];
    public $score = null;
    public string $feedback = '';

    public function mount(Assessment $assessment)
    {
        $teacher = auth()->user();
        if ($assessment->created_by !== $teacher->id && $teacher->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $this->assessment = $assessment;
        // Default ke null agar langsung menampilkan 'Semua Kelas'
        $this->selectedClassroomId = null;
    }

    public function updatingSelectedClassroomId()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openGradingModal($studentId)
    {
        $student = User::findOrFail($studentId);
        $submissions = Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', $studentId)
            ->orderByDesc('attempt')
            ->orderByDesc('id')
            ->get();

        $submission = $submissions->first();

        $this->selectedStudentId = $studentId;
        $this->selectedStudent = $student;
        $this->selectedSubmission = $submission;
        $this->submissionHistory = $submissions;
        $this->score = $submission?->score;
        $this->feedback = $submission?->feedback ?? '';
        $this->showGradingModal = true;
    }

    public function closeGradingModal()
    {
        $this->showGradingModal = false;
        $this->reset(['selectedStudentId', 'selectedStudent', 'selectedSubmission', 'submissionHistory', 'score', 'feedback']);
    }

    public function saveGrade()
    {
        $maxScore = $this->assessment->max_score ?? 100;
        $this->validate([
            'score' => 'required|numeric|min:0|max:' . $maxScore,
            'feedback' => 'nullable|string',
        ], [
            'score.required' => 'Nilai harus diisi.',
            'score.numeric' => 'Nilai harus berupa angka.',
            'score.min' => 'Nilai minimal 0.',
            'score.max' => 'Nilai tidak boleh melebihi nilai maksimal (' . $maxScore . ').',
        ]);

        if (!$this->selectedSubmission) {
            // Jika guru memberi nilai langsung untuk siswa yang mengumpulkan secara luring/offline
            $this->selectedSubmission = Submission::create([
                'assessment_id' => $this->assessment->id,
                'student_id' => $this->selectedStudentId,
                'attempt' => 1,
                'started_at' => now(),
                'submitted_at' => now(),
                'status' => Submission::STATUS_GRADED,
                'score' => $this->score,
                'feedback' => $this->feedback,
                'graded_by' => auth()->id(),
            ]);
        } else {
            $this->selectedSubmission->update([
                'score' => $this->score,
                'feedback' => $this->feedback,
                'status' => Submission::STATUS_GRADED,
                'graded_by' => auth()->id(),
            ]);
        }

        // Sinkronisasi Ketercapaian TP
        $studentClassroomId = $this->selectedClassroomId 
            ?? $this->selectedStudent?->classrooms()->whereIn('classrooms.id', $this->assessment->classrooms->pluck('id'))->first()?->id
            ?? $this->selectedStudent?->activeClassroom()?->id;

        $tpIds = $this->assessment->getAllLearningObjectiveIds();
        if (!empty($tpIds) && $studentClassroomId) {
            foreach ($tpIds as $tpId) {
                try {
                    app(ObjectiveMasteryService::class)->updateMastery(
                        $this->selectedStudentId,
                        $tpId,
                        $studentClassroomId
                    );
                } catch (\Exception $e) {
                    // Jangan gagalkan penyimpanan nilai jika sync service mengalami kendala minor
                }
            }
        }

        // Sinkronisasi otomatis ke Buku Nilai
        try {
            app(\App\Services\GradeBookSyncService::class)->syncSubmission($this->selectedSubmission, $studentClassroomId);
        } catch (\Exception $e) {
            // Jangan gagalkan penyimpanan nilai jika sync service mengalami kendala minor
        }

        session()->flash('success', 'Nilai untuk ' . $this->selectedStudent->name . ' berhasil disimpan.');
        $this->closeGradingModal();
    }

    public function requestRevision()
    {
        if (!$this->selectedSubmission || !$this->selectedSubmission->submitted_at) {
            session()->flash('error', 'Siswa belum mengumpulkan tugas, permintaan revisi tidak dapat dikirim.');
            return;
        }

        $this->selectedSubmission->update([
            'status' => Submission::STATUS_REVISION,
            'feedback' => $this->feedback ?: 'Silakan perbaiki jawaban Anda dan kumpulkan kembali.',
            'graded_by' => auth()->id(),
        ]);

        session()->flash('success', 'Permintaan revisi berhasil dikirim ke ' . $this->selectedStudent->name . '.');
        $this->closeGradingModal();
    }

    public function render()
    {
        // Kumpulkan semua kelas penugasan + kelas dari siswa yang sudah mengumpulkan tugas ini
        $assignedClassroomIds = $this->assessment->classrooms->pluck('id');
        $submittingStudentClassroomIds = Submission::where('assessment_id', $this->assessment->id)
            ->with('student.classrooms')
            ->get()
            ->flatMap(fn($s) => $s->student?->classrooms->pluck('id') ?? [])
            ->unique();

        $allClassroomIds = $assignedClassroomIds->merge($submittingStudentClassroomIds)->unique()->filter()->values()->toArray();
        $availableClassrooms = Classroom::whereIn('id', $allClassroomIds)->orderBy('name')->get();

        $dueAt = $this->assessment->dueAtForClassroom($this->selectedClassroomId);
        $dueAtCarbon = $dueAt ? Carbon::parse($dueAt)->setTimezone('Asia/Jakarta') : null;

        // Ambil semua siswa:
        // Jika guru memilih kelas spesifik: filter kelas tsb ATAU siswa yang mengumpulkan di kelas tsb
        // Jika Semua Kelas (null): tampilkan seluruh siswa dari kelas-kelas terkait ATAU siapa saja yang sudah mengumpulkan
        $studentsQuery = User::where('role', 'siswa');

        if ($this->selectedClassroomId) {
            $selectedId = $this->selectedClassroomId;
            $studentsQuery->where(function ($q) use ($selectedId) {
                $q->whereHas('classrooms', function ($cq) use ($selectedId) {
                    $cq->where('classrooms.id', $selectedId);
                })->orWhereHas('submissions', function ($sq) use ($selectedId) {
                    $sq->where('assessment_id', $this->assessment->id)
                       ->whereHas('student.classrooms', function ($scq) use ($selectedId) {
                           $scq->where('classrooms.id', $selectedId);
                       });
                });
            });
        } else {
            $studentsQuery->where(function ($q) use ($allClassroomIds) {
                $q->whereHas('classrooms', function ($cq) use ($allClassroomIds) {
                    $cq->whereIn('classrooms.id', $allClassroomIds);
                })->orWhereHas('submissions', function ($sq) {
                    $sq->where('assessment_id', $this->assessment->id);
                });
            });
        }

        $allStudents = $studentsQuery
            ->with([
                'classrooms',
                'submissions' => function ($q) {
                    $q->where('assessment_id', $this->assessment->id)->orderByDesc('attempt')->orderByDesc('id');
                }
            ])
            ->orderBy('name')
            ->get();

        // Hitung statistik untuk tampilan yang dipilih
        $statTotal = $allStudents->count();
        $statSubmitted = 0;
        $statGraded = 0;
        $statLate = 0;
        $statRevision = 0;
        $totalScores = [];

        foreach ($allStudents as $st) {
            $sub = $st->submissions->first();
            if ($sub && in_array($sub->status, [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION])) {
                $statSubmitted++;
                $isLate = $sub->is_late || ($dueAtCarbon && $sub->submitted_at && Carbon::parse($sub->submitted_at)->setTimezone('Asia/Jakarta')->gt($dueAtCarbon));
                if ($isLate) {
                    $statLate++;
                }
                if ($sub->status === Submission::STATUS_GRADED) {
                    $statGraded++;
                    if ($sub->score !== null) {
                        $totalScores[] = (float) $sub->score;
                    }
                } elseif ($sub->status === Submission::STATUS_REVISION) {
                    $statRevision++;
                }
            }
        }

        $averageScore = count($totalScores) > 0 ? round(array_sum($totalScores) / count($totalScores), 1) : 0;

        // Filter berdasarkan pencarian nama atau NISN
        $filteredStudents = $allStudents;
        if (!empty(trim($this->search))) {
            $search = strtolower(trim($this->search));
            $filteredStudents = $filteredStudents->filter(function ($student) use ($search) {
                return str_contains(strtolower($student->name), $search)
                    || ($student->nisn && str_contains(strtolower($student->nisn), $search));
            });
        }

        // Filter berdasarkan status pengumpulan
        if (!empty($this->statusFilter)) {
            $filteredStudents = $filteredStudents->filter(function ($student) use ($dueAtCarbon) {
                $sub = $student->submissions->first();
                $statusType = 'belum';
                if ($sub && $sub->submitted_at) {
                    if ($sub->status === Submission::STATUS_GRADED) {
                        $statusType = 'dinilai';
                    } elseif ($sub->status === Submission::STATUS_REVISION) {
                        $statusType = 'revisi';
                    } elseif ($sub->status === Submission::STATUS_SUBMITTED) {
                        $isLate = $sub->is_late || ($dueAtCarbon && $sub->submitted_at && Carbon::parse($sub->submitted_at)->setTimezone('Asia/Jakarta')->gt($dueAtCarbon));
                        $statusType = $isLate ? 'terlambat' : 'dikumpulkan';
                    }
                }

                return $statusType === $this->statusFilter;
            });
        }

        // Paginasi manual dari Collection menggunakan $this->getPage()
        $perPage = 20;
        $totalItems = $filteredStudents->count();
        $currentPage = (int) ($this->getPage('page') ?: 1);
        $maxPage = max(1, (int) ceil($totalItems / $perPage));
        if ($currentPage > $maxPage) {
            $currentPage = 1;
            $this->setPage(1, 'page');
        }

        $items = $filteredStudents->forPage($currentPage, $perPage)->values();
        $paginatedStudents = new LengthAwarePaginator(
            $items,
            $totalItems,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        // Map submissions key by student_id untuk kemudahan akses di view
        $submissions = $allStudents->mapWithKeys(function ($st) {
            return [$st->id => $st->submissions->first()];
        });

        return view('livewire.guru.tugas.detail', [
            'assessment' => $this->assessment,
            'availableClassrooms' => $availableClassrooms,
            'students' => $paginatedStudents,
            'submissions' => $submissions,
            'dueAtCarbon' => $dueAtCarbon,
            'statTotal' => $statTotal,
            'statSubmitted' => $statSubmitted,
            'statGraded' => $statGraded,
            'statLate' => $statLate,
            'statRevision' => $statRevision,
            'averageScore' => $averageScore,
        ]);
    }
}
