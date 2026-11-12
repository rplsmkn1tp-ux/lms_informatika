<?php

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Livewire\Livewire;
use App\Livewire\Admin\UserManagement;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
});

test('admin can view users list', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Test']);

    $this->actingAs($this->admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('Siswa Test');
});

test('admin can toggle user status', function () {
    $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('toggleStatus', $guru->id);

    expect($guru->fresh()->status)->toBe('inactive');

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('toggleStatus', $guru->id);

    expect($guru->fresh()->status)->toBe('active');
});

test('admin cannot deactivate themselves', function () {
    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('toggleStatus', $this->admin->id);

    expect($this->admin->fresh()->status)->toBe('active');
});

test('admin can move student to another class in the same year', function () {
    $year = AcademicYear::create(['name' => '2026', 'is_active' => true]);
    $class1 = Classroom::create(['name' => 'Kelas 1', 'join_code' => 'K1-ABCD', 'academic_year_id' => $year->id]);
    $class2 = Classroom::create(['name' => 'Kelas 2', 'join_code' => 'K2-EFGH', 'academic_year_id' => $year->id]);

    $student = User::factory()->create(['role' => 'siswa']);
    $student->classrooms()->attach($class1->id, ['academic_year_id' => $year->id]);

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('openChangeClassModal', $student->id)
        ->set('newClassroomId', $class2->id)
        ->call('changeClass');

    $student->refresh();
    
    // Ensure student is in class2
    expect($student->classrooms()->wherePivot('academic_year_id', $year->id)->count())->toBe(1);
    expect($student->classrooms()->first()->id)->toBe($class2->id);
});
