<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\TeachingAssignment;
use Livewire\Livewire;
use App\Livewire\Admin\TeachingAssignment as TeachingAssignmentComponent;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->year = AcademicYear::create(['name' => '2026', 'is_active' => true]);
    $this->classroom = Classroom::create(['name' => 'Kelas 1', 'join_code' => 'K1', 'academic_year_id' => $this->year->id]);
    $this->guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
});

test('admin can create teaching assignment', function () {
    Livewire::actingAs($this->admin)
        ->test(TeachingAssignmentComponent::class)
        ->set('teacher_id', $this->guru->id)
        ->set('classroom_id', $this->classroom->id)
        ->set('subject', 'Matematika')
        ->call('save');

    expect(TeachingAssignment::count())->toBe(1);
    expect(TeachingAssignment::first()->subject)->toBe('Matematika');
});

test('cannot create duplicate teaching assignment', function () {
    TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'subject' => 'Matematika'
    ]);

    Livewire::actingAs($this->admin)
        ->test(TeachingAssignmentComponent::class)
        ->set('teacher_id', $this->guru->id)
        ->set('classroom_id', $this->classroom->id)
        ->set('subject', 'Matematika')
        ->call('save')
        ->assertHasErrors('subject');

    expect(TeachingAssignment::count())->toBe(1);
});

test('admin can delete teaching assignment', function () {
    $assignment = TeachingAssignment::create([
        'teacher_id' => $this->guru->id,
        'classroom_id' => $this->classroom->id,
        'subject' => 'Matematika'
    ]);

    Livewire::actingAs($this->admin)
        ->test(TeachingAssignmentComponent::class)
        ->call('deleteAssignment', $assignment->id);

    expect(TeachingAssignment::count())->toBe(0);
});
