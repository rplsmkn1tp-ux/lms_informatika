<?php

namespace App\Livewire\Guru\BukuNilai;

use App\Models\Classroom;
use App\Models\GradeColumn;
use App\Models\GradeEntry;
use App\Models\GradeWeight;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux\Flux;

#[Layout('components.layouts.guru')]
class Spreadsheet extends Component
{
    use AuthorizesRequests;

    public Classroom $classroom;
    public $students = [];
    public $columns = [];
    public $weights = [];
    public $finalGrades = [];

    public array $entriesMap = [];
    public array $columnTaskTitles = [];

    // Settings
    public int $settingUhCount = 3;
    public int $settingTugasCount = 5;
    public int $settingWeightUh = 30;
    public int $settingWeightTugas = 30;
    public int $settingWeightUasMid = 30;
    public int $settingWeightAbsensi = 10;

    // Atur TP Kolom
    public ?int $editingColumnId = null;
    public array $selectedTpIds = [];
    public bool $showTpModal = false;

    public function mount(Classroom $classroom)
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $classroom]);
        $this->classroom = $classroom;
        $this->loadData();
    }

    public function loadData()
    {
        // Sinkronisasi otomatis nilai tugas yang telah dinilai guru ke Buku Nilai
        try {
            app(\App\Services\GradeBookSyncService::class)->syncClassroom($this->classroom);
        } catch (\Exception $e) {
        }

        $this->students = $this->classroom->students()->orderBy('name')->get();
        $this->columns = GradeColumn::where('classroom_id', $this->classroom->id)
            ->where('academic_year_id', $this->classroom->academic_year_id)
            ->with(['learningObjectives', 'learningObjective'])
            ->orderBy('group')
            ->orderBy('seq')
            ->get();
            
        if ($this->columns->isEmpty()) {
            $this->generateDefaultColumns(3, 5);
            $this->columns = GradeColumn::where('classroom_id', $this->classroom->id)
                ->where('academic_year_id', $this->classroom->academic_year_id)
                ->orderBy('group')
                ->orderBy('seq')
                ->get();
        }
        
        $this->settingUhCount = $this->columns->where('group', 'uh')->count();
        $this->settingTugasCount = $this->columns->where('group', 'tugas')->count();

        $this->weights = GradeWeight::where('classroom_id', $this->classroom->id)
            ->where('academic_year_id', $this->classroom->academic_year_id)
            ->first();

        if (!$this->weights) {
            $this->weights = new GradeWeight([
                'weight_uh' => 30,
                'weight_tugas' => 30,
                'weight_uas_mid' => 30,
                'weight_absensi' => 10,
            ]);
        }
        
        $this->settingWeightUh = $this->weights->weight_uh ?? 30;
        $this->settingWeightTugas = $this->weights->weight_tugas ?? 30;
        $this->settingWeightUasMid = $this->weights->weight_uas_mid ?? 30;
        $this->settingWeightAbsensi = $this->weights->weight_absensi ?? 10;

        // Map judul tugas yang terhubung ke kolom
        $this->columnTaskTitles = [];
        try {
            $assessments = \App\Models\Assessment::whereHas('classrooms', function ($q) {
                $q->where('classrooms.id', $this->classroom->id);
            })->get();

            $syncService = app(\App\Services\GradeBookSyncService::class);
            foreach ($assessments as $assessment) {
                $group = $assessment->grade_group ?? 'tugas';
                $seq = $syncService->resolveAssessmentSeq($assessment, $this->classroom->id, $group);
                $key = $group . '_' . $seq;
                if (!isset($this->columnTaskTitles[$key])) {
                    $this->columnTaskTitles[$key] = $assessment->title;
                } else {
                    $this->columnTaskTitles[$key] .= ', ' . $assessment->title;
                }
            }
        } catch (\Exception $e) {
        }

        $this->calculateFinalGrades();
    }

    protected function generateDefaultColumns(int $uhCount, int $tugasCount)
    {
        for ($i = 1; $i <= $uhCount; $i++) {
            GradeColumn::create([
                'classroom_id' => $this->classroom->id,
                'academic_year_id' => $this->classroom->academic_year_id,
                'group' => 'uh',
                'seq' => $i,
                'label' => "UH $i",
                'max_score' => 100,
            ]);
        }
        for ($i = 1; $i <= $tugasCount; $i++) {
            GradeColumn::create([
                'classroom_id' => $this->classroom->id,
                'academic_year_id' => $this->classroom->academic_year_id,
                'group' => 'tugas',
                'seq' => $i,
                'label' => "Tugas $i",
                'max_score' => 100,
            ]);
        }
        GradeColumn::create([
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->classroom->academic_year_id,
            'group' => 'mid',
            'seq' => 1,
            'label' => "MID",
            'max_score' => 100,
        ]);
        GradeColumn::create([
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->classroom->academic_year_id,
            'group' => 'uas',
            'seq' => 1,
            'label' => "UAS",
            'max_score' => 100,
        ]);
    }

    public function saveSettings()
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $this->classroom]);

        // Validate
        $this->validate([
            'settingUhCount' => 'required|integer|min:1|max:10',
            'settingTugasCount' => 'required|integer|min:1|max:10',
            'settingWeightUh' => 'required|integer|min:0|max:100',
            'settingWeightTugas' => 'required|integer|min:0|max:100',
            'settingWeightUasMid' => 'required|integer|min:0|max:100',
            'settingWeightAbsensi' => 'required|integer|min:0|max:100',
        ]);

        if (($this->settingWeightUh + $this->settingWeightTugas + $this->settingWeightUasMid + $this->settingWeightAbsensi) !== 100) {
            $this->addError('settingWeightTotal', 'Total bobot harus 100%');
            return;
        }

        // Update weights
        GradeWeight::updateOrCreate(
            ['classroom_id' => $this->classroom->id, 'academic_year_id' => $this->classroom->academic_year_id],
            [
                'weight_uh' => $this->settingWeightUh,
                'weight_tugas' => $this->settingWeightTugas,
                'weight_uas_mid' => $this->settingWeightUasMid,
                'weight_absensi' => $this->settingWeightAbsensi,
            ]
        );

        // Adjust columns UH
        $currentUhCount = $this->columns->where('group', 'uh')->count();
        if ($this->settingUhCount > $currentUhCount) {
            for ($i = $currentUhCount + 1; $i <= $this->settingUhCount; $i++) {
                GradeColumn::create([
                    'classroom_id' => $this->classroom->id,
                    'academic_year_id' => $this->classroom->academic_year_id,
                    'group' => 'uh',
                    'seq' => $i,
                    'label' => "UH $i",
                    'max_score' => 100,
                ]);
            }
        } elseif ($this->settingUhCount < $currentUhCount) {
            $colsToDelete = GradeColumn::where('classroom_id', $this->classroom->id)
                ->where('academic_year_id', $this->classroom->academic_year_id)
                ->where('group', 'uh')
                ->where('seq', '>', $this->settingUhCount)
                ->get();
                
            foreach($colsToDelete as $col) {
                GradeEntry::where('column_id', $col->id)->delete();
                $col->delete();
            }
        }

        // Adjust columns Tugas
        $currentTugasCount = $this->columns->where('group', 'tugas')->count();
        if ($this->settingTugasCount > $currentTugasCount) {
            for ($i = $currentTugasCount + 1; $i <= $this->settingTugasCount; $i++) {
                GradeColumn::create([
                    'classroom_id' => $this->classroom->id,
                    'academic_year_id' => $this->classroom->academic_year_id,
                    'group' => 'tugas',
                    'seq' => $i,
                    'label' => "Tugas $i",
                    'max_score' => 100,
                ]);
            }
        } elseif ($this->settingTugasCount < $currentTugasCount) {
            $colsToDelete = GradeColumn::where('classroom_id', $this->classroom->id)
                ->where('academic_year_id', $this->classroom->academic_year_id)
                ->where('group', 'tugas')
                ->where('seq', '>', $this->settingTugasCount)
                ->get();
                
            foreach($colsToDelete as $col) {
                GradeEntry::where('column_id', $col->id)->delete();
                $col->delete();
            }
        }

        Flux::modal('settings-modal')->close();
        Flux::toast('Pengaturan berhasil disimpan');
        
        $this->loadData();
    }

    public function calculateFinalGrades()
    {
        $service = new GradeCalculationService();
        $entries = GradeEntry::whereIn('column_id', $this->columns->pluck('id'))->get();
        
        $this->entriesMap = [];
        foreach ($entries as $entry) {
            $this->entriesMap[$entry->column_id . '_' . $entry->student_id] = $entry->score;
        }

        $this->finalGrades = [];

        foreach ($this->students as $student) {
            $gradeData = ['uh' => [], 'tugas' => [], 'mid' => [], 'uas' => []];
            foreach ($this->columns as $col) {
                $score = $this->entriesMap[$col->id . '_' . $student->id] ?? null;
                $gradeData[$col->group][] = $score;
            }

            $this->finalGrades[$student->id] = $service->calculateFinalGrade(
                $student->id,
                $this->classroom,
                $this->classroom->academicYear,
                $gradeData
            );
        }
    }

    public function updateScore($columnId, $studentId, $score)
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $this->classroom]);
        
        if ($score === '') {
            $score = null;
        }

        if ($score !== null) {
            $score = (float) $score;
            if ($score < 0 || $score > 100) {
                $this->addError('score', 'Nilai harus 0-100');
                return;
            }
        }

        GradeEntry::updateOrCreate(
            ['column_id' => $columnId, 'student_id' => $studentId],
            ['score' => $score]
        );

        // Sinkronisasi dua arah ke submission tugas
        try {
            app(\App\Services\GradeBookSyncService::class)->syncGradeEntryToSubmission($columnId, $studentId, $score);
        } catch (\Exception $e) {
        }

        // Sinkronisasi ketercapaian TP untuk kolom ini
        try {
            $col = GradeColumn::find($columnId);
            if ($col) {
                $tpIds = $col->getAllLearningObjectiveIds();
                foreach ($tpIds as $tpId) {
                    app(\App\Services\ObjectiveMasteryService::class)->updateMastery((int) $studentId, (int) $tpId, (int) $this->classroom->id);
                }
            }
        } catch (\Exception $e) {
        }

        $this->loadData();
    }

    public function openTpModal(int $columnId)
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $this->classroom]);
        $col = GradeColumn::with('learningObjectives')->find($columnId);
        if (!$col) {
            return;
        }

        $this->editingColumnId = $col->id;
        $this->selectedTpIds = $col->getAllLearningObjectiveIds();
        $this->showTpModal = true;
    }

    public function closeTpModal()
    {
        $this->showTpModal = false;
        $this->editingColumnId = null;
        $this->selectedTpIds = [];
        $this->resetValidation();
    }

    public function saveColumnTp()
    {
        $this->authorize('manageClassroom', [\App\Models\GradeColumn::class, $this->classroom]);
        $col = GradeColumn::find($this->editingColumnId);
        if (!$col) {
            return;
        }

        $this->validate([
            'selectedTpIds' => 'required|array|min:1',
            'selectedTpIds.*' => 'exists:learning_objectives,id',
        ], [
            'selectedTpIds.min' => 'Pilih minimal satu Tujuan Pembelajaran (TP).',
        ]);

        $col->syncLearningObjectives($this->selectedTpIds);

        // Jika kolom berkaitan dengan tugas LMS kelas ini, sinkronisasikan juga
        $assessments = \App\Models\Assessment::whereHas('classrooms', fn($q) => $q->where('classrooms.id', $this->classroom->id))->get();
        $syncService = app(\App\Services\GradeBookSyncService::class);
        foreach ($assessments as $ass) {
            $seq = $syncService->resolveAssessmentSeq($ass, $this->classroom->id, $col->group);
            if (($ass->grade_group ?: 'tugas') === $col->group && $seq === $col->seq) {
                $ass->syncLearningObjectives($this->selectedTpIds);
            }
        }

        $this->closeTpModal();
        $this->loadData();
    }

    public function render()
    {
        $elements = \App\Models\Element::with(['learningObjectives' => function($q) {
            $q->orderBy('sort_order');
        }])->orderBy('code')->get();

        $tpsMap = \App\Models\LearningObjective::all()->keyBy('id');
        $editingColumn = $this->editingColumnId ? GradeColumn::with('learningObjectives')->find($this->editingColumnId) : null;

        return view('livewire.guru.buku-nilai.spreadsheet', [
            'elements' => $elements,
            'tpsMap' => $tpsMap,
            'editingColumn' => $editingColumn,
        ]);
    }
}
