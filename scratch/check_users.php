<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== DATABASE CHECK ===" . PHP_EOL;
echo "DB Connection: " . config('database.default') . PHP_EOL;
echo "DB Name: " . config('database.connections.' . config('database.default') . '.database') . PHP_EOL;
echo "Total Users: " . User::count() . PHP_EOL;

$users = User::all(['id', 'name', 'email', 'role', 'status']);
if ($users->isEmpty()) {
    echo "NO USERS FOUND! Database needs seeding." . PHP_EOL;
} else {
    foreach ($users as $u) {
        echo "  ID={$u->id} | {$u->name} | {$u->email} | role={$u->role} | status={$u->status}" . PHP_EOL;
    }
}

