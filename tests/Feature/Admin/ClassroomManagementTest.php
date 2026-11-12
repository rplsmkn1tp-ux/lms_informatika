<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Livewire\Livewire;
use App\Livewire\Admin\ClassroomManagement;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->year = AcademicYear::create(['name' => '2026', 'is_active' => true]);
});

test('admin can create classroom with valid join code', function () {
    Livewire::actingAs($this->admin)
        ->test(ClassroomManagement::class)
        ->set('academic_year_id', $this->year->id)
        ->set('name', 'X RPL 1')
        ->set('grade', 10)
        ->set('major', 'RPL')
        ->set('weekly_lesson_target', 4)
        ->call('save');

    $classroom = Classroom::first();
    expect($classroom->name)->toBe('X RPL 1')
        ->and($classroom->join_code)->not->toBeNull()
        ->and($classroom->join_code)->not->toMatch('/[O0I1]/');
});

test('admin can regenerate join code', function () {
    $classroom = Classroom::create(['name' => 'Kelas 1', 'join_code' => 'K1-ABCD', 'academic_year_id' => $this->year->id]);
    
    Livewire::actingAs($this->admin)
        ->test(ClassroomManagement::class)
        ->call('regenerateJoinCode', $classroom->id);

    expect($classroom->fresh()->join_code)->not->toBe('K1-ABCD');
});

test('admin can toggle join code active status', function () {
    $classroom = Classroom::create(['name' => 'Kelas 1', 'join_code' => 'K1-ABCD', 'academic_year_id' => $this->year->id, 'join_code_active' => true]);
    
    Livewire::actingAs($this->admin)
        ->test(ClassroomManagement::class)
        ->call('toggleJoinCodeActive', $classroom->id);

    expect($classroom->fresh()->join_code_active)->toBeFalsy();
});

test('admin cannot delete classroom with students', function () {
    $classroom = Classroom::create(['name' => 'Kelas 1', 'join_code' => 'K1-ABCD', 'academic_year_id' => $this->year->id]);
    $student = User::factory()->create(['role' => 'siswa']);
    $student->classrooms()->attach($classroom->id, ['academic_year_id' => $this->year->id]);

    Livewire::actingAs($this->admin)
        ->test(ClassroomManagement::class)
        ->call('deleteClass', $classroom->id);

    expect(Classroom::find($classroom->id))->not->toBeNull();
});

test('student registration fails with inactive join code', function () {
    $classroom = Classroom::create([
        'name' => 'Kelas 1', 'join_code' => 'K1-ABCD', 
        'academic_year_id' => $this->year->id, 'join_code_active' => false
    ]);

    $response = $this->post('/register', [
        'name' => 'Siswa Baru',
        'email' => 'siswa@baru.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'siswa',
        'nisn' => '1234567890',
        'class_code' => 'K1-ABCD',
    ]);

    $response->assertSessionHasErrors('class_code');
});
