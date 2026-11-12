<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classroom;
use App\Models\TeachingAssignment;
use App\Models\Lesson;
use App\Models\Assessment;
use App\Models\AttendanceSession;
use App\Models\AcademicYear;

echo "========================================================\n";
echo "      AUDIT DATA AKUN GURU (TEACHER)\n";
echo "========================================================\n\n";

$guru = User::where('role', 'guru')->first();
if (!$guru) {
    echo "  [FAIL] Tidak ada user role='guru' di database!\n";
    exit(1);
}

echo "Data Guru:\n";
echo "  - ID       : {$guru->id}\n";
echo "  - Nama     : {$guru->name}\n";
echo "  - Email    : {$guru->email}\n";
echo "  - NIP      : " . ($guru->nip_nuptk ?: 'Belum diisi') . "\n";
echo "  - Mapel    : " . ($guru->subject ?: 'Informatika') . "\n";
echo "  - Status   : {$guru->status}\n";
echo "  - Password : " . (\Illuminate\Support\Facades\Hash::check('password', $guru->password) ? "'password' [VALID]" : "Bukan 'password'") . "\n\n";

// Teaching assignments
$assignments = TeachingAssignment::with('classroom')->where('teacher_id', $guru->id)->get();
echo "Plot Mengajar ({$assignments->count()} Kelas):\n";
foreach ($assignments as $a) {
    echo "  - [ID: {$a->id}] Kelas: " . ($a->classroom ? $a->classroom->name : 'N/A') . " (ID: {$a->classroom_id}) | Mapel: {$a->subject}\n";
}

$sampleClassroom = $assignments->first()?->classroom ?? Classroom::first();
$sampleAssignment = $assignments->first();
$activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
$sampleLesson = Lesson::first();
$sampleAssessment = Assessment::first();

echo "\nEntity Sampel untuk Testing:\n";
echo "  - Classroom ID : " . ($sampleClassroom ? $sampleClassroom->id . " ({$sampleClassroom->name})" : "None") . "\n";
echo "  - Assignment ID: " . ($sampleAssignment ? $sampleAssignment->id : "None") . "\n";
echo "  - Academic Year: " . ($activeYear ? $activeYear->id . " ({$activeYear->name})" : "None") . "\n";
echo "  - Lesson ID    : " . ($sampleLesson ? $sampleLesson->id . " ({$sampleLesson->title})" : "None") . "\n";
echo "  - Assessment ID: " . ($sampleAssessment ? $sampleAssessment->id . " ({$sampleAssessment->title})" : "None") . "\n";

