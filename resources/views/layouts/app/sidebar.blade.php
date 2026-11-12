<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->check() && auth()->user()->role === 'admin')
                    <flux:sidebar.group :heading="__('Admin')" class="grid mt-4">
                        <flux:sidebar.item icon="cog-8-tooth" :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate>
                            Pengaturan
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar" :href="route('admin.academic-years')" :current="request()->routeIs('admin.academic-years')" wire:navigate>
                            Tahun Ajaran
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="book-open" :href="route('admin.curriculum')" :current="request()->routeIs('admin.curriculum')" wire:navigate>
                            Kurikulum
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                            Pengguna
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="academic-cap" :href="route('admin.classrooms')" :current="request()->routeIs('admin.classrooms')" wire:navigate>
                            Kelas
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="check-badge" :href="route('admin.verifikasi')" :current="request()->routeIs('admin.verifikasi')" wire:navigate>
                            Verifikasi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="file-bar-chart" :href="route('admin.laporan-absensi')" :current="request()->routeIs('admin.laporan-absensi')" wire:navigate>
                            Laporan Absensi
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
                
                @if(auth()->check() && auth()->user()->role === 'guru')
                    <flux:sidebar.group :heading="__('Guru')" class="grid mt-4">
                        <flux:sidebar.item icon="home" :href="route('guru.dashboard')" :current="request()->routeIs('guru.dashboard')" wire:navigate>
                            Dashboard
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="bookmark" :href="route('guru.tp')" :current="request()->routeIs('guru.tp')" wire:navigate>
                            Tujuan Pembelajaran
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="book-open" :href="route('guru.lessons')" :current="request()->routeIs('guru.lessons')" wire:navigate>
                            Materi
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="table-cells" :href="route('guru.buku-nilai.index')" :current="request()->routeIs('guru.buku-nilai.*')" wire:navigate>
                            Buku Nilai
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar-days" :href="route('guru.absensi.index')" :current="request()->routeIs('guru.absensi.*')" wire:navigate>
                            Absensi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('guru.peta-ketercapaian')" :current="request()->routeIs('guru.peta-ketercapaian')" wire:navigate>
                            Peta Ketercapaian
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('guru.laporan-rapor')" :current="request()->routeIs('guru.laporan-rapor')" wire:navigate>
                            Laporan Rapor
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                @if(auth()->check() && auth()->user()->role === 'siswa')
                    <flux:sidebar.group :heading="__('Siswa')" class="grid mt-4">
                        <flux:sidebar.item icon="home" :href="route('siswa.beranda')" :current="request()->routeIs('siswa.beranda')" wire:navigate>
                            Beranda
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="book-open" :href="route('siswa.belajar')" :current="request()->routeIs('siswa.belajar*')" wire:navigate>
                            Materi
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-check" :href="route('siswa.tugas')" :current="request()->routeIs('siswa.tugas*')" wire:navigate>
                            Tugas
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('siswa.nilai')" :current="request()->routeIs('siswa.nilai*')" wire:navigate>
                            Nilai
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
            </flux:sidebar.nav>

            <x-user-menu />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header data-testid="app-header" class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profil')" icon="user" wire:navigate>
                            Profil saya
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
