<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "========================================================\n";
echo "      COMPREHENSIVE ADMIN AUDIT SCRIPT\n";
echo "========================================================\n\n";

// 1. Audit Admin User Account
echo "[1] AUDITING ADMIN ACCOUNT IN DATABASE:\n";
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "  [FAIL] No user with role='admin' found in database!\n";
    exit(1);
}
echo "  [OK] Admin found:\n";
echo "       ID       : {$admin->id}\n";
echo "       Name     : {$admin->name}\n";
echo "       Email    : {$admin->email}\n";
echo "       Role     : {$admin->role}\n";
echo "       Status   : {$admin->status}\n";
echo "       Password : " . (\Illuminate\Support\Facades\Hash::check('password', $admin->password) ? "Matches 'password' [OK]" : "DOES NOT MATCH 'password' [WARN]") . "\n\n";

// 2. Audit Database Counts
echo "[2] AUDITING DATABASE ENTITIES:\n";
echo "  - Total Users          : " . User::count() . " (Admin: " . User::where('role', 'admin')->count() . ", Guru: " . User::where('role', 'guru')->count() . ", Siswa: " . User::where('role', 'siswa')->count() . ")\n";
echo "  - Pending Users        : " . User::where('status', 'pending')->count() . "\n";
echo "  - Academic Years       : " . \App\Models\AcademicYear::count() . " (Active: " . \App\Models\AcademicYear::where('is_active', true)->count() . ")\n";
echo "  - Classrooms           : " . \App\Models\Classroom::count() . "\n";
echo "  - Teaching Assignments : " . \App\Models\TeachingAssignment::count() . "\n";
echo "  - Lessons              : " . \App\Models\Lesson::count() . "\n";
echo "  - Settings             : " . \App\Models\Setting::count() . "\n\n";

// 3. Testing Authentication & Session Login
echo "[3] AUDITING AUTHENTICATION & REDIRECT FLOW:\n";

// Create a simulated request to POST /login
$loginRequest = Request::create('/login', 'POST', [
    'login_id' => $admin->email,
    'password' => 'password',
], [], [], [
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
]);

// Start session
$session = $app->make('session')->driver();
$loginRequest->setLaravelSession($session);

$response = $kernel->handle($loginRequest);
echo "  - POST /login response status : " . $response->getStatusCode() . "\n";
$redirectTarget = $response->headers->get('Location');
echo "  - POST /login redirect target : " . ($redirectTarget ?: 'none') . "\n";

if ($response->getStatusCode() === 302 && str_contains($redirectTarget, 'dashboard')) {
    echo "  [OK] Login authentication flow works properly!\n\n";
} else {
    echo "  [WARN] Unexpected response on login!\n\n";
}

// 4. Testing All Admin Routes via HTTP Request as Admin
echo "[4] AUDITING ALL ADMIN ROUTES (HTTP GET AS AUTHENTICATED ADMIN):\n";

$routesToTest = [
    '/dashboard' => 'Dashboard Redirection',
    '/admin' => 'Admin Dashboard',
    '/admin/users' => 'Manajemen Pengguna (User Management)',
    '/admin/tahun-ajaran' => 'Manajemen Tahun Ajaran',
    '/admin/classrooms' => 'Manajemen Kelas (Classrooms)',
    '/admin/teaching-assignments' => 'Plot Mengajar (Teaching Assignments)',
    '/admin/jadwal/waktu' => 'Jadwal - Master Jam Pelajaran (Bell Period)',
    '/admin/kurikulum' => 'Manajemen Kurikulum & Elemen CP',
    '/admin/pengaturan' => 'Pengaturan Sekolah / Sistem',
    '/admin/verifikasi' => 'Verifikasi Pendaftaran Guru',
    '/admin/laporan-absensi' => 'Laporan Rekap Absensi',
    '/admin/laporan-kelulusan' => 'Laporan Kelulusan Siswa',
    '/profil' => 'Halaman Profil Admin',
];

Auth::login($admin);

$results = [];

foreach ($routesToTest as $uri => $label) {
    // Re-create request for route
    $req = Request::create($uri, 'GET');
    $req->setLaravelSession($session);
    
    // Bind current user to request
    $req->setUserResolver(function () use ($admin) {
        return $admin;
    });

    try {
        $res = $kernel->handle($req);
        $status = $res->getStatusCode();
        $content = $res->getContent();
        
        $hasError = false;
        $errorMsg = '';
        
        if ($status >= 400) {
            $hasError = true;
            $errorMsg = "HTTP Status {$status}";
        } elseif (str_contains($content, 'ErrorException') || str_contains($content, 'FatalError') || str_contains($content, 'Uncaught')) {
            $hasError = true;
            $errorMsg = "Exception detected in output";
        }
        
        if ($status === 302) {
            $target = $res->headers->get('Location');
            $results[] = [
                'uri' => $uri,
                'label' => $label,
                'status' => "302 REDIRECT -> {$target}",
                'ok' => true
            ];
            echo "  [OK] {$uri} [{$label}] -> 302 Redirect to {$target}\n";
        } elseif (!$hasError) {
            $results[] = [
                'uri' => $uri,
                'label' => $label,
                'status' => "{$status} OK",
                'ok' => true
            ];
            echo "  [OK] {$uri} [{$label}] -> 200 OK (Rendered " . strlen($content) . " bytes)\n";
        } else {
            $results[] = [
                'uri' => $uri,
                'label' => $label,
                'status' => "FAILED: {$errorMsg}",
                'ok' => false
            ];
            echo "  [FAIL] {$uri} [{$label}] -> {$errorMsg}\n";
        }
    } catch (\Throwable $e) {
        $results[] = [
            'uri' => $uri,
            'label' => $label,
            'status' => "EXCEPTION: " . $e->getMessage(),
            'ok' => false
        ];
        echo "  [ERROR] {$uri} [{$label}] -> EXCEPTION: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\n========================================================\n";
echo "SUMMARY AUDIT:\n";
$passed = count(array_filter($results, fn($r) => $r['ok']));
$total = count($results);
echo "Tested: {$total} routes | Passed: {$passed} | Failed: " . ($total - $passed) . "\n";
echo "========================================================\n";
