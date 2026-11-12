@props(['icon' => null, 'active' => false, 'href' => '#'])

@php
$classes = $active
    ? 'display: flex; align-items: center; gap: 12px; min-height: 44px; padding: 0 12px; border-radius: 10px; text-decoration: none; font-size: 14px; background: #E8EDFC; color: #2347C4; font-weight: 700'
    : 'display: flex; align-items: center; gap: 12px; min-height: 44px; padding: 0 12px; border-radius: 10px; text-decoration: none; font-size: 14px; color: #3B414A; font-weight: 600';
@endphp

<a href="{{ $href }}" style="{{ $classes }}" {{ $attributes }} wire:navigate>
    @if ($icon)
        <flux:icon :icon="$icon" style="width: 20px; height: 20px; flex: none" />
    @endif
    <span style="flex: 1">{{ $slot }}</span>
</a>
