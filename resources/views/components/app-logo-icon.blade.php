@props([
    'size' => null, // 'xs', 'sm', 'md', 'mobile', 'lg', 'xl', 'full'
])

@php
    $logoUrl = \App\Models\Setting::getValue('app_logo') ?? \App\Models\Setting::getValue('logo');

    // Preset configurations for sizes
    $presets = [
        'xs' => [
            'class' => 'size-6 rounded-md bg-white dark:bg-stone-800 border border-slate-200/70 dark:border-stone-700/60 shadow-2xs',
            'style' => 'width: 24px; height: 24px; max-width: 24px; max-height: 24px;',
            'font'  => 'font-size: 11px;',
        ],
        'sm' => [
            'class' => 'size-8 rounded-lg bg-white dark:bg-stone-800 border border-slate-200/70 dark:border-stone-700/60 shadow-2xs',
            'style' => 'width: 32px; height: 32px; max-width: 32px; max-height: 32px;',
            'font'  => 'font-size: 13px;',
        ],
        'md' => [
            'class' => 'size-10 rounded-xl bg-white dark:bg-stone-800 border border-slate-200/70 dark:border-stone-700/60 shadow-xs',
            'style' => 'width: 40px; height: 40px; max-width: 40px; max-height: 40px;',
            'font'  => 'font-size: 15px;',
        ],
        'mobile' => [
            'class' => 'w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-stone-800 border border-slate-200/80 dark:border-stone-700 shadow-md',
            'style' => 'width: 72px; height: 72px; max-width: 72px; max-height: 72px;',
            'font'  => 'font-size: 22px;',
        ],
        'lg' => [
            'class' => 'w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white dark:bg-stone-800 border border-slate-200/80 dark:border-stone-700/80 shadow-md shadow-slate-200/50 dark:shadow-stone-950/50',
            'style' => 'width: 120px; height: 120px; max-width: 120px; max-height: 120px;',
            'font'  => 'font-size: 32px;',
        ],
        'xl' => [
            'class' => 'w-32 h-32 sm:w-36 sm:h-36 rounded-3xl bg-white dark:bg-stone-800 border border-slate-200/80 dark:border-stone-700/80 shadow-lg shadow-slate-200/50 dark:shadow-stone-950/50',
            'style' => 'width: 136px; height: 136px; max-width: 136px; max-height: 136px;',
            'font'  => 'font-size: 36px;',
        ],
        'full' => [
            'class' => 'w-full h-full rounded-2xl bg-white dark:bg-stone-800',
            'style' => 'width: 100%; height: 100%;',
            'font'  => 'font-size: 24px;',
        ],
    ];

    $preset = $size && isset($presets[$size]) ? $presets[$size] : null;
    $presetClass = $preset ? $preset['class'] : '';
    $presetStyle = $preset ? $preset['style'] : '';
    $fontStyle   = $preset ? $preset['font']  : 'font-size: 14px;';
@endphp

@if($logoUrl)
    <div {{ $attributes->merge([
        'class' => "flex items-center justify-center overflow-hidden shrink-0 {$presetClass}",
        'style' => "display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-sizing: border-box; isolation: isolate; {$presetStyle}"
    ]) }}>
        <img src="{{ asset('storage/' . $logoUrl) }}?v={{ time() }}"
             alt="Logo"
             class="w-full h-full object-cover rounded-[inherit]"
             style="max-width: 100%; max-height: 100%; width: 100%; height: 100%; object-fit: cover; box-sizing: border-box; display: block; margin: 0; padding: 0; border: none; border-radius: inherit;">
    </div>
@else
    <span {{ $attributes->merge([
        'class' => "flex items-center justify-center font-mono font-semibold select-none shrink-0 {$presetClass}",
        'style' => "background: #2347C4; color: #FFFFFF; font-family: 'JetBrains Mono', ui-monospace, monospace; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-sizing: border-box; {$fontStyle} {$presetStyle}"
    ]) }}>{ }</span>
@endif
