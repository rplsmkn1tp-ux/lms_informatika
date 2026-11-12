<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classroom;
use App\Models\TeachingAssignment;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "========================================================\n";
echo "      COMPREHENSIVE GURU (TEACHER) AUDIT SCRIPT\n";
echo "========================================================\n\n";

$guru = User::where('role', 'guru')->first();
if (!$guru) {
    echo "  [FAIL] Akun guru tidak ditemukan!\n";
    exit(1);
}

// Start session
$session = $app->make('session')->driver();
$session->start();
$token = $session->token();

// 1. Audit POST /login as Guru
echo "[1] AUDITING GURU LOGIN VIA POST /login:\n";
$loginRequest = Request::create('/login', 'POST', [
    'login_id' => $guru->email,
    'password' => 'password',
    '_token' => $token,
]);
$loginRequest->setLaravelSession($session);
$loginRequest->cookies->set($session->getName(), $session->getId());

$loginResponse = $kernel->handle($loginRequest);
echo "  - Status Code : " . $loginResponse->getStatusCode() . "\n";
echo "  - Redirect To : " . $loginResponse->headers->get('Location') . "\n";

if ($loginResponse->getStatusCode() === 302 && str_contains($loginResponse->headers->get('Location'), 'dashboard')) {
    echo "  [OK] Login Guru Berhasil!\n\n";
} else {
    echo "  [FAIL] Gagal login guru: " . $loginResponse->getContent() . "\n\n";
}

// 2. Audit Redirect /dashboard as Guru
echo "[2] AUDITING REDIRECTION /dashboard -> /guru:\n";
Auth::login($guru);

$dashReq = Request::create('/dashboard', 'GET');
$dashReq->setLaravelSession($session);
$dashReq->setUserResolver(fn() => $guru);
$dashRes = $kernel->handle($dashReq);

echo "  - Status Code : " . $dashRes->getStatusCode() . "\n";
echo "  - Redirect To : " . $dashRes->headers->get('Location') . "\n";
if ($dashRes->getStatusCode() === 302 && str_contains($dashRes->headers->get('Location'), 'guru')) {
    echo "  [OK] Redirection role guru ke /guru valid!\n\n";
} else {
    echo "  [FAIL] Gagal redirect dashboard: " . $dashRes->getContent() . "\n\n";
}

// 3. Testing All Guru Routes
$assignment = TeachingAssignment::with('classroom')->where('teacher_id', $guru->id)->first();
$classId = $assignment ? $assignment->classroom_id : 1;
$yearId = AcademicYear::where('is_active', true)->first()?->id ?? 1;
$lesson = Lesson::first();
$lessonId = $lesson ? $lesson->id : 1;
$assessment = Assessment::where('created_by', $guru->id)->first() ?? Assessment::first();
$assessmentId = $assessment ? $assessment->id : 1;

$routesToTest = [
    '/guru' => 'Dashboard Guru (Ikhtisar Pembelajaran)',
    "/guru/kelas/{$classId}" => "Detail Kelas & Siswa (Kelas ID: {$classId})",
    '/guru/tp' => 'Manajemen Tujuan Pembelajaran (TP)',
    '/guru/materi' => 'Manajemen Materi & Modul Pelajaran',
    "/guru/materi/{$lessonId}/terbitkan" => "Pengaturan Terbit Materi (Lesson ID: {$lessonId})",
    "/guru/materi/{$lessonId}/pratinjau" => "Pratinjau Materi Mode Siswa (Lesson ID: {$lessonId})",
    "/guru/materi/{$lessonId}" => "Lihat Detail Materi (Lesson ID: {$lessonId})",
    '/guru/absensi' => 'Presensi / Absensi Siswa (Daftar Kelas)',
    "/guru/absensi/{$classId}/sesi" => "Manajer Sesi Pertemuan Absensi (Kelas ID: {$classId})",
    '/guru/agenda' => 'Agenda Jurnal Mengajar Guru',
    '/guru/peta-ketercapaian' => 'Peta Ketercapaian Kompetensi TP Siswa',
    '/guru/laporan-rapor' => 'Laporan Rekap Rapor Siswa',
    '/guru/buku-nilai' => 'Buku Nilai (Daftar Kelas)',
    "/guru/buku-nilai/{$classId}" => "Buku Nilai Spreadsheet Digital (Kelas ID: {$classId})",
    "/guru/buku-nilai/{$classId}/{$yearId}/pdf" => "Export PDF Buku Nilai (Kelas: {$classId}, TA: {$yearId})",
    "/guru/buku-nilai/{$classId}/{$yearId}/excel" => "Export Excel Buku Nilai (Kelas: {$classId}, TA: {$yearId})",
    '/guru/jadwal' => 'Jadwal Mengajar Tatap Muka Mingguan',
    '/guru/tugas' => 'Daftar Tugas & Asesmen Siswa',
    '/guru/tugas/buat' => 'Formulir Pembuatan Tugas Baru',
    "/guru/tugas/{$assessmentId}/edit" => "Formulir Edit Tugas (ID: {$assessmentId})",
    "/guru/tugas/{$assessmentId}/detail" => "Detail & Penilaian Tugas / Submisi (ID: {$assessmentId})",
    "/guru/tugas/{$assessmentId}/download-zip" => "Export Unduh File Submisi ZIP (ID: {$assessmentId})",
    '/profil' => 'Halaman Profil Guru',
];

echo "[3] AUDITING ALL GURU ROUTES:\n";
$results = [];

foreach ($routesToTest as $uri => $label) {
    $req = Request::create($uri, 'GET');
    $req->setLaravelSession($session);
    $req->setUserResolver(fn() => $guru);

    try {
        $res = $kernel->handle($req);
        $status = $res->getStatusCode();
        $content = $res->getContent();
        
        $hasError = false;
        $errorMsg = '';
        
        if ($status >= 400) {
            $hasError = true;
            $errorMsg = "HTTP Status {$status}";
        } elseif (str_contains($content, 'ErrorException') || str_contains($content, 'FatalError') || str_contains($content, 'Uncaught Exception')) {
            $hasError = true;
            $errorMsg = "Exception detected in output";
        }
        
        if ($status === 302) {
            $target = $res->headers->get('Location');
            $results[] = ['uri' => $uri, 'label' => $label, 'status' => "302 REDIRECT -> {$target}", 'ok' => true];
            echo "  [OK] {$uri} [{$label}] -> 302 Redirect to {$target}\n";
        } elseif (!$hasError) {
            $contentType = $res->headers->get('Content-Type');
            $size = strlen($content);
            $results[] = ['uri' => $uri, 'label' => $label, 'status' => "{$status} OK ({$contentType}, {$size} bytes)", 'ok' => true];
            echo "  [OK] {$uri} [{$label}] -> {$status} OK ({$size} bytes)\n";
        } else {
            $results[] = ['uri' => $uri, 'label' => $label, 'status' => "FAILED: {$errorMsg}", 'ok' => false];
            echo "  [FAIL] {$uri} [{$label}] -> {$errorMsg}\n";
        }
    } catch (\Throwable $e) {
        $results[] = ['uri' => $uri, 'label' => $label, 'status' => "EXCEPTION: " . $e->getMessage(), 'ok' => false];
        echo "  [ERROR] {$uri} [{$label}] -> EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n========================================================\n";
echo "SUMMARY AUDIT GURU:\n";
$passed = count(array_filter($results, fn($r) => $r['ok']));
$total = count($results);
echo "Tested: {$total} routes | Passed: {$passed} | Failed: " . ($total - $passed) . "\n";
echo "========================================================\n";