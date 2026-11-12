<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeColumn;
use App\Models\User;
use App\Livewire\Guru\BukuNilai\Index;
use App\Livewire\Guru\BukuNilai\Spreadsheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->guru = User::create([
        'name' => 'Guru',
        'email' => 'guru@test.com',
        'password' => bcrypt('password'),
        'role' => 'guru',
    ]);

    $this->otherGuru = User::create([
        'name' => 'Guru 2',
        'email' => 'guru2@test.com',
        'password' => bcrypt('password'),
        'role' => 'guru',
    ]);

    $this->siswa = User::create([
        'name' => 'Siswa',
        'email' => 'siswa@test.com',
        'password' => bcrypt('password'),
        'role' => 'siswa',
    ]);

    $this->academicYear = AcademicYear::create([
        'name' => '2023/2024',
        'semester' => 'ganjil',
        'is_active' => true,
    ]);

    $this->classroom = Classroom::create([
        'name' => 'X MIPA 1',
        'grade' => '10',
        'major' => 'MIPA',
        'academic_year_id' => $this->academicYear->id,
        'teacher_id' => $this->guru->id,
        'join_code' => Str::random(6),
    ]);

    $this->classroom->students()->attach($this->siswa->id, ['academic_year_id' => $this->academicYear->id]);

    \App\Models\TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
    ]);

    $this->column = GradeColumn::create([
        'classroom_id' => $this->classroom->id,
        'academic_year_id' => $this->academicYear->id,
        'group' => 'uh',
        'seq' => 1,
        'label' => 'UH 1',
        'max_score' => 100,
    ]);
});

it('can view buku nilai index', function () {
    $this->actingAs($this->guru);
    
    Livewire::test(Index::class)
        ->assertStatus(200)
        ->assertSee('X MIPA 1');
});

it('can view spreadsheet', function () {
    $this->actingAs($this->guru);

    Livewire::test(Spreadsheet::class, ['classroom' => $this->classroom])
        ->assertStatus(200)
        ->assertSee('UH 1');
});

it('cannot view other teacher spreadsheet', function () {
    $this->actingAs($this->otherGuru);

    Livewire::test(Spreadsheet::class, ['classroom' => $this->classroom])
        ->assertForbidden();
});

it('cannot update score below 0', function () {
    $this->actingAs($this->guru);

    Livewire::test(Spreadsheet::class, ['classroom' => $this->classroom])
        ->call('updateScore', $this->column->id, $this->siswa->id, -10)
        ->assertHasErrors(['score']);
});

it('cannot update score above 100', function () {
    $this->actingAs($this->guru);

    Livewire::test(Spreadsheet::class, ['classroom' => $this->classroom])
        ->call('updateScore', $this->column->id, $this->siswa->id, 105)
        ->assertHasErrors(['score']);
});

it('can update score within valid range', function () {
    $this->actingAs($this->guru);

    Livewire::test(Spreadsheet::class, ['classroom' => $this->classroom])
        ->call('updateScore', $this->column->id, $this->siswa->id, 85)
        ->assertHasNoErrors();
        
    $this->assertDatabaseHas('grade_entries', [
        'column_id' => $this->column->id,
        'student_id' => $this->siswa->id,
        'score' => 85
    ]);
});

it('siswa cannot view guru spreadsheet', function () {
    $this->actingAs($this->siswa);

    Livewire::test(Spreadsheet::class, ['classroom' => $this->classroom])
        ->assertForbidden();
});

it('siswa can view their own buku nilai', function () {
    $this->actingAs($this->siswa);

    Livewire::test(\App\Livewire\Siswa\BukuNilai\Show::class)
        ->assertStatus(200)
        ->assertSee('UH 1');
});

it('can export to pdf and excel', function () {
    $this->actingAs($this->guru);

    $responsePdf = $this->get(route('guru.buku-nilai.export.pdf', [$this->classroom, $this->academicYear]));
    $responsePdf->assertStatus(200);
    $responsePdf->assertHeader('content-type', 'application/pdf');

    $responseExcel = $this->get(route('guru.buku-nilai.export.excel', [$this->classroom, $this->academicYear]));
    $responseExcel->assertStatus(200);
    $responseExcel->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
