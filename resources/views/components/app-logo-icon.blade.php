@props([
    'size' => null, // 'xs', 'sm', 'md', 'mobile', 'lg', 'xl'
])

@php
    $logoUrl = \App\Models\Setting::getValue('app_logo') ?? \App\Models\Setting::getValue('logo');

    // Preset configurations for sizes
    $presets = [
        'xs' => [
            'class' => 'size-5 rounded-md',
            'style' => 'width: 20px; height: 20px; max-width: 20px; max-height: 20px;',
            'font'  => 'font-size: 10px;',
            'pad'   => 'padding: 2px;',
        ],
        'sm' => [
            'class' => 'size-8 rounded-lg',
            'style' => 'width: 32px; height: 32px; max-width: 32px; max-height: 32px;',
            'font'  => 'font-size: 12px;',
            'pad'   => 'padding: 3px;',
        ],
        'md' => [
            'class' => 'size-10 rounded-xl',
            'style' => 'width: 40px; height: 40px; max-width: 40px; max-height: 40px;',
            'font'  => 'font-size: 14px;',
            'pad'   => 'padding: 4px;',
        ],
        'mobile' => [
            'class' => 'w-full h-full rounded-xl bg-gradient-to-br from-slate-50 via-sky-50/50 to-indigo-50/40 dark:from-stone-800 dark:to-stone-900 border border-slate-100 dark:border-stone-700/60',
            'style' => 'width: 100%; height: 100%; max-width: 60px; max-height: 60px;',
            'font'  => 'font-size: 18px;',
            'pad'   => 'padding: 4px;',
        ],
        'lg' => [
            'class' => 'w-24 h-24 rounded-2xl bg-gradient-to-br from-slate-50 via-sky-50/50 to-indigo-50/40 dark:from-stone-800 dark:to-stone-900 border border-slate-100 dark:border-stone-700/60',
            'style' => 'width: 96px; height: 96px; max-width: 96px; max-height: 96px;',
            'font'  => 'font-size: 28px;',
            'pad'   => 'padding: 6px;',
        ],
        'xl' => [
            'class' => 'w-32 h-32 rounded-3xl bg-gradient-to-br from-slate-50 via-sky-50/50 to-indigo-50/40 dark:from-stone-800 dark:to-stone-900 border border-slate-100 dark:border-stone-700/60',
            'style' => 'width: 128px; height: 128px; max-width: 128px; max-height: 128px;',
            'font'  => 'font-size: 36px;',
            'pad'   => 'padding: 8px;',
        ],
    ];

    $preset = $size && isset($presets[$size]) ? $presets[$size] : null;
    $presetClass = $preset ? $preset['class'] : '';
    $presetStyle = $preset ? $preset['style'] : '';
    $fontStyle   = $preset ? $preset['font']  : 'font-size: 14px;';
    $imgPad      = $preset ? $preset['pad']   : 'padding: 2px;';
@endphp

@if($logoUrl)
    <div {{ $attributes->merge([
        'class' => "flex items-center justify-center overflow-hidden shrink-0 {$presetClass}",
        'style' => "display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-sizing: border-box; {$presetStyle}"
    ]) }}>
        <img src="{{ asset('storage/' . $logoUrl) }}?v={{ time() }}"
             alt="Logo"
             class="w-full h-full object-contain"
             style="max-width: 100%; max-height: 100%; width: 100%; height: 100%; object-fit: contain; box-sizing: border-box; display: block; {{ $imgPad }}">
    </div>
@else
    <span {{ $attributes->merge([
        'class' => "flex items-center justify-center font-mono font-semibold select-none shrink-0 {$presetClass}",
        'style' => "background: #2347C4; color: #FFFFFF; font-family: 'JetBrains Mono', ui-monospace, monospace; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-sizing: border-box; {$fontStyle} {$presetStyle}"
    ]) }}>{ }</span>
@endif
