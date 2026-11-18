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
use App\Livewire\Siswa\Tugas\DaftarTugas;
use App\Livewire\Siswa\Tugas\KerjakanTugas;
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
        'code' => 'BK',
        'name' => 'Berpikir Komputasional',
    ]);

    $this->tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'BK-1',
        'title' => 'Algoritma Pencarian',
    ]);

    $this->teacher = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    $this->student = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

    $this->student->classrooms()->attach($this->classroom->id, [
        'academic_year_id' => $this->year->id,
    ]);

    // Buat Materi dengan 1 Soal Checkpoint
    $this->lesson = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'created_by' => $this->teacher->id,
        'title' => 'Konsep Binary Search',
        'code' => 'BS-01',
        'status' => 'published',
        'completion_rule' => 'checkpoint_and_task',
    ]);
    $this->lesson->classrooms()->attach($this->classroom->id);

    $this->checkpoint = LessonCheckpoint::create([
        'lesson_id' => $this->lesson->id,
        'question' => 'Berapa kompleksitas Binary Search?',
        'options' => ['A' => 'O(log N)', 'B' => 'O(N)', 'C' => 'O(N^2)', 'D' => 'O(1)'],
        'correct_answer' => 'A',
        'sort_order' => 1,
    ]);

    // Buat Tugas yang Terikat ke Materi ini
    $this->assessment = Assessment::create([
        'element_id' => $this->element->id,
        'learning_objective_id' => $this->tp->id,
        'lesson_id' => $this->lesson->id,
        'created_by' => $this->teacher->id,
        'title' => 'Praktik Implementasi Binary Search',
        'type' => 'tugas',
        'status' => 'published',
        'submission_types' => ['text'],
    ]);
    $this->assessment->classrooms()->attach($this->classroom->id);

    Schema::enableForeignKeyConstraints();
});

test('siswa yang belum menuntaskan checkpoint materi akan terkunci saat mengakses halaman tugas', function () {
    // Siswa belum membaca materi / belum lulus kuis checkpoint
    Livewire::actingAs($this->student)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->assertSet('isLocked', true)
        ->assertSet('isLessonLocked', true)
        ->assertSee('Tugas Terkunci: Wajib Membaca Materi Terlebih Dahulu')
        ->assertSee('Konsep Binary Search');
});

test('pada menu daftar tugas, tugas terikat materi yang belum dibaca menampilkan badge terkunci', function () {
    Livewire::actingAs($this->student)
        ->test(DaftarTugas::class)
        ->assertSee('Terkunci (Belum Tuntas Materi)')
        ->assertSee('Terkunci: Wajib menuntaskan materi sebelum membuka tugas ini');
});

test('siswa tidak dapat mengumpulkan jawaban saat tugas dalam keadaan terkunci oleh materi', function () {
    Livewire::actingAs($this->student)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('textContent', 'Jawaban jalan pintas tanpa membaca materi')
        ->call('submit')
        ->assertSet('isLocked', true)
        ->assertSet('isLessonLocked', true);

    expect(Submission::where('assessment_id', $this->assessment->id)->count())->toBe(0);
});

test('tugas terbuka dan dapat dikumpulkan setelah siswa lulus kuis checkpoint materi', function () {
    // Siswa menyelesaikan checkpoint materi
    LessonProgress::create([
        'student_id' => $this->student->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);

    Livewire::actingAs($this->student)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->assertSet('isLocked', false)
        ->assertSet('isLessonLocked', false)
        ->set('textContent', 'Jawaban lengkap setelah membaca materi')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Submission::where('assessment_id', $this->assessment->id)->count())->toBe(1);
});
