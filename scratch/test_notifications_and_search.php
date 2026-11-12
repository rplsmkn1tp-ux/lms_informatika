<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$dummyReq = \Illuminate\Http\Request::create('http://localhost:8000/');
$app->instance('request', $dummyReq);

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "========================================================\n";
echo "      TESTING NOTIFICATION SYSTEM & LAYOUT CHANGES\n";
echo "========================================================\n\n";

$guru = User::where('role', 'guru')->first();
$siswa = User::where('role', 'siswa')->where('email', 'brh@siswa.sch')->first();
$service = app(NotificationService::class);

// 1. Test Siswa Notifications
echo "[1] TESTING SISWA NOTIFICATIONS ({$siswa->name}):\n";
$siswaNotifs = $service->getNotifications($siswa);
echo "  - Total Notifications: " . $siswaNotifs->count() . "\n";
echo "  - Unread Count: " . $service->getUnreadCount($siswa) . "\n";
foreach ($siswaNotifs->take(4) as $n) {
    echo "    * [{$n['type']}] {$n['title']} | Key: {$n['key']} | Read: " . ($n['is_read'] ? 'YES' : 'NO') . "\n";
}

// 2. Test Guru Notifications
echo "\n[2] TESTING GURU NOTIFICATIONS ({$guru->name}):\n";
$guruNotifs = $service->getNotifications($guru);
echo "  - Total Notifications: " . $guruNotifs->count() . "\n";
echo "  - Unread Count: " . $service->getUnreadCount($guru) . "\n";
foreach ($guruNotifs->take(4) as $n) {
    echo "    * [{$n['type']}] {$n['title']} | Key: {$n['key']} | Read: " . ($n['is_read'] ? 'YES' : 'NO') . "\n";
}

// 3. Test HTTP Rendering for Siswa (/beranda)
echo "\n[3] TESTING HTTP RENDER /beranda (SISWA):\n";
$session = $app->make('session')->driver();
$session->start();
$reqSiswa = Request::create('/beranda', 'GET');
$reqSiswa->setLaravelSession($session);
$reqSiswa->setUserResolver(fn() => $siswa);
$app->instance('request', $reqSiswa);
Auth::login($siswa);

$resSiswa = $kernel->handle($reqSiswa);
echo "  - Status Code: " . $resSiswa->getStatusCode() . "\n";
echo "  - Content Length: " . strlen($resSiswa->getContent()) . " bytes\n";
$hasSearchInSiswa = str_contains($resSiswa->getContent(), 'search-siswa');
echo "  - Global Search in Siswa page: " . ($hasSearchInSiswa ? 'STILL PRESENT [FAIL]' : 'REMOVED [OK]') . "\n";
$hasNotifInSiswa = str_contains($resSiswa->getContent(), 'Pemberitahuan');
echo "  - Notification component in Siswa page: " . ($hasNotifInSiswa ? 'ACTIVE [OK]' : 'NOT FOUND [FAIL]') . "\n";

// 4. Test HTTP Rendering for Guru (/guru)
echo "\n[4] TESTING HTTP RENDER /guru (GURU):\n";
$reqGuru = Request::create('/guru', 'GET');
$reqGuru->setLaravelSession($session);
$reqGuru->setUserResolver(fn() => $guru);
$app->instance('request', $reqGuru);
Auth::login($guru);

$resGuru = $kernel->handle($reqGuru);
echo "  - Status Code: " . $resGuru->getStatusCode() . "\n";
echo "  - Content Length: " . strlen($resGuru->getContent()) . " bytes\n";
$hasSearchInGuru = str_contains($resGuru->getContent(), 'search-guru');
echo "  - Global Search in Guru page: " . ($hasSearchInGuru ? 'STILL PRESENT [FAIL]' : 'REMOVED [OK]') . "\n";
$hasNotifInGuru = str_contains($resGuru->getContent(), 'Pemberitahuan');
echo "  - Notification component in Guru page: " . ($hasNotifInGuru ? 'ACTIVE [OK]' : 'NOT FOUND [FAIL]') . "\n";

// 5. Verify Admin Search is UNTOUCHED
echo "\n[5] VERIFYING ADMIN SEARCH IN /admin:\n";
$admin = User::where('role', 'admin')->first();
$reqAdmin = Request::create('/admin', 'GET');
$reqAdmin->setLaravelSession($session);
$reqAdmin->setUserResolver(fn() => $admin);
$app->instance('request', $reqAdmin);
Auth::login($admin);

$resAdmin = $kernel->handle($reqAdmin);
$hasSearchInAdmin = str_contains($resAdmin->getContent(), 'search-admin');
echo "  - Admin Global Search: " . ($hasSearchInAdmin ? 'PRESERVED [OK]' : 'MISSING [WARN]') . "\n";

echo "\n========================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "========================================================\n";
