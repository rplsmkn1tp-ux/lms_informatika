<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Assessment;
use App\Models\Submission;
use Livewire\Livewire;
use App\Livewire\Siswa\Tugas\KerjakanTugas;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    
    $this->academicYear = \App\Models\AcademicYear::create([
        'name' => '2023/2024',
        'is_active' => true
    ]);
    
    $this->classroom = Classroom::create([
        'name' => 'Kelas X',
        'academic_year_id' => $this->academicYear->id,
        'join_code' => 'ABC12345'
    ]);

    $this->otherClassroom = Classroom::create([
        'name' => 'Kelas XI',
        'academic_year_id' => $this->academicYear->id,
        'join_code' => 'XYZ12345'
    ]);
    
    $this->student = User::factory()->create(['role' => 'siswa']);
    $this->student->classrooms()->attach($this->classroom->id, [
        'academic_year_id' => $this->academicYear->id
    ]);

    $this->otherStudent = User::factory()->create(['role' => 'siswa']);
    $this->otherStudent->classrooms()->attach($this->otherClassroom->id, [
        'academic_year_id' => $this->academicYear->id
    ]);

    $this->assessment = Assessment::create([
        'title' => 'Tugas 1',
        'type' => 'tugas',
        'status' => 'published',
        'created_by' => User::factory()->create(['role' => 'guru'])->id
    ]);

    $this->assessment->classrooms()->attach($this->classroom->id, [
        'opens_at' => now()->subDay(),
        'due_at' => now()->addDays(2),
    ]);
    
    Schema::enableForeignKeyConstraints();
});

test('siswa cannot access assessment assigned to other class', function () {
    $this->actingAs($this->otherStudent);

    // Try to access as student not in classroom
    $response = $this->get(route('siswa.tugas.kerjakan', $this->assessment->id));

    $response->assertStatus(403);
});

test('siswa cannot edit submission after it is graded', function () {
    $this->actingAs($this->student);

    // Create a graded submission
    $submission = clone Submission::create([
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->student->id,
        'attempt' => 1,
        'status' => 'dinilai',
        'score' => 90
    ]);

    Livewire::test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->call('submit')
        ->assertNotDispatched('success');
    
    // Check it's still "dinilai"
    $this->assertEquals('dinilai', $submission->fresh()->status);
});

test('siswa cannot see other students submissions', function () {
    // Siswa 2 is in same class
    $this->otherStudent->classrooms()->sync([$this->classroom->id => ['academic_year_id' => $this->academicYear->id]]);

    // Other student has a submission
    Submission::create([
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->otherStudent->id,
        'attempt' => 1,
        'status' => 'dikerjakan',
    ]);

    $this->actingAs($this->student);

    $component = Livewire::test(KerjakanTugas::class, ['assessment' => $this->assessment]);
    
    // Ensure student does not see other student's submission (submission should be null)
    $this->assertNull($component->get('submission'));
});

test('siswa can submit assessment successfully', function () {
    $this->actingAs($this->student);

    Livewire::test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('textContent', 'Jawaban teks tugas')
        ->call('submit');

    $this->assertTrue(Submission::where('student_id', $this->student->id)->where('status', 'dikumpulkan')->exists());
});
