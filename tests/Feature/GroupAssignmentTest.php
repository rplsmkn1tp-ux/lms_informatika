<?php

use App\Models\User;
use App\Models\Classroom;
use App\Models\Assessment;
use App\Models\AssessmentGroup;
use App\Models\Element;
use App\Models\Submission;
use Livewire\Livewire;
use function Pest\Laravel\seed;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->ay = \App\Models\AcademicYear::first() ?? \App\Models\AcademicYear::create([
        'name' => '2026/2027',
        'semester' => 'ganjil',
        'is_active' => true,
    ]);

    $this->classroom = Classroom::first() ?? Classroom::create([
        'name' => 'X RPL 1',
        'join_code' => 'XRPL1-A2B3',
        'academic_year_id' => $this->ay->id,
        'weekly_lesson_target' => 5,
    ]);

    $this->guru = User::factory()->create([
        'role' => 'guru',
        'status' => 'active',
    ]);

    $this->siswa1 = User::factory()->create([
        'role' => 'siswa',
        'name' => 'Siswa Satu',
        'status' => 'active',
    ]);
    $this->classroom->students()->attach($this->siswa1->id, ['academic_year_id' => $this->ay->id]);

    $this->siswa2 = User::factory()->create([
        'role' => 'siswa',
        'name' => 'Siswa Dua',
        'status' => 'active',
    ]);
    $this->classroom->students()->attach($this->siswa2->id, ['academic_year_id' => $this->ay->id]);

    $this->siswa3 = User::factory()->create([
        'role' => 'siswa',
        'name' => 'Siswa Tiga',
        'status' => 'active',
    ]);
    $this->classroom->students()->attach($this->siswa3->id, ['academic_year_id' => $this->ay->id]);

    $this->element = Element::first() ?? Element::create([
        'name' => 'Berpikir Komputasional',
        'code' => 'BK',
    ]);

    // Buat asesmen dengan mode kelompok
    $this->assessment = Assessment::create([
        'title' => 'Tugas Proyek Kelompok',
        'type' => 'formatif',
        'assignment_mode' => 'group',
        'max_group_members' => 3,
        'status' => 'published',
        'element_id' => $this->element->id,
        'submission_types' => ['text', 'link'],
        'max_score' => 100,
        'created_by' => $this->guru->id,
    ]);

    $this->assessment->classrooms()->attach($this->classroom, [
        'opens_at' => now()->subDay(),
        'due_at' => now()->addDays(3),
    ]);
});

test('siswa dapat membentuk kelompok tugas baru dan menjadi ketua', function () {
    Livewire::actingAs($this->siswa1)
        ->test(\App\Livewire\Siswa\Tugas\KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('newGroupName', 'Tim Inovasi')
        ->set('selectedMemberIds', [$this->siswa2->id])
        ->call('createGroup')
        ->assertHasNoErrors()
        ->assertSee('Tim Inovasi');

    $this->assertDatabaseHas('assessment_groups', [
        'assessment_id' => $this->assessment->id,
        'name' => 'Tim Inovasi',
        'leader_id' => $this->siswa1->id,
    ]);

    $group = AssessmentGroup::where('assessment_id', $this->assessment->id)->first();
    expect($group->members)->toHaveCount(2);
    expect($group->hasMember($this->siswa1->id))->toBeTrue();
    expect($group->hasMember($this->siswa2->id))->toBeTrue();
});

test('ketua kelompok dapat mengumpulkan tugas beserta laporan kontribusi anggota', function () {
    // Bentuk kelompok
    $group = AssessmentGroup::create([
        'assessment_id' => $this->assessment->id,
        'classroom_id' => $this->classroom->id,
        'name' => 'Tim Hebat',
        'leader_id' => $this->siswa1->id,
    ]);
    $group->members()->attach([$this->siswa1->id, $this->siswa2->id]);

    // Ketua submit tugas
    Livewire::actingAs($this->siswa1)
        ->test(\App\Livewire\Siswa\Tugas\KerjakanTugas::class, ['assessment' => $this->assessment])
        ->set('textContent', 'Ini adalah jawaban tugas kelompok kami.')
        ->set('contributionNotes', "1. Siswa 1: Analisis data\n2. Siswa 2: Desain solusi")
        ->call('submit')
        ->assertHasNoErrors();

    // Verifikasi catatan kontribusi tersimpan
    $group->refresh();
    expect($group->contribution_notes)->toContain('Siswa 1: Analisis data');

    // Verifikasi submission dibuat untuk kedua siswa
    $this->assertDatabaseHas('submissions', [
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->siswa1->id,
        'assessment_group_id' => $group->id,
        'status' => Submission::STATUS_SUBMITTED,
    ]);

    $this->assertDatabaseHas('submissions', [
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->siswa2->id,
        'assessment_group_id' => $group->id,
        'status' => Submission::STATUS_SUBMITTED,
    ]);
});

test('anggota kelompok non-ketua tidak dapat mengumpulkan tugas sendiri', function () {
    $group = AssessmentGroup::create([
        'assessment_id' => $this->assessment->id,
        'classroom_id' => $this->classroom->id,
        'name' => 'Tim Hebat',
        'leader_id' => $this->siswa1->id,
    ]);
    $group->members()->attach([$this->siswa1->id, $this->siswa2->id]);

    Livewire::actingAs($this->siswa2)
        ->test(\App\Livewire\Siswa\Tugas\KerjakanTugas::class, ['assessment' => $this->assessment])
        ->assertSet('isLeader', false)
        ->assertSet('isLocked', true)
        ->assertSee('Pengumpulan Dilakukan oleh Ketua');
});

test('guru dapat menilai kelompok dengan override nilai individual untuk anggota', function () {
    $group = AssessmentGroup::create([
        'assessment_id' => $this->assessment->id,
        'classroom_id' => $this->classroom->id,
        'name' => 'Tim Alpha',
        'leader_id' => $this->siswa1->id,
        'contribution_notes' => "Siswa 1 sangat aktif, Siswa 2 pasif",
    ]);
    $group->members()->attach([$this->siswa1->id, $this->siswa2->id]);

    // Leader submit
    Submission::create([
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->siswa1->id,
        'assessment_group_id' => $group->id,
        'attempt' => 1,
        'text_content' => 'Jawaban Tim Alpha',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);
    Submission::create([
        'assessment_id' => $this->assessment->id,
        'student_id' => $this->siswa2->id,
        'assessment_group_id' => $group->id,
        'attempt' => 1,
        'text_content' => 'Jawaban Tim Alpha',
        'status' => Submission::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    // Guru buka detail dan beri nilai
    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\Tugas\Detail::class, ['assessment' => $this->assessment])
        ->call('openGroupGradingModal', $group->id)
        ->assertSet('showGroupGradingModal', true)
        ->set("memberScores.{$this->siswa1->id}", 90)
        ->set("memberFeedbacks.{$this->siswa1->id}", 'Sangat memuaskan')
        ->set("memberScores.{$this->siswa2->id}", 70)
        ->set("memberFeedbacks.{$this->siswa2->id}", 'Perlu ditingkatkan keaktifannya')
        ->call('saveGroupGrade')
        ->assertHasNoErrors();

    // Verifikasi di database nilai masing-masing anggota sesuai override
    $sub1 = Submission::where('assessment_id', $this->assessment->id)->where('student_id', $this->siswa1->id)->first();
    $sub2 = Submission::where('assessment_id', $this->assessment->id)->where('student_id', $this->siswa2->id)->first();

    expect($sub1->score)->toEqual(90);
    expect($sub1->status)->toEqual(Submission::STATUS_GRADED);
    expect($sub1->feedback)->toEqual('Sangat memuaskan');

    expect($sub2->score)->toEqual(70);
    expect($sub2->status)->toEqual(Submission::STATUS_GRADED);
    expect($sub2->feedback)->toEqual('Perlu ditingkatkan keaktifannya');
});

test('guru dapat memasukkan siswa yang belum memiliki kelompok ke dalam kelompok yang ada', function () {
    $group = AssessmentGroup::create([
        'assessment_id' => $this->assessment->id,
        'classroom_id' => $this->classroom->id,
        'name' => 'Tim Beta',
        'leader_id' => $this->siswa1->id,
    ]);
    $group->members()->attach([$this->siswa1->id]);

    // Siswa 3 belum memiliki kelompok
    Livewire::actingAs($this->guru)
        ->test(\App\Livewire\Guru\Tugas\Detail::class, ['assessment' => $this->assessment])
        ->call('assignStudentToGroup', $this->siswa3->id, $group->id)
        ->assertHasNoErrors();

    expect($group->fresh()->hasMember($this->siswa3->id))->toBeTrue();
});
