<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Announcements columns:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('announcements'));

echo "Submissions columns:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('submissions'));

echo "Assessments columns:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('assessments'));

