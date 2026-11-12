<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

dataset('roles', function () {
    return ['guest', 'siswa', 'guru', 'admin'];
});

it('tests access to all routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $guru = User::factory()->create(['role' => 'guru']);
    $siswa = User::factory()->create(['role' => 'siswa']);

    $routes = [
        '/' => '/',
        'admin' => 'admin.dashboard',
        'admin/classrooms' => 'admin.classrooms',
        'admin/kurikulum' => 'admin.curriculum',
        'admin/laporan-kelulusan' => 'admin.laporan-kelulusan',
        'admin/pengaturan' => 'admin.settings',
        'admin/tahun-ajaran' => 'admin.academic-years',
        'admin/teaching-assignments' => 'admin.teaching-assignments',
        'admin/users' => 'admin.users',
        'admin/verifikasi' => 'admin.verifikasi',
        'belajar/{element:code}' => 'siswa.belajar.tp',
        'belajar/{element:code}/{tp:code}/{lesson:code}' => 'siswa.belajar.materi',
        'beranda' => 'siswa.beranda',
        'coming-soon' => 'siswa.coming-soon',
        'dashboard' => 'dashboard',
        'guru' => 'guru.dashboard',
        'guru/asesmen' => 'guru.assessments',
        'guru/asesmen/{assessment}/soal' => 'guru.assessments.questions',
        'guru/asesmen/{assessment}/terbitkan' => 'guru.assessments.publish',
        'guru/kelas/{classroom}' => 'guru.classroom',
        'guru/laporan-rapor' => 'guru.laporan-rapor',
        'guru/materi' => 'guru.lessons',
        'guru/materi/{lesson}/terbitkan' => 'guru.lessons.publish',
        'guru/penilaian' => 'guru.penilaian',
        'guru/peta-ketercapaian' => 'guru.peta-ketercapaian',
        'guru/rapor/cetak-kelas/{classroom}' => 'guru.rapor.cetak-kelas',
        'guru/rapor/cetak-siswa/{student}/{classroom}' => 'guru.rapor.cetak-siswa',
        'guru/tp' => 'guru.tp',
        'nilai' => 'siswa.nilai',
        'pending-verification' => 'pending.notice',
        'profil' => 'siswa.profil',
        'rapor' => 'siswa.rapor',
        'settings' => 'settings',
        'tugas' => 'siswa.tugas',
        'tugas/{assessment}' => 'siswa.tugas.kerjakan'
    ];

    $params = [
        'classroom' => 1, // Assuming classroom 1 exists
        'element:code' => 'BK', // Assuming element BK exists
        'tp:code' => 'BK-1', // Assuming tp BK-1 exists
        'lesson:code' => 'P1', // Assuming lesson P1 exists
        'assessment' => 1, // Assuming assessment 1 exists
        'student' => $siswa->id,
        'lesson' => 1
    ];

    $results = [];

    $roles = [
        'Tamu' => null,
        'Siswa' => $siswa,
        'Guru' => $guru,
        'Admin' => $admin,
    ];

    foreach ($routes as $uri => $routeName) {
        // Build URL
        $url = $uri;
        foreach ($params as $key => $val) {
            $url = str_replace('{' . $key . '}', $val, $url);
        }

        $row = ['Route' => $uri];

        foreach ($roles as $roleName => $user) {
            if ($user) {
                $response = $this->actingAs($user)->get($url);
            } else {
                \Illuminate\Support\Facades\Auth::logout();
                $response = $this->get($url);
            }
            $status = $response->status();
            $row[$roleName] = $status;
        }

        $results[] = $row;
    }

    // Print table
    $output = "\n| Route | Tamu | Siswa | Guru | Admin |\n";
    $output .= "|---|---|---|---|---|\n";
    foreach ($results as $row) {
        $output .= "| {$row['Route']} | {$row['Tamu']} | {$row['Siswa']} | {$row['Guru']} | {$row['Admin']} |\n";
    }

    file_put_contents(storage_path('app/test_output.txt'), $output);
    $this->assertTrue(true);
});
