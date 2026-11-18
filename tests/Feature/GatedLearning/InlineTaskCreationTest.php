<?php

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\User;
use App\Livewire\Guru\LessonManagement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    Storage::fake('local');

    $this->year = AcademicYear::create([
        'name' => '2026/2027',
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'academic_year_id' => $this->year->id,
        'name' => 'X PPLG 1',
        'join_code' => 'XPPLG1',
    ]);

    $this->element = Element::create([
        'code' => 'TIK',
        'name' => 'Teknologi Informasi dan Komunikasi',
        'phase' => 'E',
    ]);

    $this->tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'TP-TIK-01',
        'title' => 'Pengolahan Kata Terintegrasi',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
        'status' => 'active',
    ]);
    $this->teacher->taughtClassrooms()->attach($this->classroom->id);

    Schema::enableForeignKeyConstraints();
});

test('guru can create inline task with lkpd template and kktp rubric when saving lesson', function () {
    $templateFile = UploadedFile::fake()->create('Template_Surat_Dinas.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

    Livewire::actingAs($this->teacher)
        ->test(LessonManagement::class)
        ->call('createForTp', $this->tp->id)
        ->set('title', 'Modul 1: Aplikasi Perkantoran')
        ->set('code', 'M1.1')
        ->set('summary', 'Membuat surat resmi dan tabel lampiran.')
        ->set('selectedClassrooms', [(string)$this->classroom->id])
        ->set('status', 'published')
        ->set('is_gated', true)
        ->set('completion_rule', 'checkpoint_and_task')
        ->set('checkpoints', [
            [
                'question' => 'Berapa margin standar surat resmi?',
                'options' => [
                    'A' => '4-4-3-3 cm',
                    'B' => '1-1-1-1 cm',
                    'C' => '5-5-5-5 cm',
                    'D' => 'Bebas',
                ],
                'correct_answer' => 'A',
                'explanation' => 'Standar tata naskah dinas.',
                'sort_order' => 1,
            ]
        ])
        ->set('isCreatingNewTask', true)
        ->set('newTaskTitle', 'Tugas Praktik: Format Surat Dinas (.docx)')
        ->set('newTaskInstructions', 'Unduh template surat di bawah ini dan sesuaikan formatnya.')
        ->set('newTaskSubmissionTypes', ['file'])
        ->set('newTaskMaxScore', 100)
        ->set('newTaskKktp', "1. Format Margin & Tata Letak: 30 Poin\n2. Struktur Kop Surat: 35 Poin\n3. Tabel Lampiran: 35 Poin")
        ->set('newTaskTemplateFile', $templateFile)
        ->call('save')
        ->assertHasNoErrors();

    // Verify Lesson is created
    $lesson = Lesson::where('code', 'M1.1')->first();
    expect($lesson)->not->toBeNull();
    expect($lesson->is_gated)->toBeTrue();
    expect($lesson->completion_rule)->toBe('checkpoint_and_task');

    // Verify Assessment is created and linked
    $task = Assessment::where('lesson_id', $lesson->id)->first();
    expect($task)->not->toBeNull();
    expect($task->title)->toBe('Tugas Praktik: Format Surat Dinas (.docx)');
    expect($task->learning_objective_id)->toBe($this->tp->id);
    expect($task->rubric)->toContain('Format Margin & Tata Letak: 30 Poin');
    expect($task->submission_types)->toBe(['file']);

    // Verify template attachment exists
    expect($task->teacher_attachments)->toBeArray();
    expect($task->teacher_attachments)->toHaveCount(1);
    expect($task->teacher_attachments[0]['name'])->toBe('Template_Surat_Dinas.docx');
    Storage::disk('local')->assertExists($task->teacher_attachments[0]['path']);

    // Verify classroom is attached to task
    expect($task->classrooms()->where('classrooms.id', $this->classroom->id)->exists())->toBeTrue();
});

test('inline task validation catches missing title and invalid file types', function () {
    $badFile = UploadedFile::fake()->create('script.exe', 50, 'application/x-msdownload');

    Livewire::actingAs($this->teacher)
        ->test(LessonManagement::class)
        ->call('createForTp', $this->tp->id)
        ->set('title', 'Modul 2: Presentasi')
        ->set('code', 'M2.1')
        ->set('summary', 'Membuat slide presentasi.')
        ->set('selectedClassrooms', [(string)$this->classroom->id])
        ->set('isCreatingNewTask', true)
        ->set('newTaskTitle', '') // Empty title
        ->set('newTaskSubmissionTypes', []) // Empty types
        ->set('newTaskTemplateFile', $badFile) // Invalid mime
        ->call('save')
        ->assertHasErrors(['newTaskTitle', 'newTaskSubmissionTypes', 'newTaskTemplateFile']);
});

test('guru can toggle inline task creator and UI updates accordingly', function () {
    Livewire::actingAs($this->teacher)
        ->test(LessonManagement::class)
        ->call('createForTp', $this->tp->id)
        ->set('title', 'Modul Excel VLOOKUP')
        ->assertSet('isCreatingNewTask', false)
        ->assertSee('+ Buat Tugas Asesmen Langsung di Sini')
        ->assertDontSee('Formulir Tugas Praktik Baru (Terpadu)')
        ->call('toggleCreatingNewTask')
        ->assertSet('isCreatingNewTask', true)
        ->assertSee('Tutup Form Tugas Baru')
        ->assertSee('Formulir Tugas Praktik Baru (Terpadu)')
        ->assertSee('Berkas Lembar Kerja / Template Praktik (LKPD Guru)')
        ->assertSee('Kriteria Ketercapaian TP (KKTP / Rubrik Penilaian)')
        ->call('toggleCreatingNewTask')
        ->assertSet('isCreatingNewTask', false)
        ->assertSee('+ Buat Tugas Asesmen Langsung di Sini')
        ->assertDontSee('Formulir Tugas Praktik Baru (Terpadu)');
});


