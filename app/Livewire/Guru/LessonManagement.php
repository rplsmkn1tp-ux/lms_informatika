<?php

namespace App\Livewire\Guru;

use App\Models\Lesson;
use App\Models\LearningObjective;
use App\Models\LessonAttachment;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

#[Layout('components.layouts.guru')]
class LessonManagement extends Component
{
    use WithFileUploads;

    public $isModalOpen = false;
    public $isPublishModalOpen = false;
    public $isAttachmentModalOpen = false;

    // Form fields for Lesson
    public $lessonId;
    
    #[Validate('required|exists:learning_objectives,id')]
    public $learning_objective_id;
    
    #[Validate('required|string|max:50')]
    public $code;
    
    #[Validate('required|string|max:255')]
    public $title;
    
    #[Validate('nullable|string')]
    public $summary;
    
    #[Validate('nullable|string')]
    public $content;
    
    #[Validate('nullable|url')]
    public $video_url;
    
    #[Validate('nullable|integer|min:1')]
    public $estimated_minutes;
    
    #[Validate('required|integer')]
    public $sort_order = 0;

    // For Publish Modal
    public $status = 'draft';
    public $selectedClassrooms = [];
    public $availableFrom = []; // array keyed by classroom_id

    // For Attachment Modal
    public $newAttachment;
    public $attachmentLabel;
    public $activeLessonForAttachment;

    public $search = '';
    public $activeTab = 'semua';
    public $filterElement = '';
    public $filterClass = '';

    public function mount()
    {
        // Initialization if needed
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function copyFromTemplate()
    {
        session()->flash('message', 'Fitur salin dari template segera hadir.');
    }

    public function previewVideo()
    {
        if ($this->video_url) {
            $this->dispatch('open-video', url: $this->video_url);
        } else {
            session()->flash('message', 'Silakan isi link video YouTube terlebih dahulu.');
        }
    }

    public function render()
    {
        $groupedLessons = LearningObjective::where('created_by', Auth::id())
            ->with(['lessons' => function ($q) {
                if ($this->activeTab === 'terbit') {
                    $q->where('status', 'published');
                } elseif ($this->activeTab === 'draf') {
                    $q->where('status', 'draft');
                } elseif ($this->activeTab === 'terjadwal') {
                    // Scheduled might be complex if it depends on pivot table, 
                    // for now just filter published with future available_from
                    // Simplified: just return none if we don't have scheduled logic
                    $q->where('status', 'scheduled'); 
                }
                
                if (!empty($this->search)) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                }
                
                $q->with('classrooms', 'attachments')->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->get();

        // Filter out empty TPs if search or specific tab is active
        if ($this->activeTab !== 'semua' || !empty($this->search)) {
            $groupedLessons = $groupedLessons->filter(function($tp) {
                return $tp->lessons->count() > 0;
            });
        }

        $myClassrooms = Auth::user()->taughtClassrooms;

        return view('livewire.guru.lesson-management', [
            'groupedLessons' => $groupedLessons,
            'myClassrooms' => $myClassrooms,
        ]);
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset([
            'lessonId', 'learning_objective_id', 'code', 'title', 'summary', 
            'content', 'video_url', 'estimated_minutes', 'sort_order'
        ]);
        
        // Auto-select first TP if available
        $firstTp = LearningObjective::where('created_by', Auth::id())->orderBy('sort_order')->first();
        if ($firstTp) {
            $this->learning_objective_id = $firstTp->id;
            
            // Auto sort order based on existing lessons in this TP
            $lastLesson = Lesson::where('learning_objective_id', $firstTp->id)->orderByDesc('sort_order')->first();
            $this->sort_order = $lastLesson ? $lastLesson->sort_order + 1 : 1;
        } else {
            $this->sort_order = 1;
        }

        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        
        $lesson = Lesson::findOrFail($id);
        if ($lesson->created_by !== Auth::id()) abort(403);

        $this->lessonId = $lesson->id;
        $this->learning_objective_id = $lesson->learning_objective_id;
        $this->code = $lesson->code;
        $this->title = $lesson->title;
        $this->summary = $lesson->summary;
        $this->content = $lesson->content;
        $this->video_url = $lesson->video_url;
        $this->estimated_minutes = $lesson->estimated_minutes;
        $this->sort_order = $lesson->sort_order;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        // Check ownership of TP
        $tp = LearningObjective::findOrFail($this->learning_objective_id);
        if ($tp->created_by !== Auth::id()) abort(403);

        if ($this->lessonId) {
            $lesson = Lesson::findOrFail($this->lessonId);
            if ($lesson->created_by !== Auth::id()) abort(403);
            
            $lesson->update([
                'learning_objective_id' => $this->learning_objective_id,
                'code' => $this->code,
                'title' => $this->title,
                'summary' => $this->summary,
                'content' => $this->content,
                'video_url' => $this->video_url,
                'estimated_minutes' => $this->estimated_minutes,
                'sort_order' => $this->sort_order,
            ]);
            session()->flash('message', 'Subbab berhasil diperbarui.');
        } else {
            Lesson::create([
                'learning_objective_id' => $this->learning_objective_id,
                'code' => $this->code,
                'title' => $this->title,
                'summary' => $this->summary,
                'content' => $this->content,
                'video_url' => $this->video_url,
                'estimated_minutes' => $this->estimated_minutes,
                'sort_order' => $this->sort_order,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);
            session()->flash('message', 'Subbab berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $lesson = Lesson::findOrFail($id);
        if ($lesson->created_by !== Auth::id()) abort(403);
        
        $lesson->delete();
        session()->flash('message', 'Subbab berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    // --- Publish & Assign Logic ---

    public function openPublishModal($id)
    {
        $lesson = Lesson::with('classrooms')->findOrFail($id);
        if ($lesson->created_by !== Auth::id()) abort(403);

        $this->lessonId = $lesson->id;
        $this->status = $lesson->status;
        
        $this->selectedClassrooms = $lesson->classrooms->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->availableFrom = [];
        
        foreach ($lesson->classrooms as $cls) {
            if ($cls->pivot->available_from) {
                // Convert to datetime-local format string
                $this->availableFrom[$cls->id] = \Carbon\Carbon::parse($cls->pivot->available_from)->format('Y-m-d\TH:i');
            }
        }

        $this->isPublishModalOpen = true;
    }

    public function closePublishModal()
    {
        $this->isPublishModalOpen = false;
    }

    public function savePublish()
    {
        $lesson = Lesson::findOrFail($this->lessonId);
        if ($lesson->created_by !== Auth::id()) abort(403);

        $lesson->update(['status' => $this->status]);

        if ($this->status === 'published') {
            $syncData = [];
            foreach ($this->selectedClassrooms as $classId) {
                $availableDate = !empty($this->availableFrom[$classId]) ? $this->availableFrom[$classId] : null;
                $syncData[$classId] = ['available_from' => $availableDate];
            }
            $lesson->classrooms()->sync($syncData);
        } else {
            // Optional: you might want to keep classroom assignments even if returning to draft, 
            // or clear them. Usually, keeping them is fine as status='draft' overrides availability.
            $lesson->classrooms()->sync([]);
        }

        session()->flash('message', 'Pengaturan terbit dan penjadwalan berhasil disimpan.');
        $this->closePublishModal();
    }

    // --- Attachment Logic ---

    public function openAttachmentModal($id)
    {
        $lesson = Lesson::findOrFail($id);
        if ($lesson->created_by !== Auth::id()) abort(403);

        $this->activeLessonForAttachment = $lesson;
        $this->reset(['newAttachment', 'attachmentLabel']);
        $this->isAttachmentModalOpen = true;
    }

    public function closeAttachmentModal()
    {
        $this->isAttachmentModalOpen = false;
        $this->activeLessonForAttachment = null;
    }

    public function uploadAttachment()
    {
        $this->validate([
            'newAttachment' => 'required|file|max:10240', // 10MB max
            'attachmentLabel' => 'required|string|max:100',
        ]);

        if (!$this->activeLessonForAttachment) return;
        if ($this->activeLessonForAttachment->created_by !== Auth::id()) abort(403);

        $path = $this->newAttachment->store('lesson_attachments', 'public');

        $this->activeLessonForAttachment->attachments()->create([
            'label' => $this->attachmentLabel,
            'file_path' => $path,
            'type' => $this->newAttachment->getClientOriginalExtension(),
        ]);

        $this->reset(['newAttachment', 'attachmentLabel']);
        // Refresh the active lesson to show the new attachment
        $this->activeLessonForAttachment->refresh();
        
        session()->flash('attachment_message', 'Lampiran berhasil diunggah.');
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = LessonAttachment::findOrFail($attachmentId);
        if ($attachment->lesson->created_by !== Auth::id()) abort(403);

        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();
        $this->activeLessonForAttachment->refresh();
        session()->flash('attachment_message', 'Lampiran berhasil dihapus.');
    }
}
