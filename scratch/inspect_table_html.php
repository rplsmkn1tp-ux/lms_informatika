<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$teacher = \App\Models\User::where('role', 'guru')->first();
$assessment = \App\Models\Assessment::find(20);

$component = \Livewire\Livewire::actingAs($teacher)
    ->test(\App\Livewire\Guru\Tugas\Detail::class, ['assessment' => $assessment]);

$html = $component->html();

// Cari bagian tabel aksi
preg_match_all('/<tr class="hover:bg-zinc-50\/60 transition-colors">.*?<\/tr>/s', $html, $matches);
foreach ($matches[0] as $i => $row) {
    echo "--- ROW $i ---\n";
    echo $row . "\n";
}
