<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel Admin' }} - {{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { margin: 0; background: #F4F5F2; }
        a { color: #2347C4; }
        a:hover { color: #1A3596; }
        :focus-visible { outline: 3px solid #F2A93B; outline-offset: 2px; }
    </style>
</head>
<body>
    <div class="flex flex-col md:flex-row min-h-screen bg-[#F4F5F2] text-[#15191F] text-[15px] leading-relaxed" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
        
        <!-- Sidebar -->
        <aside class="w-full md:w-[260px] md:flex-shrink-0 bg-white border-b md:border-b-0 md:border-r border-[#E2E4DF] p-5 flex flex-col gap-6 md:sticky md:top-0 md:h-screen md:overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" style="display: flex; align-items: center; gap: 10px; padding: 0 6px; text-decoration: none; color: #15191F" wire:navigate>
                <x-brand-logo />
            </a>
            
            <nav aria-label="Menu admin" style="flex: 1; display: flex; flex-direction: column; gap: 18px">
                @php
                    $pendingVerificationsCount = \App\Models\User::where('role', 'guru')->where('status', 'pending')->count();
                @endphp
                <div style="display: flex; flex-direction: column; gap: 2px">
                    <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.06em; color: #4A515C; padding: 0 12px 6px">UTAMA</span>
                    <x-admin.sidebar-item :href="route('admin.dashboard')" icon="home" :active="request()->routeIs('admin.dashboard')">
                        Dashboard
                    </x-admin.sidebar-item>
                </div>

                <div style="display: flex; flex-direction: column; gap: 2px">
                    <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.06em; color: #4A515C; padding: 0 12px 6px">AKUN</span>
                    <x-admin.sidebar-item :href="route('admin.verifikasi')" icon="check-badge" :active="request()->routeIs('admin.verifikasi')">
                        Verifikasi guru
                        @if($pendingVerificationsCount > 0)
                            <span style="padding: 1px 8px; border-radius: 99px; background: #F2A93B; color: #15191F; font-size: 12px; font-weight: 700">{{ $pendingVerificationsCount }}</span>
                        @endif
                    </x-admin.sidebar-item>
                    <x-admin.sidebar-item :href="route('admin.users')" icon="users" :active="request()->routeIs('admin.users')">
                        Pengguna
                    </x-admin.sidebar-item>
                </div>

                <div style="display: flex; flex-direction: column; gap: 2px">
                    <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.06em; color: #4A515C; padding: 0 12px 6px">AKADEMIK</span>
                    <x-admin.sidebar-item :href="route('admin.academic-years')" icon="calendar" :active="request()->routeIs('admin.academic-years')">
                        Tahun ajaran
                    </x-admin.sidebar-item>
                    <x-admin.sidebar-item :href="route('admin.classrooms')" icon="academic-cap" :active="request()->routeIs('admin.classrooms')">
                        Kelas
                    </x-admin.sidebar-item>
                    <x-admin.sidebar-item :href="route('admin.teaching-assignments')" icon="clipboard-document-check" :active="request()->routeIs('admin.teaching-assignments')">
                        Plot mengajar
                    </x-admin.sidebar-item>
                    <x-admin.sidebar-item :href="route('admin.curriculum')" icon="book-open" :active="request()->routeIs('admin.curriculum')">
                        Kurikulum
                    </x-admin.sidebar-item>
                    <x-admin.sidebar-item :href="route('admin.laporan-kelulusan')" icon="chart-bar" :active="request()->routeIs('admin.laporan-kelulusan')">
                        Laporan Kelulusan
                    </x-admin.sidebar-item>
                </div>

                <div style="display: flex; flex-direction: column; gap: 2px">
                    <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; letter-spacing: 0.06em; color: #4A515C; padding: 0 12px 6px">SEKOLAH</span>
                    <x-admin.sidebar-item :href="route('admin.settings')" icon="cog-8-tooth" :active="request()->routeIs('admin.settings')">
                        Pengaturan
                    </x-admin.sidebar-item>
                </div>
            </nav>

            <div style="border-top: 1px solid #EDEEEA; padding-top: 14px; display: flex; flex-direction: column; gap: 6px">
                <div style="display: flex; align-items: center; gap: 10px; padding: 0 6px">
                    <span style="width: 36px; height: 36px; flex: none; border-radius: 99px; background: #15191F; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800">{{ auth()->user()->initials() }}</span>
                    <span style="min-width: 0; display: flex; flex-direction: column; line-height: 1.3">
                        <span style="font-size: 14px; font-weight: 700">{{ auth()->user()->name }}</span>
                        <span style="font-size: 12px; color: #4A515C; overflow: hidden; text-overflow: ellipsis; white-space: nowrap">{{ auth()->user()->email }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" style="width: 100%; display: flex; align-items: center; gap: 12px; min-height: 44px; padding: 0 12px; border-radius: 10px; text-decoration: none; color: #8A3412; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: transparent;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3"></path><path d="M14 16l4-4-4-4M18 12H9"></path></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 p-6 md:p-8 pb-16 flex flex-col gap-6">
            <!-- Top Search Bar -->
            <div class="flex items-center justify-between gap-4 pb-4 border-b border-[#E2E4DF]">
                <div class="flex-1 max-w-md">
                    <livewire:global-search role="admin" key="search-admin" />
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.settings') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold text-[#4A515C] hover:text-[#15191F] hover:bg-white border border-transparent hover:border-[#E2E4DF] transition" title="Pengaturan Sekolah">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span class="hidden sm:inline">Pengaturan</span>
                    </a>
                </div>
            </div>

            <x-admin.page-header 
                :title="$title ?? 'Panel Admin'" 
                :breadcrumbs="$breadcrumbs ?? 'PANEL ADMIN'" 
                :description="$description ?? ''" 
            >
                @isset($headerActions)
                    {{ $headerActions }}
                @endisset
            </x-admin.page-header>
            
            {{ $slot }}
        </main>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    
</body>
</html>
