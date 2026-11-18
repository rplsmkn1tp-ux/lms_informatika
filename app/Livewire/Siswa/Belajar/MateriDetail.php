<?php

namespace App\Livewire\Siswa\Belajar;

use App\Models\Assessment;
use App\Models\Submission;
use App\Models\Discussion;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonProgress;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MateriDetail extends Component
{
    public ?Element $element = null;
    public ?LearningObjective $tp = null;
    public ?Lesson $lesson = null;

    public bool $isPreviewMode = false;
    public $tab = 'materi'; // materi, diskusi, catatan

    public string $discussionBody = '';
    public string $noteBody = '';

    public ?int $selectedClassroomId = null;
    public ?int $replyingToId = null;
    public string $replyBody = '';

    public bool $isNotFound = false;
    public bool $isLocked = false;
    public string $errorMessage = '';

    public ?int $activePdfId = null;

    // Gated Learning & Checkpoint Quiz State
    public array $checkpointAnswers = [];
    public bool $checkpointPassed = false;
    public ?int $checkpointScore = null;
    public bool $checkpointSubmitted = false;
    public array $checkpointResults = [];

    public function mount($element = null, $tp = null, $lesson = null)
    {
        // Handle flexible route parameters:
        // Case 1: Route guru.lessons.preview -> /guru/materi/{lesson}/pratinjau
        // In this case, Laravel might pass $lesson directly or as first parameter
        if ($lesson === null && $tp === null && $element !== null) {
            $lesson = $element;
            $element = null;
        }

        if ($lesson instanceof Lesson) {
            $this->lesson = $lesson;
        } elseif (is_numeric($lesson)) {
            $this->lesson = Lesson::with(['attachments', 'classrooms', 'learningObjective.element', 'assessments'])->find($lesson);
        } elseif (is_string($lesson)) {
            if ($tp instanceof LearningObjective) {
                $this->lesson = $tp->lessons()
                    ->where(fn($q) => $q->where('code', $lesson)->orWhere('id', $lesson))
                    ->first();
            } else {
                $this->lesson = Lesson::where('code', $lesson)->orWhere('id', $lesson)->first();
            }
        }

        if (!$this->lesson) {
            abort(404, 'Materi tidak ditemukan atau sudah dihapus.');
        }

        $this->tp = $this->lesson->learningObjective;
        $this->element = $this->tp?->element;

        $user = Auth::user();

        // 1. Otorisasi untuk GURU / ADMIN (Preview Mode)
        if ($user && in_array($user->role, ['guru', 'admin'])) {
            if ($user->role === 'guru' && $this->lesson->created_by !== $user->id) {
                // Cek apakah guru mengajar kelas yang ditugaskan materi ini
                $taughtClassIds = $user->taughtClassrooms->pluck('id')->toArray();
                $lessonClassIds = $this->lesson->classrooms->pluck('id')->toArray();
                if (empty(array_intersect($taughtClassIds, $lessonClassIds))) {
                    abort(403, 'Anda tidak memiliki hak akses ke materi ini.');
                }
            }

            $this->isPreviewMode = true;
            $this->initActivePdf();

            // Load existing teacher note if any
            $note = Note::where('student_id', $user->id)
                ->where('lesson_id', $this->lesson->id)
                ->first();
            if ($note) {
                $this->noteBody = $note->body;
            }

            return;
        }

        // 2. Otorisasi untuk SISWA
        if ($user && $user->role === 'siswa') {
            $classroom = $user->activeClassroom();
            $classroomId = $classroom ? $classroom->id : 0;

            // Pastikan materi ditugaskan ke kelas siswa
            $classroomPivot = $this->lesson->classrooms()->where('classrooms.id', $classroomId)->first();
            if (!$classroomPivot) {
                abort(404, 'Materi ini tidak ditujukan untuk kelas Anda.');
            }

            // Cek status Draf (tidak boleh diakses siswa sama sekali)
            if ($this->lesson->status === 'draft') {
                abort(404, 'Materi ini masih berupa draf dan belum dapat diakses.');
            }

            // Cek status Terjadwal / Available From
            $now = Carbon::now();
            if ($classroomPivot->pivot->available_from && Carbon::parse($classroomPivot->pivot->available_from)->isFuture()) {
                $jadwal = Carbon::parse($classroomPivot->pivot->available_from)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
                session()->flash('warning', 'Materi "' . $this->lesson->title . '" belum dibuka. Jadwal tayang: ' . $jadwal . ' WIB.');
                $elementCode = $this->lesson->learningObjective?->element?->code ?? 'BK';
                $this->redirectRoute('siswa.belajar.tp', ['element' => $elementCode], navigate: true);
                return;
            }

            if ($this->lesson->status === 'scheduled' && $this->lesson->scheduled_at && Carbon::parse($this->lesson->scheduled_at)->isFuture()) {
                $jadwal = Carbon::parse($this->lesson->scheduled_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i');
                session()->flash('warning', 'Materi "' . $this->lesson->title . '" belum dibuka. Jadwal tayang: ' . $jadwal . ' WIB.');
                $elementCode = $this->lesson->learningObjective?->element?->code ?? 'BK';
                $this->redirectRoute('siswa.belajar.tp', ['element' => $elementCode], navigate: true);
                return;
            }

            // Cek Alur Belajar Berjenjang (Gated Learning Prerequisite)
            if (!$this->lesson->isUnlockedFor($user)) {
                $targetPrereq = $this->lesson->getEarliestIncompletePrerequisite($user) ?: $this->lesson->getEffectivePrerequisite();
                $prereqTitle = $targetPrereq ? ('"' . $targetPrereq->title . '"') : 'materi sebelumnya';

                if ($targetPrereq && $targetPrereq->siswa_url) {
                    session()->flash('warning', 'Materi "' . $this->lesson->title . '" masih terkunci. Anda dialihkan ke materi prasyarat: ' . $prereqTitle . ' terlebih dahulu.');
                    $this->redirect($targetPrereq->siswa_url, navigate: true);
                    return;
                }

                $elementCode = $this->lesson->learningObjective?->element?->code ?? 'BK';
                session()->flash('warning', 'Materi "' . $this->lesson->title . '" masih terkunci. Anda harus menyelesaikan materi ' . $prereqTitle . ' terlebih dahulu.');
                $this->redirectRoute('siswa.belajar.tp', ['element' => $elementCode], navigate: true);
                return;
            }

            // Load existing checkpoint state
            $progress = LessonProgress::where('student_id', $user->id)
                ->where('lesson_id', $this->lesson->id)
                ->first();
            if ($progress) {
                $this->checkpointPassed = $progress->hasPassedCheckpoint();
                $this->checkpointScore = $progress->checkpoint_score;
                if (!empty($progress->checkpoint_answers)) {
                    $this->checkpointAnswers = $progress->checkpoint_answers;
                    $this->checkpointSubmitted = true;
                }
            }

            // Load existing student note if any
            $note = Note::where('student_id', $user->id)
                ->where('lesson_id', $this->lesson->id)
                ->first();
            if ($note) {
                $this->noteBody = $note->body;
            }

            $this->initActivePdf();
        }
    }

    protected function initActivePdf(): void
    {
        if ($this->lesson && $this->activePdfId === null) {
            $firstPdf = $this->lesson->attachments->where('type', 'pdf')->sortBy('sort_order')->first();
            if ($firstPdf) {
                $this->activePdfId = $firstPdf->id;
            }
        }
    }

    public function setActivePdf(int $attId): void
    {
        $this->activePdfId = $attId;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function toggleItemProgress(string $itemKey): void
    {
        if (!$this->lesson || Auth::user()->role !== 'siswa') return;
        
        $userId = Auth::id();
        $progress = LessonProgress::firstOrCreate([
            'student_id' => $userId,
            'lesson_id' => $this->lesson->id,
        ]);

        $progress->toggleItem($itemKey);

        // Periksa apakah semua item (PDF + Video) sudah selesai
        $readings = $this->lesson->attachments->where('type', 'pdf');
        $videos = $this->lesson->attachments->where('type', 'youtube');
        $totalItems = $readings->count() + $videos->count();

        $completedCount = count($progress->completed_items ?? []);
        if ($totalItems > 0 && $completedCount >= $totalItems) {
            $progress->completed_at = now();
        } else {
            $progress->completed_at = null;
        }
        $progress->save();
    }

    public function isItemDone(string $itemKey): bool
    {
        if (!$this->lesson || Auth::user()->role !== 'siswa') return false;

        $progress = LessonProgress::where('student_id', Auth::id())
            ->where('lesson_id', $this->lesson->id)
            ->first();

        return $progress ? $progress->isItemCompleted($itemKey) : false;
    }

    public function toggleOverallDone(): void
    {
        if (!$this->lesson || Auth::user()->role !== 'siswa') return;

        $user = Auth::user();

        if ($this->lesson->is_gated && !$this->lesson->isCompletedBy($user)) {
            $rule = $this->lesson->completion_rule;
            $msg = match ($rule) {
                'checkpoint_only' => 'Materi ini belum selesai. Anda wajib lulus Kuis Pemantik Literasi (100% benar) terlebih dahulu.',
                'task_only' => 'Materi ini belum selesai. Anda wajib mengumpulkan tugas praktik terlebih dahulu.',
                'checkpoint_and_task' => 'Materi ini belum selesai. Anda wajib lulus Kuis Pemantik Literasi dan mengumpulkan tugas praktik terlebih dahulu.',
                default => 'Materi ini belum memenuhi kriteria ketuntasan.',
            };
            session()->flash('progress_error', $msg);
            return;
        }

        $userId = $user->id;
        $progress = LessonProgress::firstOrCreate([
            'student_id' => $userId,
            'lesson_id' => $this->lesson->id,
        ]);

        if ($progress->completed_at) {
            $progress->completed_at = null;
        } else {
            $progress->completed_at = now();
            // Tandai semua item selesai
            $allKeys = [];
            foreach ($this->lesson->attachments as $att) {
                $allKeys[] = ($att->type === 'pdf' ? 'pdf_' : 'video_') . $att->id;
            }
            $progress->completed_items = $allKeys;
        }
        $progress->save();
    }

    public function getIsDoneProperty(): bool
    {
        if (!$this->lesson || Auth::user()->role !== 'siswa') return false;

        return $this->lesson->isCompletedBy(Auth::user());
    }

    public function submitCheckpoints(): void
    {
        $user = Auth::user();
        if (!$user || !$this->lesson) return;

        // If in preview mode as teacher/admin, allow simulation
        $isTeacherPreview = in_array($user->role, ['guru', 'admin']);

        $checkpoints = $this->lesson->checkpoints()->orderBy('sort_order')->get();
        if ($checkpoints->isEmpty()) return;

        $rules = [];
        $messages = [];
        foreach ($checkpoints as $cp) {
            $rules["checkpointAnswers.{$cp->id}"] = 'required|in:A,B,C,D';
            $messages["checkpointAnswers.{$cp->id}.required"] = 'Pilih salah satu opsi jawaban untuk soal #' . $cp->sort_order . '.';
        }
        $this->validate($rules, $messages);

        $correctCount = 0;
        $totalCount = $checkpoints->count();
        $this->checkpointResults = [];

        foreach ($checkpoints as $cp) {
            $given = $this->checkpointAnswers[$cp->id] ?? null;
            $isCorrect = $cp->isCorrect($given);
            if ($isCorrect) {
                $correctCount++;
            }
            $this->checkpointResults[$cp->id] = [
                'is_correct' => $isCorrect,
                'correct' => $cp->correct_answer,
                'explanation' => $cp->explanation,
                'selected' => $given,
            ];
        }

        $this->checkpointScore = $totalCount > 0 ? (int) round(($correctCount / $totalCount) * 100) : 100;
        $this->checkpointPassed = ($correctCount === $totalCount);
        $this->checkpointSubmitted = true;

        if (!$isTeacherPreview && $user->role === 'siswa') {
            $progress = LessonProgress::firstOrCreate([
                'student_id' => $user->id,
                'lesson_id' => $this->lesson->id,
            ]);

            $progress->checkpoint_score = $this->checkpointScore;
            $progress->checkpoint_answers = $this->checkpointAnswers;

            if ($this->checkpointPassed) {
                $progress->checkpoint_passed_at = now();
                $progress->save();

                // Auto-mark completed if lesson criteria met
                if ($this->lesson->isCompletedBy($user)) {
                    $progress->completed_at = now();
                    $progress->save();
                }

                session()->flash('checkpoint_success', 'Hebat! Anda menjawab semua kuis pemantik dengan benar (Skor 100%). Validasi literasi Anda tuntas!');
            } else {
                $progress->save();
                session()->flash('checkpoint_error', 'Skor Anda: ' . $this->checkpointScore . '%. Anda harus menjawab semua soal dengan benar (100%) untuk memvalidasi literasi membaca. Silakan baca ulang modul materi dan coba lagi.');
            }
        } else {
            // Preview Mode feedback for Guru
            if ($this->checkpointPassed) {
                session()->flash('checkpoint_success', '[Mode Pratinjau Guru] Jawaban 100% benar! Sistem akan otomatis membuka materi selanjutnya untuk siswa.');
            } else {
                session()->flash('checkpoint_error', '[Mode Pratinjau Guru] Skor: ' . $this->checkpointScore . '%. Siswa harus 100% benar untuk membuka materi berikutnya.');
            }
        }
    }

    public function tryAgainCheckpoints(): void
    {
        $this->checkpointSubmitted = false;
        $this->checkpointResults = [];
    }

    public function getProgressDataProperty(): array
    {
        if (!$this->lesson || Auth::user()->role !== 'siswa') {
            return ['completed' => 0, 'total' => 0, 'percent' => 0];
        }

        $readings = $this->lesson->attachments->where('type', 'pdf');
        $videos = $this->lesson->attachments->where('type', 'youtube');
        $total = $readings->count() + $videos->count();

        $progress = LessonProgress::where('student_id', Auth::id())
            ->where('lesson_id', $this->lesson->id)
            ->first();

        if ($total === 0) {
            $isCompleted = $progress && $progress->completed_at !== null;
            return [
                'completed' => $isCompleted ? 1 : 0,
                'total' => 1,
                'percent' => $isCompleted ? 100 : 0,
            ];
        }

        $completed = $progress ? count($progress->completed_items ?? []) : 0;
        $percent = min(100, round(($completed / $total) * 100));

        return [
            'completed' => $completed,
            'total' => $total,
            'percent' => $percent,
        ];
    }

    public function saveNote(): void
    {
        if (!$this->lesson || !Auth::check()) return;

        $this->validate(['noteBody' => 'nullable|string']);

        $user = Auth::user();
        Note::updateOrCreate(
            ['student_id' => $user->id, 'lesson_id' => $this->lesson->id],
            ['body' => $this->noteBody]
        );

        session()->flash('success', 'Catatan pribadi berhasil disimpan.');
    }

    public function sendDiscussion(): void
    {
        if (!$this->lesson || !Auth::check()) return;

        $this->validate(['discussionBody' => 'required|string|max:1000']);

        $user = Auth::user();
        $classroomId = null;

        if ($user->role === 'siswa') {
            $classroom = $user->activeClassroom();
            $classroomId = $classroom ? $classroom->id : $this->lesson->classrooms->first()?->id;
        } else {
            // Guru / Admin: gunakan selectedClassroomId jika dipilih, atau kelas pertama materi
            $classroomId = $this->selectedClassroomId ?: $this->lesson->classrooms->first()?->id;
        }

        if (!$classroomId) {
            session()->flash('discussion_error', 'Materi ini belum ditugaskan ke kelas manapun.');
            return;
        }

        Discussion::create([
            'lesson_id' => $this->lesson->id,
            'classroom_id' => $classroomId,
            'user_id' => $user->id,
            'body' => $this->discussionBody,
            'is_hidden' => false,
        ]);

        $this->discussionBody = '';
        session()->flash('discussion_success', 'Diskusi berhasil dikirim.');
    }

    public function startReply(int $discussionId): void
    {
        $this->replyingToId = $discussionId;
        $this->replyBody = '';
    }

    public function cancelReply(): void
    {
        $this->replyingToId = null;
        $this->replyBody = '';
    }

    public function sendReply(int $parentId): void
    {
        if (!$this->lesson || !Auth::check()) return;

        $this->validate(['replyBody' => 'required|string|max:1000']);

        $parent = Discussion::find($parentId);
        if (!$parent) return;

        $user = Auth::user();

        Discussion::create([
            'lesson_id' => $this->lesson->id,
            'classroom_id' => $parent->classroom_id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'body' => $this->replyBody,
            'is_hidden' => false,
        ]);

        $this->replyingToId = null;
        $this->replyBody = '';
        session()->flash('discussion_success', 'Tanggapan berhasil dikirim.');
    }

    public function toggleHideDiscussion(int $discussionId): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['guru', 'admin'])) return;

        $disc = Discussion::where('lesson_id', $this->lesson->id)->find($discussionId);
        if ($disc) {
            $disc->is_hidden = !$disc->is_hidden;
            $disc->save();
            session()->flash('discussion_success', $disc->is_hidden ? 'Pesan berhasil disembunyikan.' : 'Pesan berhasil ditampilkan.');
        }
    }

    public function deleteDiscussion(int $discussionId): void
    {
        $user = Auth::user();
        if (!$user) return;

        $disc = Discussion::where('lesson_id', $this->lesson->id)->find($discussionId);
        if (!$disc) return;

        if (in_array($user->role, ['guru', 'admin']) || $disc->user_id === $user->id) {
            $disc->delete();
            session()->flash('discussion_success', 'Pesan diskusi berhasil dihapus.');
        }
    }

    public function render()
    {
        $layout = $this->isPreviewMode ? 'components.layouts.guru' : 'components.layouts.siswa';

        if ($this->isNotFound || !$this->lesson) {
            return view('livewire.siswa.belajar.materi-detail', [
                'lessons' => collect(),
                'readings' => collect(),
                'videos' => collect(),
                'tasks' => collect(),
                'discussions' => collect(),
                'discussionCount' => 0,
                'prevLesson' => null,
                'nextLesson' => null,
                'completedLessonsCount' => 0,
                'totalLessonsCount' => 0,
            ])->layout($layout);
        }

        $user = Auth::user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;

        // Load lessons in this TP for sidebar
        $lessonsQuery = $this->tp->lessons()
            ->with(['attachments', 'classrooms'])
            ->orderBy('sort_order');

        if (!$this->isPreviewMode) {
            $lessonsQuery->availableFor($classroomId);
        }

        $sidebarLessons = $lessonsQuery->get();

        // Load attachments
        $readings = $this->lesson->attachments->where('type', 'pdf')->sortBy('sort_order')->values();
        $videos = $this->lesson->attachments->where('type', 'youtube')->sortBy('sort_order')->values();

        // Load linked assessments & student submission info
        $tasksData = [];
        $assessments = $this->lesson->assessments()->with('classrooms')->get();

        foreach ($assessments as $ass) {
            $submission = null;
            if (!$this->isPreviewMode && $user->role === 'siswa') {
                $submission = Submission::where('assessment_id', $ass->id)
                    ->where('student_id', $user->id)
                    ->first();
            }

            $tasksData[] = [
                'assessment' => $ass,
                'submission' => $submission,
                'is_submitted' => $submission !== null,
                'is_graded' => $submission && $submission->score !== null,
                'score' => $submission ? $submission->score : null,
                'due_formatted' => $ass->due_at ? Carbon::parse($ass->due_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Tidak ada tenggat waktu',
                'is_past_due' => $ass->due_at && Carbon::parse($ass->due_at)->isPast(),
            ];
        }

        // Active PDF object
        $activePdf = null;
        if ($this->activePdfId) {
            $activePdf = $readings->firstWhere('id', $this->activePdfId);
        }
        if (!$activePdf && $readings->isNotEmpty()) {
            $activePdf = $readings->first();
            $this->activePdfId = $activePdf->id;
        }

        // Load discussions with replies & users
        $discQuery = Discussion::with(['user', 'classroom', 'replies' => function ($q) use ($user) {
                if (!$user || !in_array($user->role, ['guru', 'admin'])) {
                    $q->where('is_hidden', false);
                }
                $q->with('user')->oldest();
            }])
            ->where('lesson_id', $this->lesson->id)
            ->whereNull('parent_id')
            ->latest();

        if (!$user || !in_array($user->role, ['guru', 'admin'])) {
            $discQuery->where('is_hidden', false);
        }

        if ($user && $user->role === 'siswa') {
            if ($classroomId) {
                $discQuery->where('classroom_id', $classroomId);
            }
        } else {
            if ($this->selectedClassroomId) {
                $discQuery->where('classroom_id', $this->selectedClassroomId);
            }
        }

        $discussions = $discQuery->get();
        $discussionCount = $discussions->count();

        // Previous and Next navigation
        $prevLesson = null;
        $nextLesson = null;
        $currentIndex = $sidebarLessons->search(fn($l) => $l->id === $this->lesson->id);

        if ($currentIndex !== false) {
            if ($currentIndex > 0) $prevLesson = $sidebarLessons[$currentIndex - 1];
            if ($currentIndex < $sidebarLessons->count() - 1) $nextLesson = $sidebarLessons[$currentIndex + 1];
        }

        // Completed lessons count for sidebar progress bar
        $completedLessonsCount = 0;
        if (!$this->isPreviewMode && $user->role === 'siswa') {
            $completedLessonsCount = LessonProgress::where('student_id', $user->id)
                ->whereIn('lesson_id', $sidebarLessons->pluck('id'))
                ->whereNotNull('completed_at')
                ->count();
        }

        return view('livewire.siswa.belajar.materi-detail', [
            'lessons' => $sidebarLessons,
            'readings' => $readings,
            'videos' => $videos,
            'tasksData' => $tasksData,
            'activePdf' => $activePdf,
            'discussions' => $discussions,
            'discussionCount' => $discussionCount,
            'prevLesson' => $prevLesson,
            'nextLesson' => $nextLesson,
            'completedLessonsCount' => $completedLessonsCount,
            'totalLessonsCount' => $sidebarLessons->count(),
        ])->layout($layout);
    }
}
