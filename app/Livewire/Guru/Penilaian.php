<?php

namespace App\Livewire\Guru;

use App\Models\Submission;
use App\Services\ObjectiveMasteryService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.guru')]
class Penilaian extends Component
{
    use WithPagination;

    public $showGradingModal = false;
    public $selectedSubmission = null;
    
    // Form fields
    public $score;
    public $feedback;

    protected $rules = [
        'score' => 'required|numeric|min:0|max:100',
        'feedback' => 'nullable|string',
    ];

    private function checkAccess($submission)
    {
        $classroomsIds = auth()->user()->teachingAssignments()->pluck('classroom_id');
        $studentInClass = $submission->student->classrooms()->whereIn('classrooms.id', $classroomsIds)->exists();
        
        if (!$studentInClass) {
            abort(403, 'Anda tidak memiliki akses ke tugas ini.');
        }
    }

    public function openGradingModal($submissionId)
    {
        $this->selectedSubmission = Submission::with(['student', 'assessment.learningObjective', 'answers'])->findOrFail($submissionId);
        $this->checkAccess($this->selectedSubmission);
        $this->score = $this->selectedSubmission->score;
        $this->feedback = $this->selectedSubmission->feedback;
        $this->showGradingModal = true;
    }

    public function closeGradingModal()
    {
        $this->showGradingModal = false;
        $this->selectedSubmission = null;
        $this->reset(['score', 'feedback']);
    }

    public function saveGrade(ObjectiveMasteryService $masteryService)
    {
        $this->validate();

        $this->selectedSubmission->update([
            'score' => $this->score,
            'feedback' => $this->feedback,
            'status' => Submission::STATUS_GRADED,
            'graded_by' => auth()->id(),
        ]);

        // Hitung ulang ketercapaian TP
        // Kita butuh classroom_id. Karena kita tahu ini tugas dari kelas mana?
        // submission -> assessment -> classrooms? 
        // Lebih baik cari classroom dari tabel classroom_student atau ambil dari context assignment.
        // Wait, submission_answers/submission punya student_id dan assessment_id.
        // Untuk mencari classroom_id, student itu ada di kelas apa pada tahun ajaran aktif?
        // Asumsi student terdaftar di satu kelas pada semester aktif, atau kita bisa dapatkan dari TeachingAssignment yang nyambung ke guru.
        $classroomsIds = auth()->user()->teachingAssignments()->pluck('classroom_id');
        $studentClassroom = $this->selectedSubmission->student->classrooms()->whereIn('classrooms.id', $classroomsIds)->first();
        
        if ($studentClassroom) {
            $tpIds = $this->selectedSubmission->assessment->getAllLearningObjectiveIds();
            foreach ($tpIds as $tpId) {
                try {
                    $masteryService->updateMastery(
                        $this->selectedSubmission->student_id,
                        $tpId,
                        $studentClassroom->id
                    );
                } catch (\Exception $e) {
                }
            }

            try {
                app(\App\Services\GradeBookSyncService::class)->syncSubmission($this->selectedSubmission, $studentClassroom->id);
            } catch (\Exception $e) {
            }
        }

        session()->flash('success', 'Nilai berhasil disimpan.');
        $this->closeGradingModal();
    }

    public function downloadFile($submissionId)
    {
        $submission = Submission::findOrFail($submissionId);
        $this->checkAccess($submission);
        
        if ($submission->file_path && Storage::exists($submission->file_path)) {
            return Storage::download($submission->file_path);
        }
        
        session()->flash('error', 'File tidak ditemukan.');
    }

    public function render()
    {
        // Hanya ambil tugas untuk guru ini (sesuai kelas yang diajar)
        $teacher = auth()->user();
        $classroomsIds = $teacher->teachingAssignments()->pluck('classroom_id');

        $submissions = Submission::with(['assessment.learningObjective', 'student'])
            ->where('status', Submission::STATUS_SUBMITTED)
            ->whereHas('student.classrooms', function ($q) use ($classroomsIds) {
                $q->whereIn('classrooms.id', $classroomsIds);
            })
            ->latest('submitted_at')
            ->paginate(15);

        return view('livewire.guru.penilaian', compact('submissions'));
    }
}



