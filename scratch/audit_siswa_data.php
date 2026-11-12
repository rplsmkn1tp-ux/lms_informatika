<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\Element;
use App\Models\LearningObjective;
use Illuminate\Support\Facades\Hash;

echo "========================================================\n";
echo "      INSPEKSI DATA SISWA (STUDENTS) DI DATABASE\n";
echo "========================================================\n\n";

$students = User::where('role', 'siswa')->with('classrooms')->get();
echo "Total Siswa Terdaftar: " . $students->count() . "\n\n";

$validStudents = [];
foreach ($students as $s) {
    $hasPasswordMatch = Hash::check('password', $s->password);
    $cls = $s->classrooms->pluck('name')->join(', ');
    echo "ID: {$s->id} | {$s->name} ({$s->email}) | NISN: {$s->nisn} | Kelas: [{$cls}] | Status: {$s->status} | Pwd='password': " . ($hasPasswordMatch ? 'YES' : 'NO') . "\n";
    if ($hasPasswordMatch) {
        $validStudents[] = $s;
    }
}

echo "\nSiswa dengan sandi 'password': " . count($validStudents) . "\n";
foreach ($validStudents as $vs) {
    echo "  -> ID: {$vs->id} | {$vs->email} | Kelas: " . $vs->classrooms->pluck('name')->join(', ') . "\n";
}

// Elements & TPs
$elements = Element::with('learningObjectives')->get();
echo "\nTotal Elemen Kurikulum: " . $elements->count() . "\n";
foreach ($elements as $el) {
    $tps = $el->learningObjectives->pluck('code')->join(', ');
    echo "  - Elemen: {$el->code} ({$el->name}) | TPs: [{$tps}]\n";
}

// Lessons
$lessons = Lesson::with(['classrooms', 'learningObjective.element'])->get();
echo "\nTotal Materi (Lessons): " . $lessons->count() . "\n";
foreach ($lessons as $l) {
    $cls = $l->classrooms->pluck('name')->join(', ');
    echo "  - ID: {$l->id} | {$l->title} | Status: {$l->status} | TP: " . ($l->learningObjective ? $l->learningObjective->code : 'None') . " | Kelas: [{$cls}]\n";
}

// Assessments
$assessments = Assessment::with('classrooms')->get();
echo "\nTotal Asesmen/Tugas: " . $assessments->count() . "\n";
foreach ($assessments as $a) {
    $cls = $a->classrooms->pluck('name')->join(', ');
    echo "  - ID: {$a->id} | {$a->title} | Mode: {$a->assignment_mode} | Kelas: [{$cls}]\n";
}

