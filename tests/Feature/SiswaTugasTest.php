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
        'type' => 'formatif',
        'status' => 'published',
        'element_id' => $this->element->id,
        'max_attempts' => null,
        'created_by' => User::where('role', 'guru')->first()->id,
    ]);

    $this->assessment->classrooms()->attach($this->classroom, [
        'opens_at' => now()->subDay(),
        'due_at' => now()->addDay(),
    ]);

    $this->question = Question::create([
        'assessment_id' => $this->assessment->id,
        'type' => 'pilihan_ganda',
        'body' => 'Test Question',
        'options' => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'],
        'answer_key' => 'C',
        'points' => 100,
        'sort_order' => 1,
    ]);
});

test('siswa dapat mengakses halaman daftar tugas', function () {
    $this->actingAs($this->siswa)
        ->get('/tugas')
        ->assertStatus(200)
        ->assertSee($this->assessment->title);
});

test('jawaban benar (answer_key) tidak bocor di properti publik atau html halaman kuis saat mengerjakan', function () {
    $component = Livewire::actingAs($this->siswa)
        ->test(\App\Livewire\Siswa\Tugas\KerjakanTugas::class, ['assessment' => $this->assessment]);
        
    $component->assertStatus(200);

    // Pastikan tidak ada "answer_key" dalam response body (HTML dan payload Livewire)
    $response = (string) $component->html();
    
    // We check if "C" or answer_key is leaked in the payload
    $this->assertStringNotContainsString('"answer_key":"C"', $response, 'Answer key bocor di payload Livewire.');
    $this->assertStringNotContainsString("'answer_key' => 'C'", $response);
});

test('siswa dapat mengerjakan dan mensubmit kuis', function () {
    Livewire::actingAs($this->siswa)
        ->test(\App\Livewire\Siswa\Tugas\KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('answers.'.$this->question->id, 'C')
        ->call('submit');
        
    // Cek di database
    $this->assertDatabaseHas('submissions', [
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->siswa->id,
        'status' => 'dinilai',
        'score' => 100
    ]);
});
