<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-stone-950 text-stone-800 dark:text-stone-100 antialiased flex min-h-svh flex-col items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md flex flex-col gap-6">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 text-center transition-transform hover:scale-105 duration-200" wire:navigate>
                <x-app-logo-icon size="md" class="!w-14 !h-14 rounded-2xl shadow-sm" />

                <div class="flex flex-col items-center">
                    <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}
                    </span>
                    @php
                        $subtitle = \App\Models\Setting::getValue('app_subtitle') ?? \App\Models\Setting::getValue('app_subname');
                    @endphp
                    @if($subtitle)
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $subtitle }}
                        </span>
                    @endif
                </div>
            </a>

            <div class="rounded-2xl border border-gray-200/80 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-xl shadow-gray-200/50 dark:shadow-none p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>

