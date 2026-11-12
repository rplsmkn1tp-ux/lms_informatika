<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Tables with attendance or presensi:" . PHP_EOL;
$tables = DB::select("SHOW TABLES");
foreach ($tables as $t) {
    $tbl = array_values((array)$t)[0];
    if (str_contains($tbl, 'atten') || str_contains($tbl, 'absen') || str_contains($tbl, 'presen')) {
        echo "- $tbl" . PHP_EOL;
    }
}

echo PHP_EOL . "All records in attendance_sessions:" . PHP_EOL;
$allSessions = DB::table('attendance_sessions')->get();
foreach ($allSessions as $s) {
    echo json_encode($s) . PHP_EOL;
}

echo PHP_EOL . "All records in attendance_records:" . PHP_EOL;
$allRecords = DB::table('attendance_records')->get();
foreach ($allRecords as $r) {
    echo json_encode($r) . PHP_EOL;
}
