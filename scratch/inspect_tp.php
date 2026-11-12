<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\LearningObjective;
use App\Models\Element;

echo "=== GURU USERS ===" . PHP_EOL;
$gurus = User::where('role', 'guru')->get();
foreach ($gurus as $g) {
    echo "ID: {$g->id}, Name: {$g->name}, Email: {$g->email}" . PHP_EOL;
}

echo PHP_EOL . "=== ADMIN USERS ===" . PHP_EOL;
$admins = User::where('role', 'admin')->get();
foreach ($admins as $a) {
    echo "ID: {$a->id}, Name: {$a->name}, Email: {$a->email}" . PHP_EOL;
}

echo PHP_EOL . "=== LEARNING OBJECTIVES IN DB ===" . PHP_EOL;
$tps = LearningObjective::all();
foreach ($tps as $t) {
    echo "ID: {$t->id}, Code: {$t->code}, Element: {$t->element_id}, is_template: " . var_export($t->is_template, true) . ", created_by: {$t->created_by}" . PHP_EOL;
}
