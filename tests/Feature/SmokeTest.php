<?php

use App\Models\User;
use function Pest\Laravel\post;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    seed(DatabaseSeeder::class);
});

test('admin can login, view dashboard and logout', function () {
    $response = post('/login', [
        'login_id' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ]);
    $response->assertRedirect('/dashboard');

    $dashboard = get('/dashboard');
    $dashboard->assertRedirect('/admin');

    $adminHome = get('/admin');
    $adminHome->assertStatus(200);

    // Test other admin routes
    $adminRoutes = [
        '/admin/users',
        '/admin/tahun-ajaran',
        '/admin/classrooms',
        '/admin/teaching-assignments',
        '/admin/kurikulum',
        '/admin/pengaturan',
        '/admin/verifikasi',
        '/admin/laporan-kelulusan',
    ];

    foreach ($adminRoutes as $route) {
        get($route)->assertStatus(200);
    }

    // Livewire component tests
    \Livewire\Livewire::test(\App\Livewire\Admin\Dashboard::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\UserManagement::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\AcademicYearManagement::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\ClassroomManagement::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\TeachingAssignment::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\CurriculumManagement::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\Settings::class)->assertOk();
    \Livewire\Livewire::test(\App\Livewire\Admin\Verification::class)->assertOk();

    // Logout
    post('/logout')->assertRedirect('/');
    get('/admin')->assertRedirect('/login');
});

test('guru can login, view dashboard and logout', function () {
    $response = post('/login', [
        'login_id' => 'guru@example.com',
        'password' => 'password',
    ]);
    $response->assertRedirect('/dashboard');

    $dashboard = get('/dashboard');
    $dashboard->assertRedirect('/guru');

    $guruHome = get('/guru');
    $guruHome->assertStatus(200);

    $guruRoutes = [
        '/guru/tp',
        '/guru/materi',
        '/guru/buku-nilai',
        '/guru/absensi',
        '/guru/peta-ketercapaian',
        '/guru/laporan-rapor',
    ];

    foreach ($guruRoutes as $route) {
        get($route)->assertStatus(200);
    }

    // Logout
    post('/logout')->assertRedirect('/');
    get('/guru')->assertRedirect('/login');
});

test('siswa can login, view dashboard and logout', function () {
    $response = post('/login', [
        'login_id' => 'siswa@example.com',
        'password' => 'password',
    ]);
    $response->assertRedirect('/dashboard');

    $dashboard = get('/dashboard');
    $dashboard->assertRedirect('/beranda');

    $siswaHome = get('/beranda');
    $siswaHome->assertStatus(200);

    $siswaRoutes = [
        '/nilai',
        '/profil',
    ];

    foreach ($siswaRoutes as $route) {
        get($route)->assertStatus(200);
    }

    // Logout
    post('/logout')->assertRedirect('/');
    get('/beranda')->assertRedirect('/login');
});
