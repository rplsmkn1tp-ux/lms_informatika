<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\LearningObjective;
use Livewire\Livewire;
use App\Livewire\Guru\TpManagement;

$guru = User::where('role', 'guru')->first();
echo "Testing as Guru: {$guru->name} (ID: {$guru->id})\n";

// Test Livewire component
$comp = Livewire::actingAs($guru)->test(TpManagement::class);
$myTps = $comp->viewData('myTps');
echo "Initial myTps count: " . $myTps->count() . "\n";
foreach ($myTps as $tp) {
    echo "  [{$tp->code}] {$tp->title} (Element: {$tp->element->code}, KKTP: {$tp->kktp})\n";
}

echo "\nCalling copyTemplate()...\n";
$comp->call('copyTemplate');
$afterTps = $comp->viewData('myTps');
echo "After copyTemplate count: " . $afterTps->count() . "\n";

echo "Check DB: Guru owned TPs: " . LearningObjective::where('created_by', $guru->id)->count() . "\n";
