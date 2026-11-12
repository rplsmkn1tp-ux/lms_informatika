<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classroom;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Livewire\Guru\Absensi\SessionForm;
use Livewire\Livewire;

$teacher = User::where('role', 'guru')->first();
$classroom4 = Classroom::find(4); // 10 Pemasaran 1

echo "Testing SessionForm Livewire component as Teacher {$teacher->name} for Classroom {$classroom4->name}...\n";

// Test Livewire SessionForm for new session
$test = Livewire::actingAs($teacher)
    ->test(SessionForm::class, ['classroom' => $classroom4, 'sessionId' => 'new'])
    ->set('meeting_date', '2026-10-08')
    ->set('meeting_no', 12)
    ->set('topic', 'Pemasaran Digital & E-Commerce')
    ->call('save');

$test->assertHasNoErrors();

// Verify in DB
$session = AttendanceSession::where('classroom_id', 4)
    ->where('meeting_date', '2026-10-08')
    ->first();

if ($session) {
    echo "SUCCESS: Attendance Session created on 2026-10-08 for Classroom 4! (ID: {$session->id}, Status: {$session->status})\n";
    $records = AttendanceRecord::where('session_id', $session->id)->get();
    echo "Records count: " . $records->count() . "\n";
    foreach ($records as $r) {
        echo "  - Student ID: {$r->student_id} ({$r->student->name}) | Status: {$r->status}\n";
    }
} else {
    echo "FAILED: Session not created.\n";
}

// Now test Student 27 viewing KehadiranSaya
$student27 = User::find(27);
$testStudent = Livewire::actingAs($student27)
    ->test(\App\Livewire\Siswa\Absensi\KehadiranSaya::class);

$testStudent->assertStatus(200);
$testStudent->assertSee('08/10/2026');
$testStudent->assertSee('Pemasaran Digital & E-Commerce');
$testStudent->assertSee('21/10/2026');
echo "SUCCESS: Student 27 sees both 08/10/2026 and 21/10/2026 in KehadiranSaya!\n";

