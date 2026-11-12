@props(['variant' => 'secondary', 'type' => 'button', 'iconOnly' => false])

@php
    $baseStyle = 'display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 12px; border-radius: 10px; border: 1px solid; font: inherit; font-size: 13px; cursor: pointer; text-decoration: none; white-space: nowrap;';
    
    if ($iconOnly) {
        $baseStyle .= ' width: 44px; justify-content: center; padding: 0;';
    }

    $variantStyle = match($variant) {
        'primary' => 'border-color: #2347C4; background: #2347C4; color: #FFFFFF; font-weight: 700;',
        'secondary' => 'border-color: #D5D8D1; background: #FFFFFF; color: #15191F; font-weight: 600;',
        'danger' => 'border-color: #E3B9A6; background: #FFFFFF; color: #8A3412; font-weight: 600;',
        default => 'border-color: #D5D8D1; background: #FFFFFF; color: #15191F; font-weight: 600;',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['style' => $baseStyle . ' ' . $variantStyle]) }}>
    {{ $slot }}
</button>
