<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use Livewire\Livewire;
use App\Livewire\Siswa\Belajar\MateriDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    
    $this->student = User::factory()->create(['role' => 'siswa']);
    
    $this->academicYear = \App\Models\AcademicYear::create([
        'name' => '2023/2024',
        'is_active' => true
    ]);
    
    $this->classroom = Classroom::create([
        'name' => 'Kelas X',
        'academic_year_id' => $this->academicYear->id,
        'join_code' => 'ABC12345'
    ]);
    $this->student->classrooms()->attach($this->classroom->id, [
        'academic_year_id' => $this->academicYear->id
    ]);

    $this->element = Element::create([
        'code' => 'BK',
        'name' => 'Berpikir Komputasional',
        'phase' => 'E',
        'sort_order' => 1
    ]);

    $this->tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'X.BK.1',
        'title' => 'Memahami proposisi',
        'sort_order' => 1
    ]);

    $this->teacher = User::factory()->create(['role' => 'guru']);

    $this->lesson = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'code' => '1.1',
        'title' => 'Proposisi',
        'content' => 'Isi',
        'status' => 'published',
        'sort_order' => 1,
        'created_by' => $this->teacher->id
    ]);
    
    Schema::enableForeignKeyConstraints();
});

test('siswa can access available lesson', function () {
    $this->lesson->classrooms()->attach($this->classroom->id, [
        'available_from' => now()->subDay()
    ]);

    $response = $this->actingAs($this->student)
        ->get(route('siswa.belajar.materi', [
            'element' => $this->element->code,
            'tp' => $this->tp->code,
            'lesson' => $this->lesson->code
        ]));

    $response->assertStatus(200);
});

test('siswa cannot access locked lesson (future date)', function () {
    $this->lesson->classrooms()->attach($this->classroom->id, [
        'available_from' => now()->addDays(2)
    ]);

    $response = $this->actingAs($this->student)
        ->get(route('siswa.belajar.materi', [
            'element' => $this->element->code,
            'tp' => $this->tp->code,
            'lesson' => $this->lesson->code
        ]));

    $response->assertStatus(403);
});

test('siswa cannot access lesson not assigned to their class', function () {
    // No attachment to class
    $response = $this->actingAs($this->student)
        ->get(route('siswa.belajar.materi', [
            'element' => $this->element->code,
            'tp' => $this->tp->code,
            'lesson' => $this->lesson->code
        ]));

    $response->assertStatus(404);
});

test('siswa cannot access draft lesson', function () {
    $this->lesson->update(['status' => 'draft']);
    $this->lesson->classrooms()->attach($this->classroom->id, [
        'available_from' => now()->subDay()
    ]);

    $response = $this->actingAs($this->student)
        ->get(route('siswa.belajar.materi', [
            'element' => $this->element->code,
            'tp' => $this->tp->code,
            'lesson' => $this->lesson->code
        ]));

    $response->assertStatus(404);
});

test('purifier cleans malicious scripts from lesson content', function () {
    $this->lesson->update(['content' => '<script>alert("XSS")</script><p>Aman</p>']);
    $this->lesson->classrooms()->attach($this->classroom->id, [
        'available_from' => now()->subDay()
    ]);

    $response = $this->actingAs($this->student)
        ->get(route('siswa.belajar.materi', [
            'element' => $this->element->code,
            'tp' => $this->tp->code,
            'lesson' => $this->lesson->code
        ]));

    $response->assertStatus(200);
    $response->assertSee('Aman');
    $response->assertDontSee('<script>alert("XSS")</script>', false);
});
