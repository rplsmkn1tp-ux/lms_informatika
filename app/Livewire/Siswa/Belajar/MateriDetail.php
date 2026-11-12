<?php

namespace App\Livewire\Siswa\Belajar;

use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Discussion;
use App\Models\Note;
use Livewire\Component;

class MateriDetail extends Component
{
    public Element $element;
    public LearningObjective $tp;
    public ?Lesson $lesson = null;

    public $tab = 'materi'; // materi, diskusi, catatan

    public $discussionBody = '';
    public $noteBody = '';

    public bool $isNotFound = false;
    public bool $isLocked = false;
    public string $errorMessage = '';

    public function mount(Element $element, LearningObjective $tp, $lesson)
    {
        $this->element = $element;
        $this->tp = $tp;
        $this->lesson = $tp->lessons()->where('code', $lesson)->first();

        if (!$this->lesson) {
            $this->isNotFound = true;
            $this->errorMessage = 'Materi tidak ditemukan atau sudah dihapus.';
            return;
        }

        // Verify authorization
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;

        $classroomPivot = $this->lesson->classrooms()->where('classrooms.id', $classroomId)->first();
        $hasAccess = $classroomPivot !== null;
        $this->isLocked = $classroomPivot && $classroomPivot->pivot->available_from && \Illuminate\Support\Carbon::parse($classroomPivot->pivot->available_from)->isFuture();

        if ($this->lesson->status !== 'published' || !$hasAccess) {
            $this->isNotFound = true;
            $this->errorMessage = 'Materi tidak ditemukan atau Anda belum memiliki akses ke materi ini.';
            return;
        }
        
        if ($this->isLocked) {
            $this->errorMessage = 'Materi belum dapat diakses (terkunci).';
            return;
        }

        // Load existing note if any
        $note = Note::where('student_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();
        if ($note) {
            $this->noteBody = $note->body;
        }
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function toggleDone()
    {
        if (!$this->lesson) return;
        
        $user = auth()->user();
        $progress = LessonProgress::where('student_id', $user->id)
            ->where('lesson_id', $this->lesson->id)
            ->first();

        if ($progress) {
            $progress->delete();
        } else {
            LessonProgress::create([
                'student_id' => $user->id,
                'lesson_id' => $this->lesson->id,
                'completed_at' => now(),
            ]);
        }
    }

    public function getIsDoneProperty()
    {
        if (!$this->lesson) return false;
        
        return LessonProgress::where('student_id', auth()->id())
            ->where('lesson_id', $this->lesson->id)
            ->exists();
    }

    public function saveNote()
    {
        if (!$this->lesson) return;
        
        $this->validate(['noteBody' => 'nullable|string']);
        
        $user = auth()->user();
        Note::updateOrCreate(
            ['student_id' => $user->id, 'lesson_id' => $this->lesson->id],
            ['body' => $this->noteBody]
        );

        session()->flash('success', 'Catatan disimpan.');
    }

    public function sendDiscussion()
    {
        if (!$this->lesson) return;
        
        $this->validate(['discussionBody' => 'required|string']);
        
        $user = auth()->user();
        $classroom = $user->activeClassroom();

        Discussion::create([
            'lesson_id' => $this->lesson->id,
            'classroom_id' => $classroom ? $classroom->id : null,
            'user_id' => $user->id,
            'body' => $this->discussionBody,
            'is_hidden' => false,
        ]);

        $this->discussionBody = '';
    }

    public function render()
    {
        if ($this->isNotFound || !$this->lesson) {
            return view('livewire.siswa.belajar.materi-detail', [
                'lessons' => collect(),
                'discussions' => collect(),
                'discussionCount' => 0,
                'prevLesson' => null,
                'nextLesson' => null,
                'completedLessonsCount' => 0,
                'totalLessonsCount' => 0,
            ])->layout('components.layouts.siswa');
        }

        // Load sidebar lessons
        $user = auth()->user();
        $classroom = $user->activeClassroom();
        $classroomId = $classroom ? $classroom->id : 0;

        $lessons = $this->tp->lessons()
            ->where('status', 'published')
            ->with(['classrooms' => function ($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            }])
            ->whereHas('classrooms', function ($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            })
            ->orderBy('sort_order')
            ->get();

        // Load discussions for this classroom
        $discussions = Discussion::with(['user', 'replies.user'])
            ->where('lesson_id', $this->lesson->id)
            ->where('classroom_id', $classroomId)
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->get();
            
        $discussionCount = Discussion::where('lesson_id', $this->lesson->id)
            ->where('classroom_id', $classroomId)
            ->count();

        // Find prev and next lessons
        $prevLesson = null;
        $nextLesson = null;
        $currentIndex = $lessons->search(fn($l) => $l->id === $this->lesson->id);
        
        if ($currentIndex !== false) {
            if ($currentIndex > 0) $prevLesson = $lessons[$currentIndex - 1];
            if ($currentIndex < $lessons->count() - 1) $nextLesson = $lessons[$currentIndex + 1];
        }

        // Student's completed lessons count for this TP
        $completedLessonsCount = LessonProgress::where('student_id', $user->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->count();

        return view('livewire.siswa.belajar.materi-detail', [
            'lessons' => $lessons,
            'discussions' => $discussions,
            'discussionCount' => $discussionCount,
            'prevLesson' => $prevLesson,
            'nextLesson' => $nextLesson,
            'completedLessonsCount' => $completedLessonsCount,
            'totalLessonsCount' => $lessons->count(),
        ])->layout('components.layouts.siswa');
    }
}
