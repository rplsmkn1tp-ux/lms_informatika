<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "========================================================\n";
echo "      COMPREHENSIVE SISWA (STUDENT) AUDIT SCRIPT\n";
echo "========================================================\n\n";

$siswa = User::where('role', 'siswa')->where('email', 'brh@siswa.sch')->first();
if (!$siswa) {
    echo "  [FAIL] Siswa 'brh@siswa.sch' tidak ditemukan!\n";
    exit(1);
}

// Start session
$session = $app->make('session')->driver();
$session->start();
$token = $session->token();

// 1. Audit POST /login as Siswa
echo "[1] AUDITING SISWA LOGIN VIA POST /login:\n";
$loginRequest = Request::create('/login', 'POST', [
    'login_id' => $siswa->email,
    'password' => 'password',
    '_token' => $token,
]);
$loginRequest->setLaravelSession($session);
$loginRequest->cookies->set($session->getName(), $session->getId());

$loginResponse = $kernel->handle($loginRequest);
echo "  - Status Code : " . $loginResponse->getStatusCode() . "\n";
echo "  - Redirect To : " . $loginResponse->headers->get('Location') . "\n";

if ($loginResponse->getStatusCode() === 302 && str_contains($loginResponse->headers->get('Location'), 'dashboard')) {
    echo "  [OK] Login Siswa Berhasil!\n\n";
} else {
    echo "  [FAIL] Gagal login siswa: " . $loginResponse->getContent() . "\n\n";
}

// 2. Audit Redirect /dashboard as Siswa
echo "[2] AUDITING REDIRECTION /dashboard -> /beranda:\n";
Auth::login($siswa);

$dashReq = Request::create('/dashboard', 'GET');
$dashReq->setLaravelSession($session);
$dashReq->setUserResolver(fn() => $siswa);
$dashRes = $kernel->handle($dashReq);

echo "  - Status Code : " . $dashRes->getStatusCode() . "\n";
echo "  - Redirect To : " . $dashRes->headers->get('Location') . "\n";
if ($dashRes->getStatusCode() === 302 && str_contains($dashRes->headers->get('Location'), 'beranda')) {
    echo "  [OK] Redirection role siswa ke /beranda valid!\n\n";
} else {
    echo "  [FAIL] Gagal redirect dashboard: " . $dashRes->getContent() . "\n\n";
}

// 3. Testing All Siswa Routes
$classroom = $siswa->classrooms->first() ?? Classroom::first();
$classId = $classroom ? $classroom->id : 1;
$activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
$yearId = $activeYear ? $activeYear->id : 1;

$routesToTest = [
    '/beranda' => 'Beranda Utama Siswa (Ringkasan Pembelajaran)',
    '/belajar' => 'Daftar Elemen Pembelajaran',
    '/belajar/BK' => 'Daftar TP Elemen BK (Berpikir Komputasional)',
    '/belajar/LD' => 'Daftar TP Elemen LD (Literasi Digital)',
    '/belajar/LD/LD-4/21' => 'Detail Materi Pelajaran (LD / LD-4 / ID: 21)',
    '/tugas' => 'Daftar Tugas & Asesmen Siswa',
    '/tugas/19' => 'Halaman Pengerjaan Tugas Individu (ID: 19)',
    '/tugas/20' => 'Halaman Pengerjaan Tugas Kelompok (ID: 20)',
    '/nilai' => 'Daftar Nilai Asesmen & Riwayat Tugas',
    '/buku-nilai' => 'Buku Nilai Digital Siswa',
    '/absensi' => 'Riwayat Kehadiran / Absensi Siswa',
    '/rapor' => 'Laporan Capaian Rapor Siswa',
    "/rapor/cetak/{$classId}/{$yearId}" => "Cetak Rapor Mandiri PDF (Kelas: {$classId}, TA: {$yearId})",
    '/coming-soon' => 'Halaman Fitur Mendatang (Coming Soon)',
    '/profil' => 'Halaman Profil & Pengaturan Akun Siswa',
];

echo "[3] AUDITING ALL SISWA ROUTES:\n";
$results = [];

foreach ($routesToTest as $uri => $label) {
    $req = Request::create($uri, 'GET');
    $req->setLaravelSession($session);
    $req->setUserResolver(fn() => $siswa);

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
            $size = strlen($content);
            $results[] = ['uri' => $uri, 'label' => $label, 'status' => "{$status} OK ({$size} bytes)", 'ok' => true];
            echo "  [OK] {$uri} [{$label}] -> {$status} OK ({$size} bytes)\n";
        } else {
            $results[] = ['uri' => $uri, 'label' => $label, 'status' => "FAILED: {$errorMsg}", 'ok' => false];
            echo "  [FAIL] {$uri} [{$label}] -> {$errorMsg}\n";
            // Check first 300 chars of content for hint
            $snippet = substr(strip_tags($content), 0, 200);
            echo "         Snippet: " . trim($snippet) . "\n";
        }
    } catch (\Throwable $e) {
        $results[] = ['uri' => $uri, 'label' => $label, 'status' => "EXCEPTION: " . $e->getMessage(), 'ok' => false];
        echo "  [ERROR] {$uri} [{$label}] -> EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n========================================================\n";
echo "SUMMARY AUDIT SISWA:\n";
$passed = count(array_filter($results, fn($r) => $r['ok']));
$total = count($results);
echo "Tested: {$total} routes | Passed: {$passed} | Failed: " . ($total - $passed) . "\n";
echo "========================================================\n";

