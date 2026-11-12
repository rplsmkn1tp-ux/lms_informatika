<?php

namespace App\Livewire\Guru\Tugas;

use App\Models\Assessment;
use App\Models\AssessmentGroup;
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

    // Tabs
    public string $activeTab = 'siswa';

    // Filters
    public ?int $selectedClassroomId = null;
    public string $statusFilter = '';
    public string $search = '';

    // Grading Modal (Individual)
    public bool $showGradingModal = false;
    public ?int $selectedStudentId = null;
    public ?User $selectedStudent = null;
    public ?Submission $selectedSubmission = null;
    public $submissionHistory = [];
    public $score = null;
    public string $feedback = '';

    // Group Grading Modal
    public bool $showGroupGradingModal = false;
    public ?int $selectedGroupId = null;
    public ?AssessmentGroup $selectedGroup = null;
    public ?Submission $groupLeaderSubmission = null;
    public $groupDefaultScore = null;
    public array $memberScores = [];
    public array $memberFeedbacks = [];
    public string $groupFeedback = '';

    // Absent / Unassigned Student Assignment
    public ?int $selectedUnassignedStudentId = null;
    public ?int $targetGroupId = null;

    public function mount(Assessment $assessment)
    {
        $teacher = auth()->user();
        if ($assessment->created_by !== $teacher->id && $teacher->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $this->assessment = $assessment;
        $this->activeTab = $assessment->isGroup() ? 'kelompok' : 'siswa';
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

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openGroupGradingModal(int $groupId)
    {
        $group = AssessmentGroup::with(['members', 'leader', 'submissions' => function ($q) {
            $q->orderByDesc('attempt')->orderByDesc('id');
        }])->findOrFail($groupId);

        $this->selectedGroupId = $groupId;
        $this->selectedGroup = $group;

        $leaderSub = $group->submissions()->where('student_id', $group->leader_id)->latest('attempt')->first()
            ?? $group->submissions()->latest('attempt')->first();

        $this->groupLeaderSubmission = $leaderSub;

        $this->memberScores = [];
        $this->memberFeedbacks = [];
        $existingScores = [];

        foreach ($group->members as $member) {
            $memSub = $group->submissions()->where('student_id', $member->id)->latest('attempt')->first();
            $this->memberScores[$member->id] = $memSub?->score !== null ? (float)$memSub->score : ($leaderSub?->score !== null ? (float)$leaderSub->score : null);
            $this->memberFeedbacks[$member->id] = $memSub?->feedback ?? '';
            if ($this->memberScores[$member->id] !== null) {
                $existingScores[] = $this->memberScores[$member->id];
            }
        }

        $this->groupDefaultScore = count($existingScores) > 0 ? $existingScores[0] : null;
        $this->groupFeedback = $leaderSub?->feedback ?? '';
        $this->showGroupGradingModal = true;
    }

    public function updatedGroupDefaultScore($value)
    {
        if ($this->selectedGroup && is_numeric($value)) {
            foreach ($this->selectedGroup->members as $member) {
                $this->memberScores[$member->id] = (float)$value;
            }
        }
    }

    public function applyDefaultScoreToAll()
    {
        if ($this->groupDefaultScore !== null && $this->selectedGroup) {
            foreach ($this->selectedGroup->members as $member) {
                $this->memberScores[$member->id] = (float)$this->groupDefaultScore;
            }
        }
    }

    public function closeGroupGradingModal()
    {
        $this->showGroupGradingModal = false;
        $this->reset([
            'selectedGroupId',
            'selectedGroup',
            'groupLeaderSubmission',
            'groupDefaultScore',
            'memberScores',
            'memberFeedbacks',
            'groupFeedback',
        ]);
    }

    public function saveGroupGrade()
    {
        if (!$this->selectedGroup) return;

        $maxScore = $this->assessment->max_score ?? 100;
        
        $rules = [];
        $messages = [];
        foreach ($this->selectedGroup->members as $member) {
            $rules["memberScores.{$member->id}"] = "required|numeric|min:0|max:{$maxScore}";
            $messages["memberScores.{$member->id}.required"] = "Nilai untuk {$member->name} wajib diisi.";
            $messages["memberScores.{$member->id}.numeric"] = "Nilai untuk {$member->name} harus berupa angka.";
            $messages["memberScores.{$member->id}.min"] = "Nilai untuk {$member->name} minimal 0.";
            $messages["memberScores.{$member->id}.max"] = "Nilai untuk {$member->name} tidak boleh melebihi {$maxScore}.";
        }

        $this->validate($rules, $messages);

        $leaderSub = $this->groupLeaderSubmission;
        $attempt = $leaderSub ? $leaderSub->attempt : 1;
        $submittedAt = $leaderSub?->submitted_at ?? now();
        $filePath = $leaderSub?->file_path;
        $textContent = $leaderSub?->text_content;
        $linkUrl = $leaderSub?->link_url;
        $isLate = (bool) ($leaderSub?->is_late);

        $tpIds = $this->assessment->getAllLearningObjectiveIds();

        foreach ($this->selectedGroup->members as $member) {
            $mScore = $this->memberScores[$member->id];
            $mFeedback = trim($this->memberFeedbacks[$member->id] ?? '') ?: $this->groupFeedback;
            $memberClassroomId = $this->selectedGroup->classroom_id ?: ($member->classrooms()->first()?->id ?: $this->selectedClassroomId);

            $sub = Submission::updateOrCreate(
                [
                    'assessment_id' => $this->assessment->id,
                    'student_id' => $member->id,
                ],
                [
                    'assessment_group_id' => $this->selectedGroup->id,
                    'attempt' => $attempt,
                    'file_path' => $filePath,
                    'text_content' => $textContent,
                    'link_url' => $linkUrl,
                    'is_late' => $isLate,
                    'status' => Submission::STATUS_GRADED,
                    'score' => $mScore,
                    'feedback' => $mFeedback,
                    'graded_by' => auth()->id(),
                    'started_at' => $leaderSub?->started_at ?? now(),
                    'submitted_at' => $submittedAt,
                ]
            );

            if (!empty($tpIds) && $memberClassroomId) {
                foreach ($tpIds as $tpId) {
                    try {
                        app(ObjectiveMasteryService::class)->updateMastery($member->id, $tpId, $memberClassroomId);
                    } catch (\Exception $e) {}
                }
            }

            if ($memberClassroomId) {
                try {
                    app(\App\Services\GradeBookSyncService::class)->syncSubmission($sub, $memberClassroomId);
                } catch (\Exception $e) {}
            }
        }

        session()->flash('success', "Nilai untuk kelompok '{$this->selectedGroup->name}' berhasil disimpan.");
        $this->closeGroupGradingModal();
    }

    public function requestGroupRevision()
    {
        if (!$this->selectedGroup) return;

        foreach ($this->selectedGroup->members as $member) {
            $sub = Submission::where('assessment_id', $this->assessment->id)
                ->where('student_id', $member->id)
                ->latest('attempt')
                ->first();

            if ($sub) {
                $sub->update([
                    'status' => Submission::STATUS_REVISION,
                    'feedback' => $this->groupFeedback ?: 'Silakan perbaiki jawaban kelompok Anda dan kumpulkan kembali.',
                    'graded_by' => auth()->id(),
                ]);
            }
        }

        session()->flash('success', "Permintaan revisi berhasil dikirim ke kelompok '{$this->selectedGroup->name}'.");
        $this->closeGroupGradingModal();
    }

    public function assignStudentToGroup(?int $studentId = null, ?int $groupId = null)
    {
        $studentId = $studentId ?? $this->selectedUnassignedStudentId;
        $groupId = $groupId ?? $this->targetGroupId;

        if (!$studentId || !$groupId) {
            session()->flash('error', 'Pilih siswa dan kelompok tujuan terlebih dahulu.');
            return;
        }

        $group = AssessmentGroup::with('members')->findOrFail($groupId);
        $student = User::findOrFail($studentId);

        $max = $this->assessment->max_group_members ?? 4;
        if ($group->members()->count() >= $max) {
            session()->flash('error', "Kelompok '{$group->name}' sudah mencapai kapasitas maksimal ({$max} anggota).");
            return;
        }

        $otherGroups = AssessmentGroup::where('assessment_id', $this->assessment->id)
            ->whereHas('members', fn($q) => $q->where('users.id', $studentId))
            ->get();

        foreach ($otherGroups as $og) {
            $og->members()->detach($studentId);
        }

        $group->members()->syncWithoutDetaching([$studentId]);

        $leaderSub = $group->submissions()->latest('attempt')->first();
        if ($leaderSub) {
            Submission::updateOrCreate(
                [
                    'assessment_id' => $this->assessment->id,
                    'student_id' => $studentId,
                ],
                [
                    'assessment_group_id' => $group->id,
                    'attempt' => $leaderSub->attempt,
                    'file_path' => $leaderSub->file_path,
                    'text_content' => $leaderSub->text_content,
                    'link_url' => $leaderSub->link_url,
                    'is_late' => $leaderSub->is_late,
                    'status' => $leaderSub->status,
                    'score' => $leaderSub->score,
                    'feedback' => $leaderSub->feedback,
                    'graded_by' => $leaderSub->graded_by,
                    'started_at' => $leaderSub->started_at,
                    'submitted_at' => $leaderSub->submitted_at,
                ]
            );
        }

        $this->reset(['selectedUnassignedStudentId', 'targetGroupId']);
        session()->flash('success', "Siswa {$student->name} berhasil dimasukkan ke kelompok '{$group->name}'.");
    }

    public function removeStudentFromGroupTeacher(int $studentId, int $groupId)
    {
        $group = AssessmentGroup::findOrFail($groupId);
        $student = User::findOrFail($studentId);

        $group->members()->detach($studentId);

        Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', $studentId)
            ->where('status', '!=', Submission::STATUS_GRADED)
            ->delete();

        if ((int)$group->leader_id === (int)$studentId) {
            $newLeader = $group->members()->first();
            if ($newLeader) {
                $group->update(['leader_id' => $newLeader->id]);
            } else {
                $group->delete();
            }
        }

        if ($this->selectedGroup && $this->selectedGroup->id === $groupId) {
            $this->selectedGroup = AssessmentGroup::with(['members', 'leader', 'submissions'])->find($groupId);
            unset($this->memberScores[$studentId]);
            unset($this->memberFeedbacks[$studentId]);
            if (!$this->selectedGroup || $this->selectedGroup->members->isEmpty()) {
                $this->closeGroupGradingModal();
            }
        }

        session()->flash('success', "Siswa {$student->name} dikeluarkan dari kelompok.");
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

        // Data Kelompok (jika penugasan mode kelompok)
        $groups = collect();
        $unassignedStudents = collect();
        $statTotalGroups = 0;
        $statSubmittedGroups = 0;
        $statGradedGroups = 0;

        if ($this->assessment->isGroup()) {
            $groupsQuery = AssessmentGroup::where('assessment_id', $this->assessment->id)
                ->with([
                    'classroom',
                    'leader',
                    'members',
                    'submissions' => function ($q) {
                        $q->orderByDesc('attempt')->orderByDesc('id');
                    }
                ]);

            if ($this->selectedClassroomId) {
                $groupsQuery->where('classroom_id', $this->selectedClassroomId);
            }

            $groups = $groupsQuery->orderBy('name')->get();
            $statTotalGroups = $groups->count();

            foreach ($groups as $grp) {
                $sub = $grp->submissions->first();
                if ($sub && in_array($sub->status, [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED, Submission::STATUS_REVISION])) {
                    $statSubmittedGroups++;
                    if ($sub->status === Submission::STATUS_GRADED) {
                        $statGradedGroups++;
                    }
                }
            }

            $allAssignedIds = $groups->flatMap(fn($g) => $g->members->pluck('id'))->unique()->toArray();
            $unassignedStudents = $allStudents->filter(fn($st) => !in_array($st->id, $allAssignedIds))->values();
        }

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
            'groups' => $groups,
            'unassignedStudents' => $unassignedStudents,
            'statTotalGroups' => $statTotalGroups,
            'statSubmittedGroups' => $statSubmittedGroups,
            'statGradedGroups' => $statGradedGroups,
        ]);
    }
}
