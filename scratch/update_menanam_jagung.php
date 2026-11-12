<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$a = App\Models\Assessment::find(11);
$a->attendance_session_id = 3;
$a->meeting_no = 1;
// Link to lesson 1 if empty
if (!$a->lesson_id) {
    $a->lesson_id = 1;
    $lesson = App\Models\Lesson::find(1);
    $a->learning_objective_id = $lesson?->learning_objective_id;
    $a->element_id = $lesson?->learningObjective?->element_id;
}
$a->save();

echo "Updated Assessment 11:\n";
echo "Title: {$a->title}\n";
echo "Lesson: {$a->lesson?->title}\n";
echo "Meeting No: {$a->effectiveMeetingNo()}\n";
echo "Attendance Session: Pertemuan {$a->attendanceSession?->meeting_no}\n";
