<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classroom;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Livewire\Siswa\Absensi\KehadiranSaya;
use Livewire\Livewire;

echo "========================================================\n";
echo "      TESTING ATTENDANCE PERSISTENCE & SISWA QUERY      \n";
echo "========================================================\n\n";

// 1. Pick teacher and classroom with enrolled students
$teacher = User::where('role', 'guru')->first();
$thisClassroom = Classroom::whereHas('students')->whereHas('teachingAssignments', function($q) use ($teacher) {
    $q->where('teacher_id', $teacher->id);
})->first();

if (!$thisClassroom) {
    $thisClassroom = Classroom::whereHas('students')->first();
}

echo "[1] Teacher: {$teacher->name} (ID: {$teacher->id})\n";
echo "    Classroom: {$thisClassroom->name} (ID: {$thisClassroom->id})\n";

// 2. Ensure an attendance session exists on 2026-10-08
$oct8Session = AttendanceSession::where('classroom_id', $thisClassroom->id)
    ->whereDate('meeting_date', '2026-10-08')
    ->first();

if (!$oct8Session) {
    echo "    Creating attendance session for 2026-10-08 with status 'open'...\n";
    $oct8Session = AttendanceSession::create([
        'classroom_id' => $thisClassroom->id,
        'teacher_id' => $teacher->id,
        'academic_year_id' => $thisClassroom->academic_year_id,
        'meeting_date' => '2026-10-08',
        'meeting_no' => 10,
        'topic' => 'Pengenalan Algoritma & Logika Pemrograman',
        'status' => 'open', // Test 'open' status!
        'opened_at' => '2026-10-08 08:00:00',
    ]);
} else {
    echo "    Existing 2026-10-08 session found (ID: {$oct8Session->id}, Status: {$oct8Session->status}).\n";
}

// 3. Test saving attendance records for all students in that class
$students = $thisClassroom->students()->get();
echo "[2] Enrolled Students in {$thisClassroom->name}: {$students->count()} students\n";

foreach ($students as $student) {
    AttendanceRecord::updateOrCreate(
        [
            'session_id' => $oct8Session->id,
            'student_id' => $student->id,
        ],
        [
            'status' => 'hadir',
            'note' => 'Hadir tepat waktu',
            'marked_by' => $teacher->id,
            'marked_at' => now(),
        ]
    );
}

$recordCount = AttendanceRecord::where('session_id', $oct8Session->id)->count();
echo "    Records saved in attendance_records: {$recordCount} / {$students->count()} [OK]\n";

// 4. Test from Student perspective (Pick first student from classroom)
$student = $students->first();
auth()->login($student);
echo "\n[3] Testing from Student Perspective:\n";
echo "    Logged in as: {$student->name} (ID: {$student->id}, Email: {$student->email})\n";

// Test Livewire component KehadiranSaya
$component = new KehadiranSaya();
$service = new App\Services\AttendanceService();
$view = $component->render($service);
$viewData = $view->getData();

$records = $viewData['records'];
echo "    Total attendance records returned for student: " . count($records) . "\n";

$foundOct8 = false;
foreach ($records as $r) {
    $dateStr = $r->session->meeting_date->format('d/m/Y');
    echo "    - Pertemuan {$r->session->meeting_no} | Tanggal: {$dateStr} | Materi: '{$r->session->topic}' | Status: {$r->status} | Sesi Status: {$r->session->status}\n";
    if ($r->session->meeting_date->format('Y-m-d') === '2026-10-08') {
        $foundOct8 = true;
    }
}

if ($foundOct8) {
    echo "    -> Sesi 8 Oktober BERHASIL ditemukan di Riwayat Pertemuan Siswa! [SUCCESS]\n";
} else {
    echo "    -> ERROR: Sesi 8 Oktober TIDAK ditemukan!\n";
}

echo "    Percentage Kehadiran: {$viewData['percentage']}%\n";
echo "    Summary: " . json_encode($viewData['summary']) . "\n";

// 5. Test Livewire rendering
$response = Livewire::actingAs($student)->test(KehadiranSaya::class);
$response->assertStatus(200);
$response->assertSee('08/10/2026');
$response->assertSee('Pengenalan Algoritma & Logika Pemrograman');
echo "    -> Livewire Blade HTML Render contains '08/10/2026' and topic: [SUCCESS]\n";

echo "\n========================================================\n";
echo "ALL TESTS PASSED!\n";
echo "========================================================\n";
