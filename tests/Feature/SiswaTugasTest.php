<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\Element;
use Livewire\Livewire;
use function Pest\Laravel\seed;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    seed(DatabaseSeeder::class);
    $this->siswa = User::where('email', 'siswa@example.com')->first();
    $this->classroom = $this->siswa->classrooms()->first();
    $this->element = Element::first();
    
    $this->assessment = Assessment::create([
        'title' => 'Tugas 1',
        'type' => 'tugas',
        'status' => 'published',
        'element_id' => $this->element->id,
        'submission_types' => ['text'],
        'max_attempts' => null,
        'created_by' => User::where('role', 'guru')->first()->id,
    ]);

    $this->assessment->classrooms()->attach($this->classroom, [
        'opens_at' => now()->subDay(),
        'due_at' => now()->addDay(),
    ]);
});

test('siswa dapat mengakses halaman daftar tugas', function () {
    $this->actingAs($this->siswa)
        ->get('/tugas')
        ->assertStatus(200)
        ->assertSee($this->assessment->title);
});

test('siswa dapat mengerjakan dan mensubmit tugas', function () {
    Livewire::actingAs($this->siswa)
        ->test(\App\Livewire\Siswa\Tugas\KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('textContent', 'Jawaban teks tugas siswa')
        ->call('submit');
        
    // Cek di database
    $this->assertDatabaseHas('submissions', [
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->siswa->id,
        'status' => 'dikumpulkan',
    ]);
});
