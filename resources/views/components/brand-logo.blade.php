@props([
    'withText' => true,
    'size' => 'md',
])
@php
    $appName = \App\Models\Setting::getValue('app_name', 'Ruang Informatika');
    $appSubtitle = \App\Models\Setting::getValue('app_subtitle', 'SMK Negeri 1 Tanjungpandan');
@endphp

<div {{ $attributes->merge(['style' => 'display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;']) }}>
    <x-app-logo-icon :size="$size" class="bg-white border border-slate-200/60 shadow-xs" />

    @if($withText)
    <span style="display: flex; flex-direction: column; line-height: 1.25">
        <span style="font-weight: 800; font-size: 16px">{{ $appName }}</span>
        @if($appSubtitle)
            <span style="font-size: 12px; color: #4A515C">{{ $appSubtitle }}</span>
        @endif
    </span>
    @endif
</div>

