<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lesson;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

// Restore Lesson 1 if missing
if (!Lesson::find(1)) {
    DB::table('lessons')->insert([
        'id' => 1,
        'learning_objective_id' => 5,
        'code' => '5.1',
        'title' => 'Perangkat keras & perangkat lunak',
        'content' => '',
        'sort_order' => 1,
        'status' => 'published',
        'created_by' => 1,
        'created_at' => '2026-10-07 06:50:04',
        'updated_at' => '2026-10-07 06:50:04',
    ]);
    echo "Restored Lesson 1\n";
}

// Restore Lesson 2 if missing
if (!Lesson::find(2)) {
    DB::table('lessons')->insert([
        'id' => 2,
        'learning_objective_id' => 5,
        'code' => '5.2',
        'title' => 'Model komputer Von Neumann',
        'content' => '<p>Tujuan pembelajaran: memahami komponen utama CPU, RAM, I/O, dan bus. <strong>Catatan: RAM bersifat volatil.</strong></p>',
        'sort_order' => 2,
        'status' => 'published',
        'created_by' => 1,
        'created_at' => '2026-10-07 06:50:04',
        'updated_at' => '2026-10-07 06:50:04',
    ]);
    echo "Restored Lesson 2\n";
}

// Restore Lesson 6 if missing
if (!Lesson::find(6)) {
    DB::table('lessons')->insert([
        'id' => 6,
        'learning_objective_id' => 1,
        'code' => '12',
        'title' => '1sdfsdf',
        'summary' => 'sfxsdgaf',
        'content' => 'xsfdf',
        'estimated_minutes' => 55,
        'sort_order' => 1,
        'status' => 'published',
        'created_by' => 2,
        'created_at' => '2026-10-07 06:58:45',
        'updated_at' => '2026-10-07 06:59:31',
    ]);
    DB::table('lesson_classroom')->updateOrInsert(
        ['lesson_id' => 6, 'classroom_id' => 1],
        ['available_from' => null]
    );
    echo "Restored Lesson 6\n";
}

// Link assessments back to lessons
Assessment::where('id', 1)->update(['lesson_id' => 2]);
Assessment::where('id', 7)->update(['lesson_id' => 6]);
Assessment::where('id', 9)->update(['lesson_id' => 1]);
Assessment::where('id', 11)->update(['lesson_id' => 1]);

echo "Lessons & Assessments verified!\n";
