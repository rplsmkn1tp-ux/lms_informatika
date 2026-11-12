<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\SiswaGradeService::class);
$burhanGradesWrongClass = $service->getStudentGrades(27, 1, 1);
echo "Burhan in Class 1 (not enrolled): " . $burhanGradesWrongClass->count() . " items" . PHP_EOL;

$burhanGradesOwnClass = $service->getStudentGrades(27, 4, 1);
echo "Burhan in Class 4 (enrolled): " . $burhanGradesOwnClass->count() . " items" . PHP_EOL;
foreach ($burhanGradesOwnClass as $g) {
    echo "  - {$g->nama_kolom}: " . ($g->is_graded ? $g->score : 'Belum dinilai') . PHP_EOL;
}
