<?php

use App\Models\User;
use function Pest\Laravel\seed;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    seed(DatabaseSeeder::class);
    $this->admin = User::where('role', 'admin')->first();
    $this->guru = User::where('role', 'guru')->first();
    $this->siswa = User::where('role', 'siswa')->first();
});

test('guests are redirected to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admin dapat mengakses dashboard admin', function () {
    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertStatus(200)
        ->assertSee('Dashboard')
        ->assertSee('Total Siswa')
        ->assertSee('Total Guru');
});

test('guru dapat mengakses dashboard guru', function () {
    $this->actingAs($this->guru)
        ->get('/guru')
        ->assertStatus(200)
        ->assertSee('Statistik Kelas', false)
        ->assertSee('Kelas yang diampu', false)
        ->assertSee('Perlu perhatian', false);
});

test('siswa tidak dapat mengakses dashboard admin atau guru', function () {
    $this->actingAs($this->siswa)
        ->get('/admin')
        ->assertStatus(403);
        
    $this->actingAs($this->siswa)
        ->get('/guru')
        ->assertStatus(403);
});

test('admin yang membuka /dashboard diarahkan ke /admin', function () {
    $this->actingAs($this->admin)
        ->get('/dashboard')
        ->assertRedirect('/admin');
});

test('guru yang membuka /dashboard diarahkan ke /guru', function () {
    $this->actingAs($this->guru)
        ->get('/dashboard')
        ->assertRedirect('/guru');
});

test('siswa yang membuka /dashboard diarahkan ke /beranda', function () {
    $this->actingAs($this->siswa)
        ->get('/dashboard')
        ->assertRedirect('/beranda');
});
