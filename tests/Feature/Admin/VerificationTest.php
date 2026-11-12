<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherAccountApproved;
use App\Mail\TeacherAccountRejected;
use Livewire\Livewire;
use App\Livewire\Admin\Verification;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
});

test('admin can see pending teachers', function () {
    $teacher = User::factory()->create(['role' => 'guru', 'status' => 'pending']);
    $activeTeacher = User::factory()->create(['role' => 'guru', 'status' => 'active']);

    $this->actingAs($this->admin)
        ->get(route('admin.verifikasi'))
        ->assertOk()
        ->assertSee($teacher->name)
        ->assertSee($activeTeacher->name);
});

test('admin can approve teacher', function () {
    Mail::fake();
    $teacher = User::factory()->create(['role' => 'guru', 'status' => 'pending']);

    Livewire::actingAs($this->admin)
        ->test(Verification::class)
        ->call('approve', $teacher->id);

    $teacher->refresh();
    expect($teacher->status)->toBe('active')
        ->and($teacher->approved_at)->not->toBeNull()
        ->and($teacher->approved_by)->toBe($this->admin->id);

    Mail::assertSent(TeacherAccountApproved::class, function ($mail) use ($teacher) {
        return $mail->hasTo($teacher->email);
    });
});

test('admin can reject teacher with reason', function () {
    Mail::fake();
    $teacher = User::factory()->create(['role' => 'guru', 'status' => 'pending']);

    Livewire::actingAs($this->admin)
        ->test(Verification::class)
        ->set('rejectionReason', 'Data tidak valid')
        ->call('reject', $teacher->id);

    $teacher->refresh();
    expect($teacher->status)->toBe('rejected')
        ->and($teacher->rejection_reason)->toBe('Data tidak valid');

    Mail::assertSent(TeacherAccountRejected::class, function ($mail) use ($teacher) {
        return $mail->hasTo($teacher->email);
    });
});

test('non-admin cannot access verification page', function () {
    $guru = User::factory()->create(['role' => 'guru', 'status' => 'active']);
    $siswa = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

    $this->actingAs($guru)->get(route('admin.verifikasi'))->assertForbidden();
    $this->actingAs($siswa)->get(route('admin.verifikasi'))->assertForbidden();
});
