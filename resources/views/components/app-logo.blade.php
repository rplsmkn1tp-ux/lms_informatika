@props([
    'sidebar' => false,
    'size' => 'xs',
])

@php
    $appName = \App\Models\Setting::getValue('app_name', config('app.name', 'Laravel'));
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon :size="$size" class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$appName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon :size="$size" class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif

