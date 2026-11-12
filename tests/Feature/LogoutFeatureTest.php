<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('admin can logout via POST and GET', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

    actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Keluar');

    actingAs($admin)
        ->post('/logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();

    // Test GET /logout fallback
    actingAs($admin)
        ->get('/logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();
});

test('guru can logout via POST and GET', function () {
    $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);

    actingAs($guru)
        ->get('/guru')
        ->assertOk()
        ->assertSee('Keluar');

    actingAs($guru)
        ->post('/logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();

    actingAs($guru)
        ->get('/logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();
});

test('siswa can logout via POST and GET', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

    actingAs($siswa)
        ->get('/beranda')
        ->assertOk()
        ->assertSee('Keluar');

    actingAs($siswa)
        ->post('/logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();

    actingAs($siswa)
        ->get('/logout')
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();
});

