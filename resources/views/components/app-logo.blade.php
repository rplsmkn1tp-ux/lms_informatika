@props([
    'sidebar' => false,
    'size' => 'sm',
])

@php
    $appName = \App\Models\Setting::getValue('app_name', config('app.name', 'Laravel'));
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg overflow-hidden shrink-0">
            <x-app-logo-icon :size="$size" class="w-full h-full" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg overflow-hidden shrink-0">
            <x-app-logo-icon :size="$size" class="w-full h-full" />
        </x-slot>
    </flux:brand>
@endif
