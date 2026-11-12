<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== PASSWORD CHECK ===" . PHP_EOL;

// Check guru
$guru = User::where('email', 'andi@guru.id')->first();
if ($guru) {
    echo "Guru: {$guru->name} ({$guru->email})" . PHP_EOL;
    echo "  password hash exists: " . (!empty($guru->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "  'password' matches: " . (Hash::check('password', $guru->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "  '12345678' matches: " . (Hash::check('12345678', $guru->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "  'guru123' matches: " . (Hash::check('guru123', $guru->password) ? 'YES' : 'NO') . PHP_EOL;
}

// Check a siswa
$siswa = User::where('email', 'burhan@siswa.id')->first();
if ($siswa) {
    echo PHP_EOL . "Siswa: {$siswa->name} ({$siswa->email})" . PHP_EOL;
    echo "  password hash exists: " . (!empty($siswa->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "  'password' matches: " . (Hash::check('password', $siswa->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "  '12345678' matches: " . (Hash::check('12345678', $siswa->password) ? 'YES' : 'NO') . PHP_EOL;
}

// Check admin
$admin = User::where('role', 'admin')->first();
if ($admin) {
    echo PHP_EOL . "Admin: {$admin->name} ({$admin->email})" . PHP_EOL;
    echo "  password hash exists: " . (!empty($admin->password) ? 'YES' : 'NO') . PHP_EOL;
    echo "  'password' matches: " . (Hash::check('password', $admin->password) ? 'YES' : 'NO') . PHP_EOL;
}

