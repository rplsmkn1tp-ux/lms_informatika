<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }} - Guru</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; background: #F4F5F2; }
        :where(a:not([class*="text-"])) { color: #2347C4; }
        :where(a:not([class*="text-"]):hover) { color: #1A3596; }
        :focus-visible { outline: 3px solid #F2A93B; outline-offset: 2px; }
        @media (max-width: 768px) {
            .desktop-nav { display: none !important; }
            .mobile-nav { display: flex !important; }
            .content-wrapper { padding-bottom: 90px !important; }
        }
        @media (min-width: 769px) {
            .mobile-nav { display: none !important; }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div style="min-height: 100vh; background: #F4F5F2; color: #15191F; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; font-size: 15px; line-height: 1.5" class="content-wrapper">
        <header class="desktop-nav" data-testid="app-header" style="background: #FFFFFF; border-bottom: 1px solid #E2E4DF">
            <div style="max-width: 1280px; margin: 0 auto; padding: 14px 24px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px 28px">
                <a href="{{ route('guru.dashboard') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: #15191F">
                    <x-brand-logo />
                </a>
                <nav aria-label="Navigasi utama" style="display: flex; flex-wrap: wrap; gap: 4px">
                    <a href="{{ route('guru.dashboard') }}" aria-current="{{ request()->routeIs('guru.dashboard') ? 'page' : 'false' }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.dashboard') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Beranda</a>
                    <a href="{{ route('guru.tp') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.tp*') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Tujuan Pembelajaran</a>
                    <a href="{{ route('guru.lessons') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.lessons*') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Materi</a>
                    <a href="{{ route('guru.tugas.index') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.tugas*') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Tugas</a>
                    <div x-data="{ open: false }" class="relative inline-block text-left" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open" class="flex items-center gap-1" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.absensi.*') || request()->routeIs('guru.agenda.*') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; border: none; font-family: inherit; cursor: pointer; transition: background 0.2s;">
                            Absensi
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{'rotate-180': open}" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100" 
                             x-transition:enter-start="transform opacity-0 scale-95" 
                             x-transition:enter-end="transform opacity-100 scale-100" 
                             x-transition:leave="transition ease-in duration-75" 
                             x-transition:leave-start="transform opacity-100 scale-100" 
                             x-transition:leave-end="transform opacity-0 scale-95" 
                             class="absolute left-0 z-50 mt-1 w-48 origin-top-left rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                             style="display: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #E2E4DF; padding: 4px;">
                             
                            <a href="{{ route('guru.absensi.index') }}" style="display: block; padding: 8px 12px; border-radius: 8px; color: #15191F; font-size: 14px; text-decoration: none; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#F4F5F2'" onmouseout="this.style.backgroundColor='transparent'">Daftar Kehadiran</a>
                            <a href="{{ route('guru.agenda.index') }}" style="display: block; padding: 8px 12px; border-radius: 8px; color: #15191F; font-size: 14px; text-decoration: none; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#F4F5F2'" onmouseout="this.style.backgroundColor='transparent'">Agenda Guru</a>
                        </div>
                    </div>
                    <div x-data="{ open: false }" class="relative inline-block text-left" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open" class="flex items-center gap-1" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.buku-nilai.*') || request()->routeIs('guru.peta-ketercapaian') || request()->routeIs('guru.laporan-rapor') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; border: none; font-family: inherit; cursor: pointer; transition: background 0.2s;">
                            Penilaian
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{'rotate-180': open}" style="transition: transform 0.2s;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100" 
                             x-transition:enter-start="transform opacity-0 scale-95" 
                             x-transition:enter-end="transform opacity-100 scale-100" 
                             x-transition:leave="transition ease-in duration-75" 
                             x-transition:leave-start="transform opacity-100 scale-100" 
                             x-transition:leave-end="transform opacity-0 scale-95" 
                             class="absolute left-0 z-50 mt-1 w-48 origin-top-left rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                             style="display: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #E2E4DF; padding: 4px;">
                             
                            <a href="{{ route('guru.buku-nilai.index') }}" style="display: block; padding: 8px 12px; border-radius: 8px; color: #15191F; font-size: 14px; text-decoration: none; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#F4F5F2'" onmouseout="this.style.backgroundColor='transparent'">Buku Nilai</a>
                            <a href="{{ route('guru.peta-ketercapaian') }}" style="display: block; padding: 8px 12px; border-radius: 8px; color: #15191F; font-size: 14px; text-decoration: none; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#F4F5F2'" onmouseout="this.style.backgroundColor='transparent'">Peta Ketercapaian</a>
                            <a href="{{ route('guru.laporan-rapor') }}" style="display: block; padding: 8px 12px; border-radius: 8px; color: #15191F; font-size: 14px; text-decoration: none; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#F4F5F2'" onmouseout="this.style.backgroundColor='transparent'">Rapor</a>
                        </div>
                    </div>
                    <a href="{{ route('guru.jadwal') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('guru.jadwal') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Jadwal</a>
                </nav>
                <div style="flex: 1 1 260px; display: flex; justify-content: flex-end; align-items: center; gap: 10px">
                    <div style="flex: 0 1 360px; min-width: 0;">
                        <livewire:global-search role="guru" key="search-guru-desktop" />
                    </div>
                    <button type="button" aria-label="Notifikasi, 2 baru" style="position: relative; width: 44px; height: 44px; flex: none; border-radius: 12px; border: 1px solid #E2E4DF; background: #FFFFFF; color: #15191F; display: flex; align-items: center; justify-content: center; cursor: pointer">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z"></path><path d="M10 20a2 2 0 0 0 4 0"></path></svg>
                        <span style="position: absolute; top: 9px; right: 10px; width: 8px; height: 8px; border-radius: 99px; background: #E0892A; border: 2px solid #FFFFFF"></span>
                    </button>
                    <x-user-menu />
                </div>
            </div>
        </header>

        <header class="mobile-nav" style="background: #FFFFFF; border-bottom: 1px solid #E2E4DF; padding: 12px 16px; display: none; flex-direction: column; gap: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <a href="{{ route('guru.dashboard') }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #15191F">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #2347C4; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-family: 'JetBrains Mono', ui-monospace, monospace; font-weight: 600; font-size: 12px">{ }</span>
                    <span style="font-weight: 800; font-size: 15px">Informatika Guru</span>
                </a>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <x-user-menu />
                </div>
            </div>
            <div style="width: 100%;">
                <livewire:global-search role="guru" key="search-guru-mobile" />
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>

        <nav class="mobile-nav" aria-label="Navigasi bawah" style="position: fixed; bottom: 0; left: 0; right: 0; background: #FFFFFF; border-top: 1px solid #E2E4DF; padding: 8px 16px 24px; display: none; justify-content: space-around; z-index: 10">
            <a href="{{ route('guru.dashboard') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('guru.dashboard') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span style="font-size: 11px">Beranda</span>
            </a>
            <a href="{{ route('guru.lessons') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('guru.lessons*') || request()->routeIs('guru.tp*') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                <span style="font-size: 11px">Materi</span>
            </a>
            <a href="{{ route('guru.tugas.index') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('guru.tugas.*') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                <span style="font-size: 11px">Tugas</span>
            </a>
            <a href="{{ route('guru.buku-nilai.index') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('guru.buku-nilai.*') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                <span style="font-size: 11px">Nilai</span>
            </a>
            <div x-data="{ open: false }" class="relative flex flex-col items-center">
                <button @click="open = !open" @click.away="open = false" style="display: flex; flex-direction: column; align-items: center; gap: 4px; border: none; background: transparent; cursor: pointer; {{ request()->routeIs('guru.absensi.*') || request()->routeIs('guru.agenda.*') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }} padding: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span style="font-size: 11px; font-family: inherit;">Absensi</span>
                </button>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100" 
                     x-transition:enter-start="transform opacity-0 translate-y-2" 
                     x-transition:enter-end="transform opacity-100 translate-y-0" 
                     x-transition:leave="transition ease-in duration-75" 
                     x-transition:leave-start="transform opacity-100 translate-y-0" 
                     x-transition:leave-end="transform opacity-0 translate-y-2" 
                     class="absolute z-50 mb-2 w-36 origin-bottom rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                     style="bottom: 100%; display: none; box-shadow: 0 -10px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #E2E4DF; padding: 4px;">
                     
                    <a href="{{ route('guru.absensi.index') }}" style="display: block; padding: 10px 12px; border-radius: 8px; color: #15191F; font-size: 13px; text-decoration: none; font-weight: 500; text-align: center; border-bottom: 1px solid #f3f4f6;">Kehadiran</a>
                    <a href="{{ route('guru.agenda.index') }}" style="display: block; padding: 10px 12px; border-radius: 8px; color: #15191F; font-size: 13px; text-decoration: none; font-weight: 500; text-align: center;">Agenda Guru</a>
                </div>
            </div>
            <a href="{{ route('profil') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('profil') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span style="font-size: 11px">Profil</span>
            </a>
            <a href="{{ route('guru.jadwal') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('guru.jadwal') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span style="font-size: 11px">Jadwal</span>
            </a>
        </nav>
    </div>
    @livewireScripts
</body>
</html>
