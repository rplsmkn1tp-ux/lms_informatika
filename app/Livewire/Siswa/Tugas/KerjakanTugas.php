<?php

namespace App\Livewire\Siswa\Tugas;

use App\Models\Assessment;
use App\Models\AssessmentGroup;
use App\Models\Question;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.siswa')]
class KerjakanTugas extends Component
{
    use WithFileUploads;

    public Assessment $assessment;
    public ?Submission $submission = null;

    // Respon siswa
    public $uploadedFile = null;
    public string $textContent = '';
    public string $linkUrl = '';

    // Group Assignment properties
    public ?AssessmentGroup $myGroup = null;
    public bool $isLeader = false;
    public string $newGroupName = '';
    public array $selectedMemberIds = [];
    public string $contributionNotes = '';
    public $availableStudents = [];
    public ?int $selectedNewMemberId = null;

    // Status & Policy
    public bool $isLocked = false;
    public bool $canResubmit = false;
    public bool $isPastDue = false;
    public string $lockReason = '';
    public ?Carbon $dueAtCarbon = null;

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment;

        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;

        $hasAccess = $assessment->classrooms()
            ->where('classrooms.id', $classroomId)
            ->where(function ($subq) {
                $subq->whereNull('assessment_classroom.opens_at')
                     ->orWhere('assessment_classroom.opens_at', '<=', now());
            })->exists();

        $hasExistingSubmission = Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', $user->id)
            ->exists();

        if ($assessment->status !== 'published' || (!$hasAccess && !$hasExistingSubmission)) {
            abort(403, 'Tugas belum tersedia untuk kelas Anda.');
        }

        $pivot = $assessment->classrooms()->where('classrooms.id', $classroomId)->first()?->pivot;
        $dueAt = $pivot?->due_at ?? $assessment->due_at;
        $this->dueAtCarbon = $dueAt ? Carbon::parse($dueAt)->setTimezone('Asia/Jakarta') : null;
        $now = Carbon::now('Asia/Jakarta');
        $this->isPastDue = $this->dueAtCarbon && $now->gt($this->dueAtCarbon);

        $this->loadGroupAndSubmissionData();
    }

    public function loadGroupAndSubmissionData()
    {
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;

        // Ambil submission terakhir siswa saat ini
        $this->submission = Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', $user->id)
            ->latest('attempt')
            ->first();

        if ($this->assessment->isGroup()) {
            $this->myGroup = AssessmentGroup::with(['members', 'leader', 'submissions'])
                ->where('assessment_id', $this->assessment->id)
                ->where('classroom_id', $classroomId)
                ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
                ->first();

            if ($this->myGroup) {
                $this->isLeader = ((int) $this->myGroup->leader_id === (int) $user->id);
                $this->contributionNotes = $this->myGroup->contribution_notes ?? '';

                // Jika belum ada submission di akun siswa ini tapi ketua sudah submit
                if (!$this->submission) {
                    $leaderSub = $this->myGroup->submissions()->latest('attempt')->first();
                    if ($leaderSub) {
                        $this->submission = Submission::create([
                            'assessment_id' => $this->assessment->id,
                            'assessment_group_id' => $this->myGroup->id,
                            'student_id' => $user->id,
                            'attempt' => $leaderSub->attempt,
                            'started_at' => $leaderSub->started_at,
                            'submitted_at' => $leaderSub->submitted_at,
                            'file_path' => $leaderSub->file_path,
                            'text_content' => $leaderSub->text_content,
                            'link_url' => $leaderSub->link_url,
                            'is_late' => $leaderSub->is_late,
                            'status' => $leaderSub->status,
                        ]);
                    }
                }
            } else {
                $this->isLeader = false;
                $this->loadAvailableStudents();
            }
        }

        if ($this->submission) {
            $this->textContent = $this->submission->text_content ?? '';
            $this->linkUrl = $this->submission->link_url ?? '';

            if ($this->submission->status === Submission::STATUS_REVISION) {
                $this->canResubmit = true;
                $this->isLocked = $this->assessment->isGroup() && !$this->isLeader;
                if ($this->isLocked) {
                    $this->lockReason = 'Guru meminta revisi tugas. Perbaikan berkas akan diunggah ulang oleh Ketua Kelompok (' . ($this->myGroup?->leader?->name ?? 'Ketua') . ').';
                }
            } elseif (in_array($this->submission->status, [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED])) {
                if ($this->submission->status === Submission::STATUS_GRADED) {
                    $this->isLocked = true;
                    $this->lockReason = 'Tugas ini sudah dinilai oleh guru.';
                } elseif ($this->assessment->allow_resubmit) {
                    $this->canResubmit = $this->assessment->isGroup() ? $this->isLeader : true;
                    $this->isLocked = !$this->canResubmit;
                    if ($this->isLocked && $this->assessment->isGroup()) {
                        $this->lockReason = 'Pengumpulan ulang diizinkan dan dilakukan oleh Ketua Kelompok (' . ($this->myGroup?->leader?->name ?? 'Ketua') . ').';
                    }
                } else {
                    $this->isLocked = true;
                    $this->lockReason = 'Tugas telah dikumpulkan dan pengumpulan ulang tidak diizinkan.';
                }
            }
        } else {
            // Belum pernah mengumpulkan
            if ($this->isPastDue && !$this->assessment->allow_late) {
                $this->isLocked = true;
                $this->lockReason = 'Batas waktu pengumpulan telah berakhir. Pengumpulan terlambat tidak diizinkan oleh guru.';
            } elseif ($this->assessment->isGroup()) {
                if (!$this->myGroup) {
                    $this->isLocked = true;
                    $this->lockReason = 'Tugas ini dikerjakan secara berkelompok. Silakan buat kelompok baru atau minta ketua kelompok untuk menambahkan nama Anda.';
                } elseif (!$this->isLeader) {
                    $this->isLocked = true;
                    $this->lockReason = 'Tugas ini dikerjakan secara berkelompok. Pengumpulan berkas jawaban akan dilakukan oleh Ketua Kelompok (' . ($this->myGroup->leader->name ?? 'Ketua') . ').';
                } else {
                    $this->isLocked = false;
                }
            }
        }
    }

    public function loadAvailableStudents()
    {
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        if (!$classroom) {
            $this->availableStudents = [];
            return;
        }

        $assignedStudentIds = AssessmentGroup::where('assessment_id', $this->assessment->id)
            ->where('classroom_id', $classroom->id)
            ->with('members')
            ->get()
            ->flatMap(fn($g) => $g->members->pluck('id'))
            ->unique()
            ->toArray();

        $this->availableStudents = $classroom->students()
            ->whereNotIn('users.id', array_merge($assignedStudentIds, [$user->id]))
            ->orderBy('name')
            ->get();
    }

    public function createGroup()
    {
        if (!$this->assessment->isGroup()) return;

        $user = auth()->user();
        $classroom = $user->activeClassroom();
        if (!$classroom) {
            session()->flash('error', 'Kelas aktif tidak ditemukan.');
            return;
        }

        $this->validate([
            'newGroupName' => 'required|string|min:2|max:50',
            'selectedMemberIds' => 'nullable|array',
            'selectedMemberIds.*' => 'exists:users,id',
        ], [
            'newGroupName.required' => 'Nama kelompok wajib diisi.',
            'newGroupName.min' => 'Nama kelompok minimal 2 karakter.',
        ]);

        $max = $this->assessment->max_group_members ?? 4;
        $totalMembers = 1 + count($this->selectedMemberIds);
        if ($totalMembers > $max) {
            session()->flash('error', "Jumlah anggota melebihi batas maksimal ({$max} siswa termasuk Anda).");
            return;
        }

        // Cek apakah ada yang sudah masuk kelompok lain
        $alreadyAssigned = AssessmentGroup::where('assessment_id', $this->assessment->id)
            ->where('classroom_id', $classroom->id)
            ->whereHas('members', fn($q) => $q->whereIn('users.id', array_merge([$user->id], $this->selectedMemberIds)))
            ->exists();

        if ($alreadyAssigned) {
            session()->flash('error', 'Anda atau salah satu anggota yang dipilih sudah terdaftar di kelompok lain.');
            return;
        }

        $group = AssessmentGroup::create([
            'assessment_id' => $this->assessment->id,
            'classroom_id' => $classroom->id,
            'name' => trim($this->newGroupName),
            'leader_id' => $user->id,
        ]);

        // Attach ketua dan anggota
        $allIds = array_unique(array_merge([$user->id], $this->selectedMemberIds));
        $group->members()->sync($allIds);

        $this->reset(['newGroupName', 'selectedMemberIds']);
        $this->loadGroupAndSubmissionData();

        session()->flash('success', "Kelompok '{$group->name}' berhasil dibentuk!");
    }

    public function addMemberToGroup($studentId)
    {
        if (!$this->myGroup || !$this->isLeader) return;
        if ($this->submission && $this->submission->status === Submission::STATUS_SUBMITTED && !$this->canResubmit) {
            session()->flash('error', 'Tidak dapat mengubah anggota setelah tugas dikumpulkan.');
            return;
        }

        $max = $this->assessment->max_group_members ?? 4;
        if ($this->myGroup->members()->count() >= $max) {
            session()->flash('error', "Kelompok sudah penuh (Maksimal {$max} anggota).");
            return;
        }

        $already = AssessmentGroup::where('assessment_id', $this->assessment->id)
            ->where('classroom_id', $this->myGroup->classroom_id)
            ->whereHas('members', fn($q) => $q->where('users.id', $studentId))
            ->exists();

        if ($already) {
            session()->flash('error', 'Siswa ini sudah bergabung di kelompok lain.');
            return;
        }

        $this->myGroup->members()->attach($studentId);
        $this->loadGroupAndSubmissionData();
        $this->loadAvailableStudents();
        session()->flash('success', 'Anggota berhasil ditambahkan ke kelompok.');
    }

    public function addSelectedMember()
    {
        if (!$this->selectedNewMemberId) {
            session()->flash('error', 'Pilih teman sekelas yang ingin ditambahkan.');
            return;
        }
        $this->addMemberToGroup($this->selectedNewMemberId);
        $this->selectedNewMemberId = null;
    }

    public function removeMemberFromGroup($studentId)
    {
        if (!$this->myGroup || !$this->isLeader) return;
        if ((int)$studentId === (int)$this->myGroup->leader_id) {
            session()->flash('error', 'Ketua kelompok tidak dapat dihapus.');
            return;
        }

        if ($this->submission && $this->submission->status === Submission::STATUS_SUBMITTED && !$this->canResubmit) {
            session()->flash('error', 'Tidak dapat mengubah anggota setelah tugas dikumpulkan.');
            return;
        }

        $this->myGroup->members()->detach($studentId);
        Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', $studentId)
            ->where('status', '!=', Submission::STATUS_GRADED)
            ->delete();

        $this->loadGroupAndSubmissionData();
        $this->loadAvailableStudents();
        session()->flash('success', 'Anggota berhasil dikeluarkan dari kelompok.');
    }

    public function leaveGroup()
    {
        if (!$this->myGroup || $this->isLeader) return;
        if ($this->submission && $this->submission->status === Submission::STATUS_SUBMITTED && !$this->canResubmit) {
            session()->flash('error', 'Tidak dapat keluar setelah tugas dikumpulkan.');
            return;
        }

        $this->myGroup->members()->detach(auth()->id());
        Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', auth()->id())
            ->where('status', '!=', Submission::STATUS_GRADED)
            ->delete();

        $this->loadGroupAndSubmissionData();
        $this->loadAvailableStudents();
        session()->flash('success', 'Anda telah keluar dari kelompok.');
    }

    public function disbandGroup()
    {
        if (!$this->myGroup || !$this->isLeader) return;
        if ($this->submission && $this->submission->status === Submission::STATUS_SUBMITTED && !$this->canResubmit) {
            session()->flash('error', 'Kelompok tidak dapat dibubarkan setelah tugas dikumpulkan.');
            return;
        }

        Submission::where('assessment_id', $this->assessment->id)
            ->where('assessment_group_id', $this->myGroup->id)
            ->where('status', '!=', Submission::STATUS_GRADED)
            ->delete();

        $this->myGroup->delete();
        $this->loadGroupAndSubmissionData();
        $this->loadAvailableStudents();
        session()->flash('success', 'Kelompok berhasil dibubarkan.');
    }

    public function submit()
    {
        if (RateLimiter::tooManyAttempts('submit-task:' . auth()->id(), 5)) {
            session()->flash('error', 'Terlalu banyak percobaan pengumpulan. Silakan tunggu beberapa saat.');
            return;
        }

        RateLimiter::hit('submit-task:' . auth()->id(), 60);

        if ($this->assessment->isGroup()) {
            if (!$this->myGroup || !$this->isLeader) {
                session()->flash('error', 'Hanya Ketua Kelompok yang berhak mengumpulkan tugas kelompok.');
                return;
            }
        }

        $now = Carbon::now('Asia/Jakarta');
        $isLate = $this->dueAtCarbon && $now->gt($this->dueAtCarbon);

        if ($isLate && !$this->assessment->allow_late) {
            session()->flash('error', 'Batas waktu pengumpulan telah berakhir. Pengumpulan terlambat tidak diizinkan oleh guru.');
            $this->isLocked = true;
            return;
        }

        if ($this->submission && in_array($this->submission->status, [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED]) && !$this->assessment->allow_resubmit && $this->submission->status !== Submission::STATUS_REVISION) {
            session()->flash('error', 'Tugas sudah dikumpulkan dan tidak mengizinkan pengumpulan ulang.');
            $this->isLocked = true;
            return;
        }

        $allowedTypes = $this->assessment->submission_types ?? ['file'];
        $rules = [];
        $messages = [];

        if (in_array('file', $allowedTypes)) {
            $rules['uploadedFile'] = 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,webp|max:20480';
            $messages['uploadedFile.max'] = 'Ukuran file maksimal 20 MB.';
            $messages['uploadedFile.mimes'] = 'Format file harus berupa PPT/PPTX, PDF, Word, Excel, Gambar, atau ZIP.';
        }

        if (in_array('text', $allowedTypes)) {
            $rules['textContent'] = 'nullable|string|max:10000';
        }

        if (in_array('link', $allowedTypes)) {
            $rules['linkUrl'] = 'nullable|url|max:500';
            $messages['linkUrl.url'] = 'Format tautan harus berupa URL yang valid (contoh: https://...).';
        }

        if ($this->assessment->isGroup()) {
            $rules['contributionNotes'] = 'nullable|string|max:2000';
        }

        $this->validate($rules, $messages);

        // Verifikasi bahwa setidaknya salah satu input pengumpulan diisi
        $hasExistingFile = $this->submission && !empty($this->submission->file_path);
        $hasFile = $this->uploadedFile !== null || $hasExistingFile;
        $hasText = !empty(trim($this->textContent));
        $hasLink = !empty(trim($this->linkUrl));

        if (!$hasFile && !$hasText && !$hasLink) {
            session()->flash('error', 'Harap isi setidaknya satu format jawaban yang diminta sebelum mengumpulkan.');
            return;
        }

        $filePath = $this->submission?->file_path;
        if ($this->uploadedFile) {
            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
            $origName = $this->uploadedFile->getClientOriginalName();
            $ext = $this->uploadedFile->getClientOriginalExtension() ?: pathinfo($origName, PATHINFO_EXTENSION);
            $cleanName = \Illuminate\Support\Str::slug(pathinfo($origName, PATHINFO_FILENAME), '_');
            $safeFileName = time() . '_' . ($cleanName ?: 'jawaban') . '.' . strtolower($ext);
            $filePath = $this->uploadedFile->storeAs('submissions', $safeFileName, 'local');
        }

        $attempt = $this->submission ? ($this->submission->attempt + ($this->canResubmit ? 1 : 0)) : 1;

        if ($this->submission) {
            $this->submission->update([
                'attempt' => $attempt,
                'file_path' => $filePath,
                'text_content' => $this->textContent,
                'link_url' => $this->linkUrl,
                'is_late' => $isLate,
                'status' => Submission::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);
        } else {
            $this->submission = Submission::create([
                'assessment_id' => $this->assessment->id,
                'assessment_group_id' => $this->myGroup?->id,
                'student_id' => auth()->id(),
                'attempt' => $attempt,
                'started_at' => now(),
                'submitted_at' => now(),
                'file_path' => $filePath,
                'text_content' => $this->textContent,
                'link_url' => $this->linkUrl,
                'is_late' => $isLate,
                'status' => Submission::STATUS_SUBMITTED,
            ]);
        }

        // Jika ini tugas kelompok, sinkronkan ke catatan kelompok dan seluruh anggota
        if ($this->assessment->isGroup() && $this->myGroup) {
            $this->myGroup->update([
                'contribution_notes' => $this->contributionNotes,
            ]);
            $this->submission->update(['assessment_group_id' => $this->myGroup->id]);

            foreach ($this->myGroup->members as $member) {
                if ((int)$member->id !== (int)auth()->id()) {
                    Submission::updateOrCreate(
                        [
                            'assessment_id' => $this->assessment->id,
                            'student_id' => $member->id,
                        ],
                        [
                            'assessment_group_id' => $this->myGroup->id,
                            'attempt' => $attempt,
                            'file_path' => $filePath,
                            'text_content' => $this->textContent,
                            'link_url' => $this->linkUrl,
                            'is_late' => $isLate,
                            'status' => Submission::STATUS_SUBMITTED,
                            'submitted_at' => now(),
                        ]
                    );
                }
            }
        }

        $this->canResubmit = (bool) $this->assessment->allow_resubmit;
        $this->isLocked = !$this->canResubmit;

        session()->flash('success', $this->assessment->isGroup() 
            ? 'Tugas kelompok berhasil dikumpulkan untuk seluruh anggota! Anda dapat memantau status penilaian dari guru di halaman ini.' 
            : 'Tugas berhasil dikumpulkan! Anda dapat memantau status penilaian dari guru di halaman ini.');
    }

    public function render()
    {
        return view('livewire.siswa.tugas.kerjakan-tugas');
    }
}
