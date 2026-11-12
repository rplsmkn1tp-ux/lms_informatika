<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$entries = \App\Models\ScheduleEntry::all();
foreach ($entries as $e) {
    echo "ID: {$e->id}, TA: {$e->teaching_assignment_id}, Day: {$e->day_of_week}, Start: {$e->start_period_no}, End: {$e->end_period_no}\n";
}
