<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\TeachingAssignment;
use App\Models\LearningObjective;
use App\Models\Element;
use App\Models\Assessment;
use Livewire\Livewire;
use App\Livewire\Guru\TpManagement;
use App\Livewire\Guru\ClassroomDetail;
use App\Livewire\Guru\LessonManagement;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    $this->siswa = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    
    $this->year = AcademicYear::create(['name' => '2026', 'is_active' => true]);
    $this->classroom = Classroom::create([
        'name' => 'X RPL 1',
        'join_code' => 'XRPL1-A2B3',
        'academic_year_id' => $this->year->id,
    ]);
    
    $this->element = Element::create(['name' => 'Berpikir Komputasional', 'code' => 'BK']);
});

test('admin and siswa cannot access guru routes', function () {
    $this->actingAs($this->admin)->get(route('guru.tp'))->assertForbidden();
    $this->actingAs($this->siswa)->get(route('guru.tp'))->assertForbidden();
});

test('guru receives 403 when accessing unassigned classroom', function () {
    $this->actingAs($this->guru)
         ->get(route('guru.classroom', $this->classroom->id))
         ->assertForbidden();
});

test('guru can access assigned classroom', function () {
    TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'subject' => 'Informatika'
    ]);

    $this->actingAs($this->guru)
         ->get(route('guru.classroom', $this->classroom->id))
         ->assertOk()
         ->assertSee($this->classroom->join_code);
});

test('guru can regenerate join code', function () {
    TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'subject' => 'Informatika'
    ]);

    Livewire::actingAs($this->guru)
        ->test(ClassroomDetail::class, ['classroom' => $this->classroom])
        ->call('regenerateJoinCode');

    expect($this->classroom->fresh()->join_code)->not->toBe('XRPL1-A2B3');
});

test('guru can copy templates to their own tps', function () {
    $template = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'Template TP',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => true,
        'created_by' => $this->admin->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->call('copyTemplate', $template->id);

    $myTp = LearningObjective::where('created_by', $this->guru->id)->first();
    
    expect($myTp)->not->toBeNull();
    expect($myTp->code)->toBe('BK-1');
    expect($myTp->is_template)->toBeFalse();
});

test('guru cannot edit another gurus tp', function () {
    $guru2 = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    
    $tpGuru2 = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-2',
        'title' => 'TP Guru 2',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $guru2->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->call('edit', $tpGuru2->id)
        ->assertForbidden(); // authorize/policy will throw 403
});

test('guru receives 403 when trying to regenerate code for unassigned classroom', function () {
    Livewire::actingAs($this->guru)
        ->test(ClassroomDetail::class, ['classroom' => $this->classroom])
        ->assertForbidden(); // Mount now checks and fails
});

test('guru receives 403 when trying to toggle join code for unassigned classroom', function () {
    Livewire::actingAs($this->guru)
        ->test(ClassroomDetail::class, ['classroom' => $this->classroom])
        ->assertForbidden();
});

test('guru can toggle join code for assigned classroom', function () {
    TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'subject' => 'Informatika'
    ]);

    Livewire::actingAs($this->guru)
        ->test(ClassroomDetail::class, ['classroom' => $this->classroom])
        ->call('toggleJoinCodeActive')
        ->assertHasNoErrors();

    expect($this->classroom->fresh()->join_code_active)->toBeFalsy();
});

test('guru can create and save new tp', function () {
    Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->set('element_id', $this->element->id)
        ->set('code', 'BK-NEW')
        ->set('title', 'Test Title')
        ->set('description', 'Desc')
        ->set('sort_order', 2)
        ->set('kktp', 80)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('learning_objectives', [
        'code' => 'BK-NEW',
        'created_by' => $this->guru->id
    ]);
});

test('guru receives validation error on empty tp save', function () {
    Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->call('save')
        ->assertHasErrors(['element_id', 'code', 'title']);
});

test('guru can delete their own tp', function () {
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-DEL',
        'title' => 'To be deleted',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->call('delete', $tp->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('learning_objectives', ['id' => $tp->id]);
});

test('guru cannot delete another gurus tp', function () {
    $guru2 = User::factory()->create(['role' => 'guru']);
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-DEL2',
        'title' => 'To be deleted',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $guru2->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->call('delete', $tp->id)
        ->assertForbidden();
});

test('guru can create and save new lesson', function () {
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(LessonManagement::class)
        ->set('element_id', $this->element->id)
        ->set('learning_objective_id', $tp->id)
        ->set('selectedClassrooms', [$this->classroom->id])
        ->set('code', 'L-1')
        ->set('title', 'Test Lesson')
        ->set('summary', 'Desc')
        ->set('sort_order', 1)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('lessons', [
        'code' => 'L-1',
        'created_by' => $this->guru->id
    ]);
});

test('guru receives validation error on empty lesson save', function () {
    Livewire::actingAs($this->guru)
        ->test(LessonManagement::class)
        ->call('save')
        ->assertHasErrors(['learning_objective_id', 'code', 'title']);
});

test('guru cannot edit another gurus lesson', function () {
    $guru2 = User::factory()->create(['role' => 'guru']);
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $guru2->id
    ]);
    $lesson = \App\Models\Lesson::create([
        'learning_objective_id' => $tp->id,
        'code' => 'L-2',
        'title' => 'Other Lesson',
        'sort_order' => 1,
        'status' => 'draft',
        'created_by' => $guru2->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(LessonManagement::class)
        ->call('edit', $lesson->id)
        ->assertForbidden();
});

test('guru can delete their own lesson', function () {
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);
    $lesson = \App\Models\Lesson::create([
        'learning_objective_id' => $tp->id,
        'code' => 'L-DEL',
        'title' => 'To be deleted',
        'sort_order' => 1,
        'status' => 'draft',
        'created_by' => $this->guru->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(LessonManagement::class)
        ->call('delete', $lesson->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
});

test('guru can create and save new assessment', function () {
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\AssessmentManagement::class)
        ->set('learning_objective_id', $tp->id)
        ->set('title', 'Tugas 1')
        ->set('type', 'tugas')
        ->set('instructions', 'Kerjakan')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('assessments', [
        'title' => 'Tugas 1',
        'created_by' => $this->guru->id
    ]);
});

test('guru receives validation error on empty assessment save', function () {
    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\AssessmentManagement::class)
        ->call('save')
        ->assertHasErrors(['learning_objective_id', 'title', 'type']);
});

test('guru cannot edit another gurus assessment', function () {
    $guru2 = User::factory()->create(['role' => 'guru']);
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $guru2->id
    ]);
    $assessment = \App\Models\Assessment::create([
        'learning_objective_id' => $tp->id,
        'title' => 'Tugas 2',
        'type' => 'tugas',
        'status' => 'draft',
        'created_by' => $guru2->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\AssessmentManagement::class)
        ->call('edit', $assessment->id)
        ->assertForbidden();
});

test('guru can delete their own assessment', function () {
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);
    $assessment = \App\Models\Assessment::create([
        'learning_objective_id' => $tp->id,
        'title' => 'Tugas 2',
        'type' => 'tugas',
        'status' => 'draft',
        'created_by' => $this->guru->id
    ]);

    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\AssessmentManagement::class)
        ->call('delete', $assessment->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('assessments', ['id' => $assessment->id]);
});

test('guru can delete tugas from index even if submissions exist', function () {
    $element = Element::create(['code' => 'AD', 'name' => 'Analisis Data']);
    $tp = LearningObjective::create([
        'element_id' => $element->id,
        'code' => 'AD-1',
        'title' => 'Analisis Data 1',
        'created_by' => $this->guru->id
    ]);

    $tugas = Assessment::create([
        'learning_objective_id' => $tp->id,
        'title' => 'Tugas dengan submission',
        'type' => 'tugas',
        'status' => 'published',
        'created_by' => $this->guru->id
    ]);

    // Dummy submission
    \App\Models\Submission::create([
        'assessment_id' => $tugas->id,
        'student_id' => $this->siswa->id,
        'status' => \App\Models\Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\Tugas\Index::class)
        ->call('delete', $tugas->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('assessments', ['id' => $tugas->id]);
    $this->assertDatabaseMissing('submissions', ['assessment_id' => $tugas->id]);
});

test('guru can grade submission of their own student', function () {
    // Setup
    TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'subject' => 'Informatika'
    ]);
    
    // Attach student to classroom
    $this->classroom->students()->attach($this->siswa->id, ['academic_year_id' => $this->year->id]);

    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    $assessment = \App\Models\Assessment::create([
        'learning_objective_id' => $tp->id,
        'title' => 'Tugas 1',
        'type' => 'tugas',
        'status' => 'published',
        'created_by' => $this->guru->id
    ]);

    $submission = \App\Models\Submission::create([
        'assessment_id' => $assessment->id,
        'student_id' => $this->siswa->id,
        'status' => 'dikumpulkan',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\Penilaian::class)
        ->call('openGradingModal', $submission->id)
        ->assertHasNoErrors()
        ->set('score', 90)
        ->set('feedback', 'Bagus')
        ->call('saveGrade')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'score' => 90,
        'status' => 'dinilai'
    ]);
});

test('guru cannot grade submission of student from other classroom', function () {
    $guru2 = User::factory()->create(['role' => 'guru']);
    $classroom2 = Classroom::create([
        'name' => 'X RPL 2',
        'join_code' => 'XRPL2-AAAA',
        'academic_year_id' => $this->year->id,
    ]);
    
    TeachingAssignment::create([
        'teacher_id' => $guru2->id,
        'classroom_id' => $classroom2->id,
        'subject' => 'Informatika'
    ]);
    
    $this->classroom->students()->attach($this->siswa->id, ['academic_year_id' => $this->year->id]);
    
    $tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'TP 1',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    $assessment = \App\Models\Assessment::create([
        'learning_objective_id' => $tp->id,
        'title' => 'Tugas 1',
        'type' => 'tugas',
        'status' => 'published',
        'created_by' => $this->guru->id
    ]);

    $submission = \App\Models\Submission::create([
        'assessment_id' => $assessment->id,
        'student_id' => $this->siswa->id, // in classroom 1
        'status' => 'dikumpulkan',
        'submitted_at' => now(),
    ]);

    // guru2 tries to grade siswa's submission
    Livewire::actingAs($guru2)
        ->test(\App\Livewire\Guru\Penilaian::class)
        ->call('openGradingModal', $submission->id)
        ->assertForbidden();
});

test('tp management supports dropdown filter and groups by element BK and LD', function () {
    $elementLd = Element::firstOrCreate(
        ['code' => 'LD'],
        ['name' => 'Literasi Digital', 'description' => 'LD desc']
    );

    LearningObjective::create([
        'element_id' => $this->element->id, // BK
        'code' => 'BK-1',
        'title' => 'Algoritma Pemrograman',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    LearningObjective::create([
        'element_id' => $elementLd->id, // LD
        'code' => 'LD-1',
        'title' => 'Pencarian Informasi',
        'sort_order' => 1,
        'kktp' => 75,
        'is_template' => false,
        'created_by' => $this->guru->id
    ]);

    $component = Livewire::actingAs($this->guru)
        ->test(TpManagement::class)
        ->assertOk()
        ->assertSee('BK-1')
        ->assertSee('LD-1');

    // Filter to BK
    $component->set('selectedElement', $this->element->id)
        ->assertSee('Algoritma Pemrograman')
        ->assertDontSee('Pencarian Informasi');

    // Filter to LD
    $component->set('selectedElement', $elementLd->id)
        ->assertSee('Pencarian Informasi')
        ->assertDontSee('Algoritma Pemrograman');

    // Test create with element pre-selected
    $component->call('create', $this->element->id);
    expect($component->get('element_id'))->toBe($this->element->id);
    expect($component->get('code'))->toStartWith('BK-');
});

