<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\User;

$guru = User::where('role', 'guru')->first();
echo "Guru ID: {$guru->id}\n";

$assessments = Assessment::with('classrooms')->get();
echo "Total Assessments in DB: " . $assessments->count() . "\n";
foreach ($assessments as $a) {
    echo "  - ID: {$a->id} | Title: {$a->title} | created_by: {$a->created_by} | Classrooms: " . $a->classrooms->pluck('id')->join(', ') . "\n";
}

