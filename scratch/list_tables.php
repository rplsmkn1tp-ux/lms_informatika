<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $arr = (array)$t;
    echo array_values($arr)[0] . PHP_EOL;
}

