<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE', 'ruang_informatika');
$keyName = 'Tables_in_' . $dbName;

echo "Searching for 1sdfsdf in all tables..." . PHP_EOL;
foreach ($tables as $t) {
    $tableName = $t->$keyName;
    try {
        $cols = DB::getSchemaBuilder()->getColumnListing($tableName);
        foreach ($cols as $col) {
            $count = DB::table($tableName)->where($col, 'like', '%1sdfsdf%')->count();
            if ($count > 0) {
                echo "Found in table: {$tableName}, column: {$col} ({$count} rows)" . PHP_EOL;
                $rows = DB::table($tableName)->where($col, 'like', '%1sdfsdf%')->get();
                foreach ($rows as $r) {
                    echo "  " . json_encode($r) . PHP_EOL;
                }
            }
        }
    } catch (\Exception $e) {
    }
}
