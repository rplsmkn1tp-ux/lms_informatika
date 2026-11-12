<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Profil;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_page_is_displayed_for_siswa()
    {
        $user = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($user)->get('/profil');

        $response->assertOk();
        $response->assertSeeLivewire(Profil::class);
    }

    public function test_profil_information_can_be_updated()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Profil::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_must_be_unique_when_updating()
    {
        $user1 = User::factory()->create(['email' => 'test1@example.com']);
        $user2 = User::factory()->create(['email' => 'test2@example.com']);

        Livewire::actingAs($user1)
            ->test(Profil::class)
            ->set('name', 'Test User')
            ->set('email', 'test2@example.com')
            ->call('updateProfile')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_password_can_be_updated()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Profil::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(auth()->validate([
            'email' => $user->email,
            'password' => 'new-password',
        ]));
    }

    public function test_correct_password_must_be_provided_to_update_password()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Profil::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }

    public function test_rate_limiting_applies_to_password_update()
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(Profil::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password');

        for ($i = 0; $i < 5; $i++) {
            $component->call('updatePassword');
        }

        $component->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }
}
