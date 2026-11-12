<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-100/80 dark:bg-stone-950 text-slate-800 dark:text-stone-100 antialiased relative flex items-center justify-center p-3 sm:p-6 lg:p-8">
        
        <!-- Ambient Background Glows -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-400/20 dark:bg-blue-600/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -right-40 w-96 h-96 bg-emerald-400/20 dark:bg-emerald-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-indigo-400/20 dark:bg-indigo-600/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Main Floating Split Card -->
        <div class="relative z-10 w-full max-w-5xl bg-white dark:bg-stone-900 rounded-3xl shadow-2xl shadow-slate-200/60 dark:shadow-stone-950/50 border border-slate-200/80 dark:border-stone-800 overflow-hidden flex flex-col lg:flex-row min-h-[580px]">
            
            <!-- Left Panel (Branding & Logo di Samping) -->
            <div class="hidden lg:flex flex-col justify-between w-5/12 p-8 lg:p-10 relative overflow-hidden bg-gradient-to-br from-slate-50 via-sky-50/60 to-emerald-50/40 dark:from-stone-900 dark:via-stone-900 dark:to-stone-950 border-r border-slate-200/70 dark:border-stone-800">
                
                <!-- Branding Header -->
                <a href="{{ route('home') }}" class="flex flex-col items-center text-center gap-4 transition-transform hover:scale-[1.02] duration-300 group" wire:navigate>
                    <!-- Single logo card with rounded corners and soft shadow, logo fills frame with ~4% margin -->
                    <x-app-logo-icon size="lg" class="transition-all duration-300 group-hover:shadow-xl group-hover:scale-[1.02]" />
                    
                    <div class="space-y-1.5 flex flex-col items-center">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}
                        </h1>
                        @php
                            $subtitle = \App\Models\Setting::getValue('app_subtitle') ?? \App\Models\Setting::getValue('app_subname', 'SMK Negeri 1 Tanjungpandan');
                        @endphp
                        @if($subtitle)
                            <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>
                </a>

                <!-- Feature Highlights -->
                <div class="space-y-2.5 my-4">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/70 dark:bg-stone-800/60 backdrop-blur-sm border border-slate-200/60 dark:border-stone-700/50 text-xs text-slate-700 dark:text-slate-300 shadow-xs">
                        <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shrink-0">
                            ✓
                        </div>
                        <span class="font-medium">Materi &amp; Kuis Sesuai Capaian Pembelajaran</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white/70 dark:bg-stone-800/60 backdrop-blur-sm border border-slate-200/60 dark:border-stone-700/50 text-xs text-slate-700 dark:text-slate-300 shadow-xs">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                            ✓
                        </div>
                        <span class="font-medium">Presensi &amp; Rekap Nilai Terintegrasi</span>
                    </div>
                </div>

                <!-- Footer note -->
                <div class="text-center text-xs text-slate-400 dark:text-stone-500">
                    &copy; {{ date('Y') }} {{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}. All rights reserved.
                </div>
            </div>

            <!-- Right Panel (Form Area) -->
            <div class="w-full lg:w-7/12 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white dark:bg-stone-900 relative z-20">
                
                <!-- Mobile Branding Header (Hanya tampil di layar kecil/HP) -->
                <a href="{{ route('home') }}" class="flex lg:hidden flex-col items-center gap-3 font-medium mb-6 text-center" wire:navigate>
                    <!-- Mobile logo card -->
                    <x-app-logo-icon size="mobile" />
                    <div class="space-y-1">
                        <span class="text-lg font-bold text-slate-900 dark:text-white block">
                            {{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block">
                            {{ \App\Models\Setting::getValue('app_subtitle') ?? \App\Models\Setting::getValue('app_subname', 'SMK Negeri 1 Tanjungpandan') }}
                        </span>
                    </div>
                </a>

                <div class="max-w-md w-full mx-auto">
                    {{ $slot }}
                </div>
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
