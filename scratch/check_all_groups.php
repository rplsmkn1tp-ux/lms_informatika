<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$groups = \App\Models\AssessmentGroup::with(['assessment', 'classroom', 'members'])->get();
echo "Total groups in DB: " . $groups->count() . "\n";
foreach ($groups as $g) {
    echo "Group ID: {$g->id}, Assessment ID: {$g->assessment_id} ({$g->assessment?->title}), Name: {$g->name}, Classroom: {$g->classroom?->name}, Members: " . $g->members->count() . "\n";
}
