<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\Illuminate\Support\Facades\Auth::loginUsingId(2);
try {
    $html = \Livewire\Livewire::mount('guru.tp-management');
    echo 'Render length: ' . strlen($html);
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
}
