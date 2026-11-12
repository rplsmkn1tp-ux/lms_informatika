<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\AcademicYear;
use App\Livewire\GlobalSearch;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

test('global search component is displayed on admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

    actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSeeLivewire(GlobalSearch::class);
});

test('global search component is not displayed on guru dashboard', function () {
    $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);

    actingAs($guru)
        ->get('/guru')
        ->assertOk()
        ->assertDontSeeLivewire(GlobalSearch::class);
});

test('global search component is not displayed on siswa beranda', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

    actingAs($siswa)
        ->get('/beranda')
        ->assertOk()
        ->assertDontSeeLivewire(GlobalSearch::class);
});

test('admin can search for users and classrooms', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Super Administrator']);
    $guru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Santoso Guru']);
    $year = AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $class = Classroom::create(['name' => 'X-RPL-1', 'join_code' => 'XRPL001', 'academic_year_id' => $year->id, 'grade' => '10']);

    Livewire::actingAs($admin)
        ->test(GlobalSearch::class, ['role' => 'admin'])
        ->set('query', 'Budi')
        ->assertSee('Budi Santoso Guru')
        ->set('query', 'XRPL001')
        ->assertSee('Kelas X-RPL-1');
});

test('guru can search for their assignments and classrooms', function () {
    $guru = User::factory()->create(['role' => 'guru', 'name' => 'Guru Informatika']);
    $year = AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $class = Classroom::create(['name' => 'XI-TKJ-2', 'join_code' => 'TKJ002', 'academic_year_id' => $year->id, 'grade' => '11']);
    
    // Assign classroom to teacher
    $class->teachingAssignments()->create(['teacher_id' => $guru->id, 'academic_year_id' => $year->id]);

    $task = Assessment::create([
        'title' => 'Tugas Algoritma Dasar',
        'created_by' => $guru->id,
        'status' => 'published',
        'type' => 'tugas',
    ]);
    $task->classrooms()->attach($class->id);

    Livewire::actingAs($guru)
        ->test(GlobalSearch::class, ['role' => 'guru'])
        ->set('query', 'Algoritma')
        ->assertSee('Tugas Algoritma Dasar')
        ->set('query', 'TKJ-2')
        ->assertSee('Kelas XI-TKJ-2');
});

test('siswa can search for assignments in their classroom', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Pintar']);
    $year = AcademicYear::create(['name' => '2026/2027', 'semester' => 'ganjil', 'is_active' => true]);
    $class = Classroom::create(['name' => 'X-SIJA-1', 'join_code' => 'SIJA001', 'academic_year_id' => $year->id, 'grade' => '10']);
    
    $siswa->classrooms()->attach($class->id, ['academic_year_id' => $year->id]);

    $task = Assessment::create([
        'title' => 'Tugas Basis Data Relasional',
        'status' => 'published',
        'type' => 'tugas',
        'created_by' => $siswa->id,
    ]);
    $task->classrooms()->attach($class->id);

    Livewire::actingAs($siswa)
        ->test(GlobalSearch::class, ['role' => 'siswa'])
        ->set('query', 'Basis Data')
        ->assertSee('Tugas Basis Data Relasional');
});
