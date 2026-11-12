<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

echo "=== LOGIN SIMULATION ===" . PHP_EOL;
echo "Fortify username field: " . Fortify::username() . PHP_EOL;
echo "Fortify lowercase: " . (config('fortify.lowercase_usernames') ? 'YES' : 'NO') . PHP_EOL . PHP_EOL;

// Simulate login with 'andi@guru.id' / 'password'
$loginId = 'andi@guru.id';
$password = 'password';

// Fortify lowercases if configured
if (config('fortify.lowercase_usernames')) {
    $loginId = strtolower($loginId);
}

echo "Searching for login_id: '{$loginId}'" . PHP_EOL;

$user = User::where('email', $loginId)
    ->orWhere('nisn', $loginId)
    ->orWhere('nip_nuptk', $loginId)
    ->first();

if (!$user) {
    echo "RESULT: User NOT FOUND!" . PHP_EOL;
    // Check if email exists with different case
    $similar = User::whereRaw('LOWER(email) = ?', [strtolower($loginId)])->first();
    if ($similar) {
        echo "  Found with case-insensitive: {$similar->email}" . PHP_EOL;
    }
} else {
    echo "RESULT: User found - {$user->name} ({$user->email})" . PHP_EOL;
    echo "  Password check: " . (Hash::check($password, $user->password) ? 'PASS' : 'FAIL') . PHP_EOL;
    echo "  Status: {$user->status}" . PHP_EOL;
    echo "  Status check: " . ($user->status === 'active' ? 'PASS' : 'FAIL - account not active') . PHP_EOL;
}

echo PHP_EOL . "=== ALL USERS PASSWORD='password' CHECK ===" . PHP_EOL;
foreach (User::all() as $u) {
    $match = Hash::check('password', $u->password);
    echo "  {$u->email} (role={$u->role}): " . ($match ? 'YES' : 'NO') . PHP_EOL;
}

