<?php

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonCheckpoint;
use App\Models\LessonProgress;
use App\Models\Submission;
use App\Models\User;
use App\Livewire\Siswa\Belajar\MateriDetail;
use App\Livewire\Siswa\Belajar\DaftarTp;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();

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
        'title' => 'Aplikasi Pengolah Kata dan Angka',
        'description' => 'Mampu memanfaatkan aplikasi perkantoran untuk dokumentasi dan analisis data.',
    ]);

    $this->teacher = User::factory()->create([
        'role' => 'guru',
        'status' => 'active',
    ]);

    $this->student = User::factory()->create([
        'role' => 'siswa',
        'status' => 'active',
    ]);

    // Enroll student in active classroom
    $this->student->classrooms()->attach($this->classroom->id, [
        'academic_year_id' => $this->year->id,
    ]);

    // Setup 2 chained lessons
    $this->lesson1 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M1',
        'title' => 'Pengenalan Microsoft Word',
        'content' => 'Modul materi dasar pembuatan dokumen dan format teks.',
        'sort_order' => 1,
        'status' => 'published',
        'is_gated' => true,
        'completion_rule' => 'checkpoint_only',
        'created_by' => $this->teacher->id,
    ]);
    $this->lesson1->classrooms()->attach($this->classroom->id);

    $this->checkpoint1 = LessonCheckpoint::create([
        'lesson_id' => $this->lesson1->id,
        'question' => 'Kombinasi tombol keyboard untuk menyimpan dokumen adalah?',
        'options' => [
            ['key' => 'A', 'text' => 'Ctrl + P'],
            ['key' => 'B', 'text' => 'Ctrl + S'],
            ['key' => 'C', 'text' => 'Ctrl + C'],
            ['key' => 'D', 'text' => 'Ctrl + V'],
        ],
        'correct_answer' => 'B',
        'explanation' => 'Ctrl + S adalah shortcut standar untuk Save dokumen.',
        'sort_order' => 1,
    ]);

    $this->lesson2 = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => 'M2',
        'title' => 'Format Paragraf & Tabel Word',
        'content' => 'Materi lanjutan pembuatan tabel dan styling dokumen.',
        'sort_order' => 2,
        'status' => 'published',
        'is_gated' => true,
        'completion_rule' => 'checkpoint_only',
        'prerequisite_lesson_id' => $this->lesson1->id,
        'created_by' => $this->teacher->id,
    ]);
    $this->lesson2->classrooms()->attach($this->classroom->id);

    Schema::enableForeignKeyConstraints();
});

test('student can access first lesson without prerequisites', function () {
    $this->actingAs($this->student);

    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson1,
    ])
    ->assertStatus(200)
    ->assertSee('Pengenalan Microsoft Word')
    ->assertSee('Kuis Pemantik Literasi');
});

test('student is blocked with 403 when trying to access locked second lesson directly', function () {
    $this->actingAs($this->student);

    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson2,
    ])
    ->assertStatus(403);
});

test('student answering checkpoint quiz incorrectly fails verification and next lesson remains locked', function () {
    $this->actingAs($this->student);

    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson1,
    ])
    ->set('checkpointAnswers', [$this->checkpoint1->id => 'A']) // Wrong: Ctrl + P
    ->call('submitCheckpoints')
    ->assertHasNoErrors()
    ->assertSee('Skor Anda: 0%')
    ->assertSee('Silakan baca ulang modul materi dan coba lagi');

    // Verify database state: progress saved, but checkpoint not passed
    $progress = LessonProgress::where('student_id', $this->student->id)
        ->where('lesson_id', $this->lesson1->id)
        ->first();

    expect($progress)->not->toBeNull();
    expect($progress->checkpoint_score)->toBe(0);
    expect($progress->hasPassedCheckpoint())->toBeFalse();
    expect($progress->completed_at)->toBeNull();

    // Lesson 1 is not completed, Lesson 2 remains locked
    expect($this->lesson1->isCompletedBy($this->student))->toBeFalse();
    expect($this->lesson2->isUnlockedFor($this->student))->toBeFalse();
});

test('student answering checkpoint 100% correctly passes and unlocks subsequent lesson', function () {
    $this->actingAs($this->student);

    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson1,
    ])
    ->set('checkpointAnswers', [$this->checkpoint1->id => 'B']) // Correct: Ctrl + S
    ->call('submitCheckpoints')
    ->assertHasNoErrors()
    ->assertSee('Validasi literasi Anda tuntas');

    // Verify database state
    $progress = LessonProgress::where('student_id', $this->student->id)
        ->where('lesson_id', $this->lesson1->id)
        ->first();

    expect($progress)->not->toBeNull();
    expect($progress->checkpoint_score)->toBe(100);
    expect($progress->hasPassedCheckpoint())->toBeTrue();
    expect($progress->completed_at)->not->toBeNull();

    // Lesson 1 is completed
    expect($this->lesson1->isCompletedBy($this->student))->toBeTrue();

    // Lesson 2 is now unlocked!
    expect($this->lesson2->isUnlockedFor($this->student))->toBeTrue();

    // Student can now visit Lesson 2 without 403 error!
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson2,
    ])
    ->assertStatus(200)
    ->assertSee('Format Paragraf & Tabel Word');
});

test('hybrid lesson requires both checkpoint quiz and practical task submission', function () {
    // Configure lesson1 as hybrid (checkpoint_and_task)
    $this->lesson1->update(['completion_rule' => 'checkpoint_and_task']);

    // Create linked practical assessment for lesson 1
    $task = Assessment::create([
        'learning_objective_id' => $this->tp->id,
        'element_id' => $this->element->id,
        'lesson_id' => $this->lesson1->id,
        'type' => 'tugas',
        'title' => 'Tugas Praktik Dokumen Profil Usaha',
        'status' => 'published',
        'created_by' => $this->teacher->id,
    ]);

    $this->actingAs($this->student);

    // Step 1: Student answers checkpoint 100% correctly
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson1,
    ])
    ->set('checkpointAnswers', [$this->checkpoint1->id => 'B'])
    ->call('submitCheckpoints')
    ->assertSee('Validasi literasi Anda tuntas');

    // Checkpoint is passed, BUT task not yet submitted
    $progress = LessonProgress::where('student_id', $this->student->id)
        ->where('lesson_id', $this->lesson1->id)
        ->first();
    expect($progress->hasPassedCheckpoint())->toBeTrue();
    expect($this->lesson1->isCompletedBy($this->student))->toBeFalse();
    expect($this->lesson2->isUnlockedFor($this->student))->toBeFalse();

    // Step 2: Student submits the practical assignment
    Submission::create([
        'assessment_id' => $task->id,
        'student_id' => $this->student->id,
        'file_path' => 'submissions/profil_usaha.docx',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    // Now lesson1 criteria is satisfied!
    expect($this->lesson1->isCompletedBy($this->student))->toBeTrue();
    expect($this->lesson2->isUnlockedFor($this->student))->toBeTrue();

    // Student can now access lesson 2!
    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson2,
    ])
    ->assertStatus(200);
});

test('student sees locked indicator in daftar tp for gated subsequent lessons', function () {
    $this->actingAs($this->student);

    Livewire::test(DaftarTp::class, [
        'element' => $this->element,
    ])
    ->assertStatus(200)
    ->assertSee('M1')
    ->assertSee('Pengenalan Microsoft Word')
    ->assertSee('M2')
    ->assertSee('Format Paragraf & Tabel Word')
    ->assertSee('🔒 Selesaikan prasyarat: "Pengenalan Microsoft Word"', false);
});

test('teacher can preview locked lesson without being blocked by 403', function () {
    $this->actingAs($this->teacher);

    Livewire::test(MateriDetail::class, [
        'element' => $this->element,
        'tp' => $this->tp,
        'lesson' => $this->lesson2,
    ])
    ->assertStatus(200)
    ->assertSee('Format Paragraf & Tabel Word')
    ->assertSee('Mode Pratinjau Guru');
});
