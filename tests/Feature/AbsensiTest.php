<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\TeachingAssignment;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Services\AttendanceService;
use Livewire\Livewire;
use App\Livewire\Guru\Absensi\SessionForm;
use App\Livewire\Guru\Absensi\SessionManager;
use Carbon\Carbon;

beforeEach(function () {
    // Setup Admin
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    
    // Setup Academic Year
    $this->academicYear = AcademicYear::create([
        'name' => '2026/2027 Ganjil',
        'is_active' => true,
    ]);

    // Setup Guru & Kelas A
    $this->guruA = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    $this->classA = Classroom::create([
        'name' => 'X PPLG 1',
        'academic_year_id' => $this->academicYear->id,
        'grade' => 10,
        'major' => 'PPLG',
        'weekly_lesson_target' => 4,
        'join_code' => 'XPPLG1',
    ]);
    TeachingAssignment::create([
        'teacher_id' => $this->guruA->id,
        'classroom_id' => $this->classA->id,
        'academic_year_id' => $this->academicYear->id,
    ]);

    // Setup Siswa di Kelas A
    $this->siswaA1 = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    $this->siswaA2 = User::factory()->create(['role' => 'siswa', 'status' => 'active']);
    $this->classA->students()->attach([
        $this->siswaA1->id => ['academic_year_id' => $this->academicYear->id],
        $this->siswaA2->id => ['academic_year_id' => $this->academicYear->id],
    ]);

    // Setup Guru B & Kelas B
    $this->guruB = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    $this->classB = Classroom::create([
        'name' => 'X PPLG 2',
        'academic_year_id' => $this->academicYear->id,
        'grade' => 10,
        'major' => 'PPLG',
        'weekly_lesson_target' => 4,
        'join_code' => 'XPPLG2',
    ]);
    TeachingAssignment::create([
        'teacher_id' => $this->guruB->id,
        'classroom_id' => $this->classB->id,
        'academic_year_id' => $this->academicYear->id,
    ]);
});

it('guru dapat melihat daftar kelas untuk absensi', function () {
    $this->actingAs($this->guruA)
        ->get(route('guru.absensi.index'))
        ->assertStatus(200)
        ->assertSee('X PPLG 1');
});

it('guru ditolak membuka kelola sesi kelas guru lain', function () {
    // Guru A coba akses kelas B
    $this->actingAs($this->guruA)
        ->get(route('guru.absensi.session', $this->classB->id))
        ->assertStatus(403);
});

it('siswa ditolak membuka rute absensi guru', function () {
    $this->actingAs($this->siswaA1)
        ->get(route('guru.absensi.index'))
        ->assertStatus(403);
});

it('guru dapat membuat sesi absensi baru dan menyimpan kehadiran siswa', function () {
    Livewire::actingAs($this->guruA)
        ->test(SessionForm::class, ['classroom' => $this->classA, 'sessionId' => 'new'])
        ->set('meeting_date', '2026-10-01')
        ->set('meeting_no', 1)
        ->set('topic', 'Test Absen')
        ->set('attendance.' . $this->siswaA1->id, 'hadir')
        ->set('attendance.' . $this->siswaA2->id, 'izin')
        ->call('save')
        ->assertRedirect(route('guru.absensi.session', $this->classA->id));

    $this->assertDatabaseHas('attendance_sessions', [
        'classroom_id' => $this->classA->id,
        'meeting_no' => 1,
        'topic' => 'Test Absen',
        'status' => 'open'
    ]);

    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $this->siswaA1->id,
        'status' => 'hadir'
    ]);

    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $this->siswaA2->id,
        'status' => 'izin'
    ]);
});

it('guru dapat menutup sesi absensi', function () {
    $session = AttendanceSession::create([
        'classroom_id' => $this->classA->id,
        'teacher_id' => $this->guruA->id,
        'academic_year_id' => $this->academicYear->id,
        'meeting_date' => '2026-10-01',
        'meeting_no' => 1,
        'status' => 'open',
    ]);

    Livewire::actingAs($this->guruA)
        ->test(SessionForm::class, ['classroom' => $this->classA, 'sessionId' => $session->id])
        ->call('closeSession')
        ->assertRedirect(route('guru.absensi.session', $this->classA->id));

    $this->assertDatabaseHas('attendance_sessions', [
        'id' => $session->id,
        'status' => 'closed'
    ]);
});

it('guru harus menyertakan alasan jika mengedit sesi yang sudah ditutup', function () {
    $session = AttendanceSession::create([
        'classroom_id' => $this->classA->id,
        'teacher_id' => $this->guruA->id,
        'academic_year_id' => $this->academicYear->id,
        'meeting_date' => '2026-10-01',
        'meeting_no' => 1,
        'status' => 'closed',
    ]);

    Livewire::actingAs($this->guruA)
        ->test(SessionForm::class, ['classroom' => $this->classA, 'sessionId' => $session->id])
        ->set('edit_reason', '') // Kosong -> harus gagal
        ->call('save')
        ->assertHasErrors(['edit_reason']);

    Livewire::actingAs($this->guruA)
        ->test(SessionForm::class, ['classroom' => $this->classA, 'sessionId' => $session->id])
        ->set('edit_reason', 'Ada siswa susulan')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('guru.absensi.session', $this->classA->id));

    $this->assertDatabaseHas('attendance_sessions', [
        'id' => $session->id,
        'edit_reason' => 'Ada siswa susulan'
    ]);
});

it('service perhitungan absensi menghitung persentase dengan benar', function () {
    // 2 sesi tertutup. Sesi 1: siswa1 hadir, siswa2 alpa. Sesi 2: siswa1 izin, siswa2 hadir.
    $session1 = AttendanceSession::create([
        'classroom_id' => $this->classA->id,
        'teacher_id' => $this->guruA->id,
        'academic_year_id' => $this->academicYear->id,
        'meeting_date' => '2026-10-01',
        'meeting_no' => 1,
        'status' => 'closed',
    ]);
    AttendanceRecord::create(['session_id' => $session1->id, 'student_id' => $this->siswaA1->id, 'status' => 'hadir']);
    AttendanceRecord::create(['session_id' => $session1->id, 'student_id' => $this->siswaA2->id, 'status' => 'alpa']);

    $session2 = AttendanceSession::create([
        'classroom_id' => $this->classA->id,
        'teacher_id' => $this->guruA->id,
        'academic_year_id' => $this->academicYear->id,
        'meeting_date' => '2026-10-02',
        'meeting_no' => 2,
        'status' => 'closed',
    ]);
    AttendanceRecord::create(['session_id' => $session2->id, 'student_id' => $this->siswaA1->id, 'status' => 'izin']);
    AttendanceRecord::create(['session_id' => $session2->id, 'student_id' => $this->siswaA2->id, 'status' => 'hadir']);

    // Sesi 3 terbuka (tidak dihitung)
    $session3 = AttendanceSession::create([
        'classroom_id' => $this->classA->id,
        'teacher_id' => $this->guruA->id,
        'academic_year_id' => $this->academicYear->id,
        'meeting_date' => '2026-10-03',
        'meeting_no' => 3,
        'status' => 'open',
    ]);
    AttendanceRecord::create(['session_id' => $session3->id, 'student_id' => $this->siswaA1->id, 'status' => 'hadir']);

    $service = new AttendanceService();
    
    // Siswa 1: 1 hadir dari 2 sesi tertutup = 50%
    $pct1 = $service->getStudentAttendancePercentage($this->siswaA1->id, $this->classA->id, $this->academicYear->id);
    expect($pct1)->toEqual(50.0);

    // Siswa 2: 1 hadir dari 2 sesi tertutup = 50%
    $pct2 = $service->getStudentAttendancePercentage($this->siswaA2->id, $this->classA->id, $this->academicYear->id);
    expect($pct2)->toEqual(50.0);
});
