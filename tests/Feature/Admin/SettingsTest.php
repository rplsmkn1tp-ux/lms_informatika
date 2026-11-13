<?php

use App\Models\User;
use App\Models\Setting;
use Livewire\Livewire;
use App\Livewire\Admin\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('admin can upload custom logo and updates logo_version', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('custom-logo.png', 100, 100);

    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->set('logo', $file)
        ->call('save')
        ->assertHasNoErrors();

    $appLogo = Setting::getValue('app_logo');
    expect($appLogo)->not->toBeNull();
    expect(Setting::getValue('logo_version'))->not->toBeNull();
});

test('admin can remove logo and resets to default', function () {
    Storage::fake('public');

    Setting::setValue('app_logo', 'logos/test.png');
    Setting::setValue('app_logo_original', 'logos/original/test.png');

    Livewire::actingAs($this->admin)
        ->test(Settings::class)
        ->call('removeLogo')
        ->assertHasNoErrors();

    expect(Setting::getValue('app_logo'))->toBeNull();
    expect(Setting::getValue('app_logo_original'))->toBeNull();
});

test('webmanifest route returns valid json with app branding', function () {
    Setting::setValue('app_name', 'SMKN 1 Tanjungpandan');
    
    $response = $this->get(route('webmanifest'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/manifest+json');
    $response->assertJsonPath('name', 'SMKN 1 Tanjungpandan');
});

test('favicon component renders correctly on layouts', function () {
    Setting::setValue('app_logo', null);
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200)
        ->assertSee('favicon.svg')
        ->assertSee('favicon.ico');

    Setting::setValue('app_logo', 'logos/custom_test.png');
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200)
        ->assertSee('logos/custom_test.png');
});

