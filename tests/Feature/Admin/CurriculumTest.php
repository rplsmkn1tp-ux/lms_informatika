<?php

use App\Models\User;
use App\Models\Element;
use App\Models\LearningObjective;
use Livewire\Livewire;
use App\Livewire\Admin\CurriculumManagement;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->element = Element::create([
        'code' => 'BK',
        'name' => 'Berpikir Komputasional',
        'phase' => 'E'
    ]);
});

test('admin can view curriculum page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.curriculum'))
        ->assertStatus(200);
});

test('admin can update element description', function () {
    Livewire::actingAs($this->admin)
        ->test(CurriculumManagement::class)
        ->call('editElement', $this->element->id)
        ->set('elementDescription', 'Deskripsi baru BK')
        ->call('saveElement')
        ->assertHasNoErrors();

    expect($this->element->fresh()->description)->toBe('Deskripsi baru BK');
});

test('admin can add master TP', function () {
    Livewire::actingAs($this->admin)
        ->test(CurriculumManagement::class)
        ->set('selectedElementId', $this->element->id)
        ->set('tpCode', 'BK-1')
        ->set('tpTitle', 'Test TP')
        ->set('tpSortOrder', 1)
        ->call('saveTp')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('learning_objectives', [
        'code' => 'BK-1',
        'element_id' => $this->element->id,
        'is_template' => true,
    ]);
});
