<?php

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentGroup;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonCheckpoint;
use App\Models\LessonProgress;
use App\Models\Submission;
use App\Models\User;
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
        'code' => 'AD',
        'name' => 'Analisis Data',
    ]);

    $this->tp = LearningObjective::create([
        'element_id' => $this->element->id,
        'code' => 'AD-1',
        'title' => 'Pengolahan Data Tabel',
    ]);

    $this->teacher = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    
    // 3 Siswa dalam 1 Kelas: Ketua, Siswa Rajin, Siswa Belum Baca
    $this->leader = User::factory()->create(['name' => 'Ketua Tim', 'role' => 'siswa', 'status' => 'active']);
    $this->studentRajin = User::factory()->create(['name' => 'Siswa Rajin', 'role' => 'siswa', 'status' => 'active']);
    $this->studentMalas = User::factory()->create(['name' => 'Siswa Belum Baca', 'role' => 'siswa', 'status' => 'active']);

    foreach ([$this->leader, $this->studentRajin, $this->studentMalas] as $s) {
        $s->classrooms()->attach($this->classroom->id, [
            'academic_year_id' => $this->year->id,
        ]);
    }

    // Materi dengan 1 Checkpoint
    $this->lesson = Lesson::create([
        'learning_objective_id' => $this->tp->id,
        'created_by' => $this->teacher->id,
        'title' => 'Teknik Analisis Data',
        'code' => 'AD-M1',
        'status' => 'published',
        'completion_rule' => 'checkpoint_and_task',
    ]);
    $this->lesson->classrooms()->attach($this->classroom->id);

    $this->checkpoint = LessonCheckpoint::create([
        'lesson_id' => $this->lesson->id,
        'question' => 'Fungsi utama Spreadsheet?',
        'options' => ['A' => 'Pengolahan Angka', 'B' => 'Desain Grafis', 'C' => 'Coding', 'D' => 'Audio'],
        'correct_answer' => 'A',
        'sort_order' => 1,
    ]);

    // Tugas Kelompok Terikat Materi
    $this->assessment = Assessment::create([
        'element_id' => $this->element->id,
        'learning_objective_id' => $this->tp->id,
        'lesson_id' => $this->lesson->id,
        'created_by' => $this->teacher->id,
        'title' => 'Proyek Kelompok Analisis Data Penjualan',
        'type' => 'tugas',
        'assignment_mode' => 'group',
        'max_group_members' => 3,
        'status' => 'published',
        'submission_types' => ['text'],
    ]);
    $this->assessment->classrooms()->attach($this->classroom->id);

    Schema::enableForeignKeyConstraints();
});

test('ketua yang belum membaca materi tidak dapat membentuk kelompok', function () {
    // Leader belum membaca materi
    Livewire::actingAs($this->leader)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->assertSet('isLocked', true)
        ->assertSet('isLessonLocked', true)
        ->set('newGroupName', 'Kelompok Alfa')
        ->call('createGroup');

    expect(AssessmentGroup::where('assessment_id', $this->assessment->id)->count())->toBe(0);
});

test('ketua tidak dapat memilih anggota yang belum membaca materi saat membuat kelompok', function () {
    // Leader menyelesaikan materi
    LessonProgress::create([
        'student_id' => $this->leader->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);

    // SiswaRajin juga menyelesaikan materi
    LessonProgress::create([
        'student_id' => $this->studentRajin->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);

    // SiswaMalas BELUM membaca materi

    // Leader mencoba membuat kelompok dengan memasukkan SiswaMalas
    Livewire::actingAs($this->leader)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->assertSet('isLessonLocked', false)
        ->set('newGroupName', 'Kelompok Data Super')
        ->set('selectedMemberIds', [$this->studentMalas->id])
        ->call('createGroup')
        ->assertSee('belum membaca/menuntaskan materi pelajaran');

    expect(AssessmentGroup::where('assessment_id', $this->assessment->id)->count())->toBe(0);
});

test('ketua berhasil membentuk kelompok bersama anggota yang sudah membaca materi', function () {
    // Leader & SiswaRajin menyelesaikan materi
    LessonProgress::create([
        'student_id' => $this->leader->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);
    LessonProgress::create([
        'student_id' => $this->studentRajin->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);

    Livewire::actingAs($this->leader)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('newGroupName', 'Kelompok Data Super')
        ->set('selectedMemberIds', [$this->studentRajin->id])
        ->call('createGroup');

    $group = AssessmentGroup::where('assessment_id', $this->assessment->id)->first();
    expect($group)->not->toBeNull();
    expect($group->members()->count())->toBe(2);
});

test('ketua ditolak saat mencoba menambah anggota baru yang belum membaca materi', function () {
    // Buat kelompok yang sudah ada
    LessonProgress::create([
        'student_id' => $this->leader->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);

    $group = AssessmentGroup::create([
        'assessment_id' => $this->assessment->id,
        'classroom_id' => $this->classroom->id,
        'name' => 'Kelompok Alfa',
        'leader_id' => $this->leader->id,
    ]);
    $group->members()->attach($this->leader->id);

    // Coba tambahkan SiswaMalas (yang belum baca materi)
    Livewire::actingAs($this->leader)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->call('addMemberToGroup', $this->studentMalas->id);

    // Anggota tetap hanya ketua
    expect($group->fresh()->members()->count())->toBe(1);
});

test('pengumpulan tugas kelompok berhasil jika seluruh anggota sudah menuntaskan materi', function () {
    // Leader & SiswaRajin menyelesaikan materi
    LessonProgress::create([
        'student_id' => $this->leader->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);
    LessonProgress::create([
        'student_id' => $this->studentRajin->id,
        'lesson_id' => $this->lesson->id,
        'checkpoint_score' => 100,
        'checkpoint_passed_at' => now(),
    ]);

    $group = AssessmentGroup::create([
        'assessment_id' => $this->assessment->id,
        'classroom_id' => $this->classroom->id,
        'name' => 'Kelompok Sukses',
        'leader_id' => $this->leader->id,
    ]);
    $group->members()->attach([$this->leader->id, $this->studentRajin->id]);

    Livewire::actingAs($this->leader)
        ->test(KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('textContent', 'Laporan Proyek Analisis Data Penjualan Kelompok Sukses')
        ->call('submit')
        ->assertHasNoErrors();

    // Submission terbentuk untuk ketua dan anggota kelompok
    expect(Submission::where('assessment_id', $this->assessment->id)->count())->toBeGreaterThanOrEqual(1);
});
