<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('admin main page loads header exactly once', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    
    $response = actingAs($admin)->get('/admin');
    $response->assertOk();
    
    $content = $response->getContent();
    $count = substr_count($content, 'data-testid="app-header"');
    
    expect($count)->toBe(1, 'Admin header should be loaded exactly once.');
});

test('guru main page loads header exactly once', function () {
    $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    
    $response = actingAs($guru)->get('/guru');
    $response->assertOk();
    
    $content = $response->getContent();
    $count = substr_count($content, 'data-testid="app-header"');
    
    expect($count)->toBe(1, 'Guru header should be loaded exactly once.');
});

test('siswa main page loads header exactly once', function () {
    $siswa = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    
    // Create classroom if needed or just go to /beranda
    $response = actingAs($siswa)->get('/beranda');
    $response->assertOk();
    
    $content = $response->getContent();
    $count = substr_count($content, 'data-testid="app-header"');
    
    expect($count)->toBe(1, 'Siswa header should be loaded exactly once.');
});
