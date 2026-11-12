<?php

use App\Models\User;
use App\Models\Setting;
use Livewire\Livewire;
use App\Livewire\Admin\Settings;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
});

test('admin can view settings page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.settings'))
        ->assertStatus(200);
});

test('non-admin cannot view settings page', function () {
    $this->actingAs($this->guru)
        ->get(route('admin.settings'))
        ->assertStatus(403);
});

test('admin can update settings', function () {
    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->set('app_name', 'Nama Baru')
        ->set('registration_open', false)
        ->set('teacher_approval_required', false)
        ->set('default_kktp', 80)
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::getValue('app_name'))->toBe('Nama Baru');
    expect((bool)Setting::getValue('registration_open'))->toBeFalse();
    expect((bool)Setting::getValue('teacher_approval_required'))->toBeFalse();
    expect((int)Setting::getValue('default_kktp'))->toBe(80);
});
