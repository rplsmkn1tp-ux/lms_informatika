@props(['status' => null, 'variant' => null])

@php
    $key = $variant ?? $status;
    $styles = match($key) {
        'aktif', 'disetujui', 'success' => 'background: #E7F3EC; color: #1D6B45;',
        'tidak aktif', 'neutral' => 'background: #EFF0EC; color: #4A515C;',
        'menunggu', 'warning' => 'background: #FDF1DC; color: #8A4B00;',
        'ditolak', 'danger' => 'background: #FBEDE5; color: #8A3412;',
        default => 'background: #EFF0EC; color: #4A515C;',
    };
@endphp

<span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; white-space: nowrap; {{ $styles }}" {{ $attributes }}>
    {{ $slot->isEmpty() && $status ? ucfirst($status) : $slot }}
</span>
