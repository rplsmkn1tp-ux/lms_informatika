<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\GradeWeight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING RAPOR PDF GENERATION (WHEN PUBLISHED) ===\n";

$siswa = User::where('role', 'siswa')->where('email', 'brh@siswa.sch')->first();

$session = $app->make('session')->driver();
$session->start();

$req = Request::create('/rapor/cetak/2/1', 'GET');
$req->setLaravelSession($session);
$req->setUserResolver(fn() => $siswa);
$app->instance('request', $req);

Auth::login($siswa);

// Set GradeWeight is_published = true for classroom 2 and year 1 temporarily to test
$weight = GradeWeight::firstOrCreate([
    'classroom_id' => 2,
    'academic_year_id' => 1,
], [
    'weight_uh' => 30,
    'weight_tugas' => 30,
    'weight_uas_mid' => 30,
    'weight_absensi' => 10,
    'is_published' => false
]);

$originalStatus = $weight->is_published;
$weight->is_published = true;
$weight->save();

try {
    $res = $kernel->handle($req);

    echo "Status Code: " . $res->getStatusCode() . "\n";
    echo "Content-Type: " . $res->headers->get('Content-Type') . "\n";
    echo "Content Length: " . strlen($res->getContent()) . " bytes\n";
    
    if ($res->getStatusCode() === 200 && str_contains($res->headers->get('Content-Type'), 'pdf')) {
        echo "[OK] PDF Rapor Siswa berhasil digenerate dan diunduh tanpa error!\n";
    } else {
        echo "[FAIL] Respon tidak sesuai: " . substr($res->getContent(), 0, 300) . "\n";
    }
} finally {
    // Revert back to original status
    $weight->is_published = $originalStatus;
    $weight->save();
}
