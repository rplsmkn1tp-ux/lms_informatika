<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LearningObjective;
use App\Models\Element;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\LessonAttachment;
use App\Models\LessonProgress;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

echo "=== CHECK DUPLICATE TP (element_id, code) ===" . PHP_EOL;
$dups = DB::table('learning_objectives')
    ->select('element_id', 'code', DB::raw('count(*) as total'))
    ->groupBy('element_id', 'code')
    ->havingRaw('count(*) > 1')
    ->get();

foreach ($dups as $d) {
    echo "Element ID: {$d->element_id}, Code: {$d->code}, Count: {$d->total}" . PHP_EOL;
}

echo PHP_EOL . "=== ALL LEARNING OBJECTIVES ===" . PHP_EOL;
$all = LearningObjective::with('element')->orderBy('element_id')->orderBy('code')->orderBy('id')->get();
foreach ($all as $tp) {
    $elName = $tp->element ? $tp->element->name : 'No Element';
    $lessonCount = Lesson::where('learning_objective_id', $tp->id)->count();
    $assCount = Assessment::where('learning_objective_id', $tp->id)->count();
    echo "ID: {$tp->id} | Element: {$tp->element_id} ({$elName}) | Code: {$tp->code} | Title: '{$tp->title}' | is_template: {$tp->is_template} | created_by: {$tp->created_by} | Lessons: {$lessonCount} | Assessments: {$assCount}" . PHP_EOL;
}

echo PHP_EOL . "=== ALL LESSONS ===" . PHP_EOL;
$lessons = Lesson::with(['learningObjective', 'classrooms', 'attachments', 'assessments'])->orderBy('id')->get();
foreach ($lessons as $l) {
    $tpCode = $l->learningObjective ? $l->learningObjective->code : 'No TP';
    $classes = $l->classrooms->pluck('name')->implode(', ');
    $attCount = $l->attachments->count();
    $assCount = $l->assessments->count();
    echo "ID: {$l->id} | TP: {$tpCode} (ID: {$l->learning_objective_id}) | Code: {$l->code} | Title: '{$l->title}' | Status: {$l->status} | Classes: [{$classes}] | Attachments: {$attCount} | Assessments: {$assCount} | Created By: {$l->created_by}" . PHP_EOL;
}

echo PHP_EOL . "=== ALL ASSESSMENTS ===" . PHP_EOL;
$assessments = Assessment::with(['lesson', 'learningObjective', 'classrooms'])->orderBy('id')->get();
foreach ($assessments as $a) {
    $lCode = $a->lesson ? $a->lesson->code : 'No Lesson';
    $tpCode = $a->learningObjective ? $a->learningObjective->code : 'No TP';
    $classes = $a->classrooms->pluck('name')->implode(', ');
    $subCount = Submission::where('assessment_id', $a->id)->count();
    echo "ID: {$a->id} | TP: {$tpCode} | Lesson: {$lCode} (ID: {$a->lesson_id}) | Title: '{$a->title}' | Type: {$a->type} | Classes: [{$classes}] | Submissions: {$subCount}" . PHP_EOL;
}
