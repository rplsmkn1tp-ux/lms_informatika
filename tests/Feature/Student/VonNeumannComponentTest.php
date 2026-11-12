<?php

use Illuminate\Support\Facades\Blade;

it('merender 5 komponen dan 6 langkah simulasi Von Neumann', function () {
    $html = Blade::render('<x-sim.von-neumann />');

    foreach (['in', 'cu', 'alu', 'ram', 'out'] as $unit) {
        expect($html)->toContain('data-unit="'.$unit.'"');
    }

    expect($html)
        ->toContain('12 + 30')
        ->toContain('42')
        ->toContain('Input dari keyboard')
        ->toContain('Output ke layar')
        ->toContain('data-action="prev"')
        ->toContain('data-action="next"')
        ->toContain('data-action="reset"')
        ->toContain('aria-live="polite"');

    // 6 langkah dikirim ke Alpine
    expect(substr_count($html, 'active'))->toBeGreaterThanOrEqual(6);
});

it('bisa dipakai ulang dengan angka lain', function () {
    $html = Blade::render('<x-sim.von-neumann :a="7" :b="5" />');

    expect($html)->toContain('7 + 5')->toContain('12')->not->toContain('12 + 30');
});
