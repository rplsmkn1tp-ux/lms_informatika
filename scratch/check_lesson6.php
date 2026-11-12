<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Assessment;
use App\Models\Lesson;
use App\Models\LearningObjective;
use Illuminate\Support\Facades\DB;

echo "=== DETAIL LESSON ID 6 (1sdfsdf) ===" . PHP_EOL;
$l6 = Lesson::find(6);
$a7 = Assessment::find(7);
if ($l6) {
    echo "Lesson 6: ID={$l6->id}, Code='{$l6->code}', Title='{$l6->title}', TP={$l6->learning_objective_id}, Created={$l6->created_at}" . PHP_EOL;
}
if ($a7) {
    echo "Assessment 7: ID={$a7->id}, Title='{$a7->title}', lesson_id={$a7->lesson_id}, TP={$a7->learning_objective_id}, Created={$a7->created_at}" . PHP_EOL;
}

echo PHP_EOL . "=== ALL ASSESSMENTS WITH DETAILS ===" . PHP_EOL;
$allAssessments = Assessment::all();
foreach ($allAssessments as $a) {
    echo "ID: {$a->id} | Title: '{$a->title}' | Type: {$a->type} | lesson_id: " . var_export($a->lesson_id, true) . " | learning_objective_id: {$a->learning_objective_id} | created_by: {$a->created_by}" . PHP_EOL;
}

echo PHP_EOL . "=== PIVOT assessment_learning_objective ===" . PHP_EOL;
if (DB::getSchemaBuilder()->hasTable('assessment_learning_objective')) {
    $pivots = DB::table('assessment_learning_objective')->get();
    foreach ($pivots as $p) {
        echo "Assessment ID: {$p->assessment_id}, TP ID: {$p->learning_objective_id}" . PHP_EOL;
    }
}
