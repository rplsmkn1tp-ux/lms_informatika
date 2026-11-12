<?php

namespace App\Livewire\Guru\Tugas;

use App\Models\Assessment;
use App\Models\AttendanceSession;
use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guru')]
class Form extends Component
{
    use WithFileUploads;

    public ?Assessment $assessment = null;
    public bool $isEdit = false;

    // Fields
    public string $title = '';
    public string $instructions = '';
    public array $selectedClassrooms = [];
    public ?string $opens_at = null;
    public ?string $due_at = null;
    public int $max_score = 100;
    public array $submission_types = ['file'];
    public bool $allow_late = false;
    public bool $allow_resubmit = false;
    public ?int $lesson_id = null;
    public ?int $learning_objective_id = null;
    public ?int $attendance_session_id = null;
    public ?int $meeting_no = null;
    public string $grade_group = 'tugas';
    public ?int $grade_seq = 1;

    // Attachments
    public array $existingAttachments = [];
    public $newFiles = [];
    public string $newLinkTitle = '';
    public string $newLinkUrl = '';

    public function mount(?Assessment $assessment = null)
    {
        $teacher = auth()->user();

        if ($assessment && $assessment->exists) {
            if ($assessment->created_by !== $teacher->id && $teacher->role !== 'admin') {
                abort(403, 'Akses ditolak.');
            }

            $this->assessment = $assessment;
            $this->isEdit = true;
            $this->title = $assessment->title;
            $this->instructions = $assessment->instructions ?? '';
            $this->selectedClassrooms = $assessment->classrooms->pluck('id')->map(fn($id) => (string) $id)->toArray();
            $this->opens_at = $assessment->opens_at ? Carbon::parse($assessment->opens_at)->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : null;
            $this->due_at = $assessment->due_at ? Carbon::parse($assessment->due_at)->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : null;
            $this->max_score = $assessment->max_score ?? 100;
            $this->submission_types = !empty($assessment->submission_types) ? $assessment->submission_types : ['file'];
            $this->allow_late = (bool) $assessment->allow_late;
            $this->allow_resubmit = (bool) $assessment->allow_resubmit;
            $this->lesson_id = $assessment->lesson_id;
            $this->learning_objective_id = $assessment->learning_objective_id;
            $this->attendance_session_id = $assessment->attendance_session_id;
            $this->meeting_no = $assessment->meeting_no ?: $assessment->attendanceSession?->meeting_no;
            $this->grade_group = $assessment->grade_group ?: 'tugas';
            $this->grade_seq = $assessment->grade_seq ?: 1;
            $this->existingAttachments = $assessment->teacher_attachments ?? [];
        } else {
            $this->opens_at = Carbon::now('Asia/Jakarta')->format('Y-m-d\TH:i');
            $this->due_at = Carbon::now('Asia/Jakarta')->addDays(7)->setHour(23)->setMinute(59)->format('Y-m-d\TH:i');
            // Default select all classrooms taught by teacher
            $this->selectedClassrooms = $teacher->taughtClassrooms()->pluck('classrooms.id')->map(fn($id) => (string) $id)->toArray();
            $this->grade_group = 'tugas';
            $highestSeq = Assessment::where('type', 'tugas')->max('grade_seq') ?? 0;
            $this->grade_seq = max(1, $highestSeq + 1);
            $this->meeting_no = 1;
        }
    }

    public function updatedLessonId($val)
    {
        if ($val) {
            $lesson = Lesson::with('learningObjective')->find($val);
            if ($lesson && $lesson->learning_objective_id) {
                $this->learning_objective_id = $lesson->learning_objective_id;
            }
        }
    }

    public function updatedAttendanceSessionId($val)
    {
        if ($val) {
            $session = AttendanceSession::find($val);
            if ($session && $session->meeting_no) {
                $this->meeting_no = $session->meeting_no;
            }
        }
    }

    public function selectAllClassrooms()
    {
        $teacher = auth()->user();
        $this->selectedClassrooms = $teacher->taughtClassrooms()->pluck('classrooms.id')->map(fn($id) => (string) $id)->toArray();
    }

    public function deselectAllClassrooms()
    {
        $this->selectedClassrooms = [];
    }

    public function addLinkAttachment()
    {
        $this->validate([
            'newLinkTitle' => 'required|string|max:100',
            'newLinkUrl' => 'required|url|max:500',
        ]);

        $this->existingAttachments[] = [
            'type' => 'link',
            'name' => $this->newLinkTitle,
            'url' => $this->newLinkUrl,
        ];

        $this->reset(['newLinkTitle', 'newLinkUrl']);
    }

    public function removeExistingAttachment($index)
    {
        if (isset($this->existingAttachments[$index])) {
            $att = $this->existingAttachments[$index];
            if (($att['type'] ?? '') === 'file' && !empty($att['path'])) {
                Storage::disk('local')->delete($att['path']);
            }
            array_splice($this->existingAttachments, $index, 1);
        }
    }

    public function removeNewFile($index)
    {
        if (isset($this->newFiles[$index])) {
            array_splice($this->newFiles, $index, 1);
        }
    }

    public function save($status = 'published')
    {
        $tpValidation = $this->isEdit ? 'nullable|exists:learning_objectives,id' : 'required|exists:learning_objectives,id';

        $this->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'selectedClassrooms' => 'required|array|min:1',
            'selectedClassrooms.*' => 'exists:classrooms,id',
            'opens_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'max_score' => 'required|integer|min:1|max:1000',
            'submission_types' => 'required|array|min:1',
            'allow_late' => 'boolean',
            'allow_resubmit' => 'boolean',
            'lesson_id' => 'nullable|exists:lessons,id',
            'learning_objective_id' => $tpValidation,
            'attendance_session_id' => 'nullable|exists:attendance_sessions,id',
            'meeting_no' => 'nullable|integer|min:1|max:50',
            'newFiles.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,webp|max:10240',
        ], [
            'selectedClassrooms.min' => 'Pilih minimal satu kelas untuk menugaskan tugas ini.',
            'learning_objective_id.required' => 'Pilih Tujuan Pembelajaran (TP) untuk tugas ini.',
            'learning_objective_id.exists' => 'Tujuan Pembelajaran yang dipilih tidak valid.',
            'submission_types.min' => 'Pilih minimal satu tipe pengumpulan jawaban.',
            'newFiles.*.max' => 'Ukuran file lampiran maksimal 10 MB per file.',
            'newFiles.*.mimes' => 'Format file lampiran harus berupa PDF, Dokumen Office, Gambar, atau ZIP.',
        ]);

        $attachments = $this->existingAttachments;

        // Process uploaded files
        if (!empty($this->newFiles)) {
            foreach ($this->newFiles as $file) {
                $path = $file->store('teacher_attachments', 'local');
                $attachments[] = [
                    'type' => 'file',
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
        }

        $opensAt = $this->opens_at ? Carbon::parse($this->opens_at, 'Asia/Jakarta')->setTimezone('UTC') : null;
        $dueAt = $this->due_at ? Carbon::parse($this->due_at, 'Asia/Jakarta')->setTimezone('UTC') : null;

        $lesson = $this->lesson_id ? Lesson::find($this->lesson_id) : null;
        $learningObjectiveId = $this->learning_objective_id ?: ($lesson ? $lesson->learning_objective_id : null);
        $elementId = $lesson?->learningObjective?->element_id 
            ?: ($learningObjectiveId ? \App\Models\LearningObjective::find($learningObjectiveId)?->element_id : null);

        // Auto-sinkron nomor pertemuan dan sesi agenda jika ada
        if ($this->attendance_session_id && !$this->meeting_no) {
            $this->meeting_no = AttendanceSession::find($this->attendance_session_id)?->meeting_no;
        }

        if (!$this->attendance_session_id && $this->meeting_no && !empty($this->selectedClassrooms)) {
            $matchedSession = AttendanceSession::where('teacher_id', auth()->id())
                ->whereIn('classroom_id', $this->selectedClassrooms)
                ->where('meeting_no', $this->meeting_no)
                ->first();
            if ($matchedSession) {
                $this->attendance_session_id = $matchedSession->id;
            }
        }

        $data = [
            'learning_objective_id' => $learningObjectiveId,
            'element_id' => $elementId,
            'lesson_id' => $this->lesson_id,
            'attendance_session_id' => $this->attendance_session_id,
            'meeting_no' => $this->meeting_no,
            'type' => 'tugas',
            'grade_group' => $this->grade_group ?: 'tugas',
            'grade_seq' => $this->grade_seq ? (int) $this->grade_seq : 1,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'teacher_attachments' => $attachments,
            'submission_types' => $this->submission_types,
            'allow_late' => $this->allow_late,
            'allow_resubmit' => $this->allow_resubmit,
            'opens_at' => $opensAt,
            'due_at' => $dueAt,
            'max_score' => $this->max_score,
            'status' => $status,
        ];

        if ($this->isEdit && $this->assessment) {
            $this->assessment->update($data);
            $assessment = $this->assessment;
            session()->flash('success', 'Tugas berhasil diperbarui.');
        } else {
            $data['created_by'] = auth()->id();
            $assessment = Assessment::create($data);
            session()->flash('success', $status === 'published' ? 'Tugas berhasil diterbitkan.' : 'Tugas disimpan sebagai draft.');
        }

        if ($learningObjectiveId) {
            $assessment->syncLearningObjectives([$learningObjectiveId]);
        }

        // Sync classrooms with pivot opens_at and due_at
        $syncData = [];
        foreach ($this->selectedClassrooms as $classroomId) {
            $syncData[(int) $classroomId] = [
                'opens_at' => $opensAt,
                'due_at' => $dueAt,
            ];
        }
        $assessment->classrooms()->sync($syncData);

        // Sinkronisasi otomatis ke Buku Nilai seluruh kelas terkait
        foreach ($assessment->classrooms as $cls) {
            try {
                app(\App\Services\GradeBookSyncService::class)->syncClassroom($cls);
            } catch (\Exception $e) {
            }
        }

        return redirect()->route('guru.tugas.index');
    }

    public function render()
    {
        $teacher = auth()->user();
        $classrooms = $teacher->taughtClassrooms()->get();
        $lessons = Lesson::where('status', 'published')->orderBy('title')->get();
        $attendanceSessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->orderBy('meeting_date', 'desc')
            ->orderBy('meeting_no', 'desc')
            ->take(20)
            ->get();

        $learningObjectives = \App\Models\LearningObjective::with('element')
            ->orderBy('element_id')
            ->orderBy('sort_order')
            ->get();

        return view('livewire.guru.tugas.form', [
            'classrooms' => $classrooms,
            'lessons' => $lessons,
            'learningObjectives' => $learningObjectives,
            'attendanceSessions' => $attendanceSessions,
        ]);
    }
}
