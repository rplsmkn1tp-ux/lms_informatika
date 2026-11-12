<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$c = App\Models\Classroom::first();
if ($c) echo "Classroom: " . $c->grade . ' ' . $c->major;
else echo "No classroom found.";
