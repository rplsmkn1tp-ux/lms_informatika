<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

test('active users can login', function () {
    $user = User::factory()->create([
        'status' => 'active',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'login_id' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard'); // Fortify default or home route
});

test('pending, rejected, and inactive users cannot login', function () {
    $statuses = ['pending', 'rejected', 'inactive'];

    foreach ($statuses as $status) {
        $user = User::factory()->create([
            'status' => $status,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login_id' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login_id');
    }
});
