<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Livewire\Siswa\Absensi\KehadiranSaya;
use Livewire\Livewire;

echo "=== TESTING BURHANUDIN (ID 54 - 10 Akuntansi 1) ===" . PHP_EOL;
$s54 = User::find(54);
auth()->login($s54);
$c54 = new KehadiranSaya();
$view54 = $c54->render(new App\Services\AttendanceService());
$records54 = $view54->getData()['records'];
echo "Student 54 attendance count: " . count($records54) . PHP_EOL;
foreach ($records54 as $r) {
    echo "  - [Meeting {$r->session->meeting_no}] Date: " . $r->session->meeting_date->format('d/m/Y') . " | Topic: '{$r->session->topic}' | Status: {$r->status} | Sesi Status: {$r->session->status}" . PHP_EOL;
}

echo PHP_EOL . "=== TESTING BURHAN (ID 27 - 10 Pemasaran 1) ===" . PHP_EOL;
$s27 = User::find(27);
auth()->login($s27);
$c27 = new KehadiranSaya();
$view27 = $c27->render(new App\Services\AttendanceService());
$records27 = $view27->getData()['records'];
echo "Student 27 attendance count: " . count($records27) . PHP_EOL;
foreach ($records27 as $r) {
    echo "  - [Meeting {$r->session->meeting_no}] Date: " . $r->session->meeting_date->format('d/m/Y') . " | Topic: '{$r->session->topic}' | Status: {$r->status} | Sesi Status: {$r->session->status}" . PHP_EOL;
}

