<?php

namespace Tests\Feature\Auth;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        $this->classroom = Classroom::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'X RPL 1',
            'grade' => 10,
            'major' => 'RPL',
            'join_code' => 'XRPL1',
        ]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Buat Akun Baru');
        $response->assertSee('Minimal 5 karakter');
    }

    public function test_siswa_can_register_with_valid_5_char_password_with_uppercase_and_symbol(): void
    {
        $response = $this->post(route('register.store'), [
            'role' => 'siswa',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'classroom_id' => $this->classroom->id,
            'nisn' => '1234567890',
            'password' => 'Budi!', // exactly 5 chars, uppercase B, symbol !
            'password_confirmation' => 'Budi!',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Santoso',
            'role' => 'siswa',
            'status' => 'active',
        ]);
    }

    public function test_siswa_registration_fails_if_password_under_5_chars(): void
    {
        $response = $this->post(route('register.store'), [
            'role' => 'siswa',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'classroom_id' => $this->classroom->id,
            'password' => 'Bud!', // 4 chars
            'password_confirmation' => 'Bud!',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_siswa_registration_fails_if_password_lacks_uppercase_or_symbol(): void
    {
        // No uppercase
        $response = $this->post(route('register.store'), [
            'role' => 'siswa',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'classroom_id' => $this->classroom->id,
            'password' => 'budi!',
            'password_confirmation' => 'budi!',
        ]);
        $response->assertSessionHasErrors(['password']);

        // No symbol
        $response = $this->post(route('register.store'), [
            'role' => 'siswa',
            'name' => 'Budi Santoso',
            'email' => 'budi2@example.com',
            'classroom_id' => $this->classroom->id,
            'password' => 'Budii',
            'password_confirmation' => 'Budii',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    public function test_guru_can_register_with_valid_5_char_password_with_uppercase_and_symbol(): void
    {
        $response = $this->post(route('register.store'), [
            'role' => 'guru',
            'name' => 'Dewi Anggraini',
            'email' => 'dewi@example.com',
            'password' => 'Dewi@', // 5 chars, uppercase D, symbol @
            'password_confirmation' => 'Dewi@',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'dewi@example.com',
            'name' => 'Dewi Anggraini',
            'role' => 'guru',
            'status' => 'pending',
        ]);
    }

    public function test_guru_registration_fails_if_password_invalid(): void
    {
        // 4 chars
        $response = $this->post(route('register.store'), [
            'role' => 'guru',
            'name' => 'Dewi Anggraini',
            'email' => 'dewi@example.com',
            'password' => 'Dew@',
            'password_confirmation' => 'Dew@',
        ]);
        $response->assertSessionHasErrors(['password']);

        // No symbol
        $response = $this->post(route('register.store'), [
            'role' => 'guru',
            'name' => 'Dewi Anggraini',
            'email' => 'dewi2@example.com',
            'password' => 'Dewii',
            'password_confirmation' => 'Dewii',
        ]);
        $response->assertSessionHasErrors(['password']);
    }
}
