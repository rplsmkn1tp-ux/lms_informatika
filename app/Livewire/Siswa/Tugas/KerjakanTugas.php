<?php

namespace App\Livewire\Siswa\Tugas;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\Submission;
use App\Models\SubmissionAnswer;
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

        // Ambil submission terakhir
        $this->submission = Submission::where('assessment_id', $this->assessment->id)
            ->where('student_id', $user->id)
            ->latest('attempt')
            ->first();

        if ($this->submission) {
            $this->textContent = $this->submission->text_content ?? '';
            $this->linkUrl = $this->submission->link_url ?? '';

            if ($this->submission->status === Submission::STATUS_REVISION) {
                // Diminta revisi oleh guru
                $this->canResubmit = true;
                $this->isLocked = false;
            } elseif (in_array($this->submission->status, [Submission::STATUS_SUBMITTED, Submission::STATUS_GRADED])) {
                if ($this->submission->status === Submission::STATUS_GRADED) {
                    $this->isLocked = true;
                    $this->lockReason = 'Tugas ini sudah dinilai oleh guru.';
                } elseif ($assessment->allow_resubmit) {
                    $this->canResubmit = true;
                    $this->isLocked = false;
                } else {
                    $this->isLocked = true;
                    $this->lockReason = 'Tugas telah dikumpulkan dan pengumpulan ulang tidak diizinkan.';
                }
            }
        } else {
            // Belum pernah mengumpulkan
            if ($this->isPastDue && !$assessment->allow_late) {
                $this->isLocked = true;
                $this->lockReason = 'Batas waktu pengumpulan telah berakhir. Pengumpulan terlambat tidak diizinkan oleh guru.';
            }
        }
    }

    public function submit()
    {
        if (RateLimiter::tooManyAttempts('submit-task:' . auth()->id(), 5)) {
            session()->flash('error', 'Terlalu banyak percobaan pengumpulan. Silakan tunggu beberapa saat.');
            return;
        }

        RateLimiter::hit('submit-task:' . auth()->id(), 60);

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
            $rules['uploadedFile'] = 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,webp|max:10240';
            $messages['uploadedFile.max'] = 'Ukuran file maksimal 10 MB.';
            $messages['uploadedFile.mimes'] = 'Format file harus berupa PDF, Word, Excel, PPT, Gambar, atau ZIP.';
        }

        if (in_array('text', $allowedTypes)) {
            $rules['textContent'] = 'nullable|string|max:10000';
        }

        if (in_array('link', $allowedTypes)) {
            $rules['linkUrl'] = 'nullable|url|max:500';
            $messages['linkUrl.url'] = 'Format tautan harus berupa URL yang valid (contoh: https://...).';
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
            // Hapus file lama jika ada
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

        $this->canResubmit = (bool) $this->assessment->allow_resubmit;
        $this->isLocked = !$this->canResubmit;

        session()->flash('success', 'Tugas berhasil dikumpulkan! Anda dapat memantau status penilaian dari guru di halaman ini.');
    }

    public function render()
    {
        return view('livewire.siswa.tugas.kerjakan-tugas');
    }
}
