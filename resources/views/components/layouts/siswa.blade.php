<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ \App\Models\Setting::getValue('app_name', 'Ruang Informatika') }}</title>
    <x-favicon />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; background: #F4F5F2; }
        :where(a:not([class*="text-"])) { color: #2347C4; }
        :where(a:not([class*="text-"]):hover) { color: #1A3596; }
        a.text-white, a.text-white * { color: #FFFFFF !important; }
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
                <a href="{{ route('siswa.beranda') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: #15191F">
                    <x-brand-logo />
                </a>
                <nav aria-label="Navigasi utama" style="display: flex; flex-wrap: wrap; gap: 4px">
                    <a href="{{ route('siswa.beranda') }}" aria-current="{{ request()->routeIs('siswa.beranda') ? 'page' : 'false' }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('siswa.beranda') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Beranda</a>
                    <a href="{{ route('siswa.belajar') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('siswa.belajar*') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Materi</a>
                    <a href="{{ route('siswa.tugas') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('siswa.tugas*') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Tugas</a>
                    <a href="{{ route('siswa.absensi') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('siswa.absensi') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Kehadiran</a>
                    <a href="{{ route('siswa.rapor') }}" style="padding: 11px 14px; border-radius: 10px; {{ request()->routeIs('siswa.rapor') ? 'background: #E8EDFC; color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 600;' }} font-size: 14px; text-decoration: none">Nilai</a>
                </nav>
                <div style="flex: 1 1 auto; display: flex; justify-content: flex-end; align-items: center; gap: 10px">
                    <livewire:notification-dropdown key="notif-siswa-desktop" />
                    <x-user-menu />
                </div>
            </div>
        </header>

        <header class="mobile-nav" style="background: #FFFFFF; border-bottom: 1px solid #E2E4DF; padding: 12px 16px; display: none; flex-direction: column; gap: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <a href="{{ route('siswa.beranda') }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #15191F">
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: #2347C4; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-family: 'JetBrains Mono', ui-monospace, monospace; font-weight: 600; font-size: 12px">{ }</span>
                    <span style="font-weight: 800; font-size: 15px">Informatika</span>
                </a>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <livewire:notification-dropdown key="notif-siswa-mobile" />
                    <x-user-menu />
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <nav class="mobile-nav" aria-label="Navigasi bawah" style="position: fixed; bottom: 0; left: 0; right: 0; background: #FFFFFF; border-top: 1px solid #E2E4DF; padding: 8px 16px 24px; display: none; justify-content: space-between; z-index: 10">
            <a href="{{ route('siswa.beranda') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('siswa.beranda') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span style="font-size: 11px">Beranda</span>
            </a>
            <a href="{{ route('siswa.belajar') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('siswa.belajar*') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                <span style="font-size: 11px">Materi</span>
            </a>
            <a href="{{ route('siswa.tugas') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('siswa.tugas*') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span style="font-size: 11px">Tugas</span>
            </a>
            <a href="{{ route('siswa.absensi') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('siswa.absensi') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span style="font-size: 11px">Kehadiran</span>
            </a>
            <a href="{{ route('siswa.rapor') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('siswa.rapor') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                <span style="font-size: 11px">Nilai</span>
            </a>
            <a href="{{ route('profil') }}" style="display: flex; flex-direction: column; align-items: center; gap: 4px; text-decoration: none; {{ request()->routeIs('profil') ? 'color: #2347C4; font-weight: 700;' : 'color: #4A515C; font-weight: 500;' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span style="font-size: 11px">Profil</span>
            </a>
        </nav>
    </div>
    @livewireScripts
</body>
</html>
