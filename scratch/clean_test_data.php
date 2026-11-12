<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

$testTps = LearningObjective::where('code', 'like', '%TEST%')->get();
foreach ($testTps as $tp) {
    echo "Cleaning TP: {$tp->code}" . PHP_EOL;
    foreach ($tp->lessons as $l) {
        foreach ($l->attachments as $att) {
            if ($att->file_path && Storage::disk('local')->exists($att->file_path)) {
                Storage::disk('local')->delete($att->file_path);
            }
            $att->delete();
        }
        $l->classrooms()->detach();
        $l->delete();
    }
    foreach ($tp->assessments as $a) {
        foreach ($a->submissions as $s) {
            if ($s->file_path && Storage::disk('local')->exists($s->file_path)) {
                Storage::disk('local')->delete($s->file_path);
            }
            $s->delete();
        }
        $a->classrooms()->detach();
        $a->delete();
    }
    $tp->delete();
}
User::where('email', 'like', 'siswa_uji_%')->delete();
User::where('email', 'like', 'siswa_lain_%')->delete();
\App\Models\Note::where('body', 'like', '%Protokol TCP/IP%')->delete();
\App\Models\Discussion::where('body', 'like', '%switch jaringan%')->delete();
\App\Models\LessonProgress::where('lesson_id', 21)->delete();
echo "Clean up complete!" . PHP_EOL;
