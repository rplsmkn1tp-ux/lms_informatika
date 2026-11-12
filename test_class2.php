<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$c = App\Models\Classroom::all();
foreach($c as $class) {
    echo $class->grade . ' ' . $class->major . "\n";
}
