@props([
    'title',
    'subtitle' => null,
    'description' => null,
    'breadcrumbs' => null,
    'eyebrow' => null,
])

@php
    $desc = $subtitle ?? $description;
    $eye = $eyebrow ?? $breadcrumbs;
@endphp

<header data-testid="app-header" {{ $attributes->merge(['class' => 'mb-6']) }} style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px">
    <div style="display: flex; flex-direction: column; gap: 6px; min-width: 0; flex: 1 1 420px">
        @if($eye)
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.06em; color: #4A515C; text-transform: uppercase; font-weight: 700;">{{ $eye }}</span>
        @endif
        <h1 style="margin: 0; font-size: 28px; sm:font-size: 32px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2; color: #15191F">{{ $title }}</h1>
        @if($desc)
            <p style="margin: 0; font-size: 15px; color: #4A515C; max-width: 680px; line-height: 1.5">{{ $desc }}</p>
        @endif
    </div>
    
    @if(trim($slot))
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center">
            {{ $slot }}
        </div>
    @endif
</header>
