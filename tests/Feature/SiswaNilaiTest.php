<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\ObjectiveMastery;
use function Pest\Laravel\seed;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    seed(DatabaseSeeder::class);
    $this->siswa = User::where('email', 'siswa@example.com')->first();
    $this->classroom = $this->siswa->classrooms()->first();
    
    $this->element = Element::first();
    $teacher = User::where('role', 'guru')->first();
    $this->tp = LearningObjective::where('element_id', $this->element->id)->first();
    $this->tp->update(['created_by' => $teacher->id]);
});

test('siswa dapat mengakses halaman nilai dan melihat status TP', function () {
    ObjectiveMastery::create([
        'student_id' => $this->siswa->id,
        'learning_objective_id' => $this->tp->id,
        'classroom_id' => $this->classroom->id,
        'best_score' => 80,
        'status' => 'tercapai',
    ]);

    $this->actingAs($this->siswa)
        ->get('/nilai')
        ->assertStatus(200)
        ->assertSee($this->element->name)
        ->assertSee('tercapai')
        ->assertSee('80');
});
