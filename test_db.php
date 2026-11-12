<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$siswa = \App\Models\User::where('email', 'siswa@example.com')->first();
echo 'Siswa ID: ' . ($siswa ? $siswa->id : 'Not Found') . PHP_EOL;
if($siswa){
    $classroomIds = $siswa->classrooms()->pluck('classrooms.id')->toArray();
    echo 'Classroom IDs: ' . implode(',', $classroomIds) . PHP_EOL;
    $lessons = \DB::table('lesson_classroom')->whereIn('classroom_id', $classroomIds)->get();
    echo 'Lessons in lesson_classroom for Siswa Andi: ' . count($lessons) . PHP_EOL;
    foreach($lessons as $lc) {
        $lesson = \DB::table('lessons')->where('id', $lc->lesson_id)->first();
        echo '- Lesson ' . $lesson->id . ' (' . $lesson->title . '): status=' . $lesson->status . ', available_from=' . $lc->available_from . ', classroom_id=' . $lc->classroom_id . PHP_EOL;
    }
}
