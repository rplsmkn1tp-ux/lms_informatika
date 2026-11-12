<?php

use App\Models\User;
use App\Models\AcademicYear;
use Livewire\Livewire;
use App\Livewire\Admin\AcademicYearManagement;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
});

test('admin can view academic year page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.academic-years'))
        ->assertStatus(200);
});

test('admin can create academic year', function () {
    Livewire::actingAs($this->admin)
        ->test(AcademicYearManagement::class)
        ->set('name', '2026/2027')
        ->set('semester', 'genap')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('academic_years', [
        'name' => '2026/2027',
        'semester' => 'genap',
        'is_active' => false,
    ]);
});

test('admin can set active academic year which deactivates others', function () {
    $ay1 = AcademicYear::create(['name' => 'Year 1', 'semester' => 'ganjil', 'is_active' => true]);
    $ay2 = AcademicYear::create(['name' => 'Year 2', 'semester' => 'genap', 'is_active' => false]);

    Livewire::actingAs($this->admin)
        ->test(AcademicYearManagement::class)
        ->call('setActive', $ay2->id);

    expect($ay1->fresh()->is_active)->toBeFalse();
    expect($ay2->fresh()->is_active)->toBeTrue();
});
