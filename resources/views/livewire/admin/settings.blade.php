<div>
    <x-slot:title>Pengaturan Sekolah</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN &middot; PENGATURAN</x-slot>
    <x-slot:description>Pengaturan umum LMS dan konfigurasi default.</x-slot>

    <flux:heading size="xl" class="mb-6">Pengaturan Sekolah</flux:heading>

    <form wire:submit="save" class="space-y-6 max-w-3xl">
        <!-- Identitas Aplikasi -->
        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg">Identitas Aplikasi</flux:heading>
                
                <div class="flex flex-col gap-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-900 dark:text-white">Logo Aplikasi</label>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Gunakan logo persegi, latar transparan, dan minim ruang kosong di tepi.</p>
                    </div>

                    <div class="flex items-center gap-4">
                        @if ($trimmedPreviewData)
                            <!-- Pratinjau Hasil Auto-Trim baru -->
                            <div class="relative w-20 h-20 rounded-2xl bg-white dark:bg-stone-800 border border-blue-300 dark:border-blue-700 shadow-md flex items-center justify-center overflow-hidden p-0 shrink-0 ring-2 ring-blue-500/20" style="isolation: isolate;">
                                <img src="{{ $trimmedPreviewData }}" alt="Pratinjau Hasil Auto-Trim" class="w-full h-full object-cover rounded-[inherit]">
                                <span class="absolute bottom-0 inset-x-0 bg-blue-600/90 text-white text-[9px] font-bold text-center py-0.5 uppercase tracking-wider">Trimmed</span>
                            </div>
                        @elseif ($logo)
                            <!-- Preview upload baru -->
                            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-stone-800 border border-zinc-200 dark:border-stone-700 shadow-sm flex items-center justify-center overflow-hidden p-0 shrink-0" style="isolation: isolate;">
                                <img src="{{ $logo->temporaryUrl() }}" alt="Preview Baru" class="w-full h-full object-cover rounded-[inherit]">
                            </div>
                        @elseif ($currentLogoUrl)
                            <!-- Logo tersimpan -->
                            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-stone-800 border border-zinc-200 dark:border-stone-700 shadow-sm flex items-center justify-center overflow-hidden p-0 shrink-0" style="isolation: isolate;">
                                <img src="{{ asset('storage/' . $currentLogoUrl) }}?v={{ time() }}" alt="Logo Aplikasi" class="w-full h-full object-cover rounded-[inherit]">
                            </div>
                        @else
                            <!-- Logo default -->
                            <div class="w-20 h-20 rounded-2xl bg-[#2347C4] text-white flex items-center justify-center font-mono font-bold text-2xl shadow-sm shrink-0">
                                { }
                            </div>
                        @endif
                        
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <input type="file" wire:model="logo" id="logoUpload" class="hidden" accept="image/png, image/jpeg, image/jpg, image/svg+xml, image/webp">
                                <label for="logoUpload" class="cursor-pointer inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 rounded-xl transition-colors">
                                    <flux:icon.arrow-up-tray class="w-4 h-4" />
                                    Pilih Logo Baru
                                </label>
                                
                                @if($currentLogoUrl)
                                    <button type="button" wire:click="removeLogo" wire:confirm="Anda yakin ingin mengembalikan logo ke default?" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 rounded-xl transition-colors">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">Maks. 2MB (PNG, JPG, SVG, WebP). Area kosong di sekeliling logo akan otomatis di-trim.</span>
                            <div wire:loading wire:target="logo" class="text-xs text-blue-600 font-medium animate-pulse">Memproses & mengoptimasi pratinjau logo...</div>
                        </div>
                    </div>
                    @error('logo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <flux:input wire:model="app_name" label="Nama Sekolah / Aplikasi" placeholder="Contoh: Ruang Informatika" required />
                    <flux:input wire:model="app_subtitle" label="Subjudul (Opsional)" placeholder="Contoh: SMK Negeri 1 Tanjungpandan" />
                </div>
            </div>
        </flux:card>

        <!-- Teks Dashboard (Guru & Siswa) -->
        <flux:card>
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Teks Dashboard (Guru & Siswa)</flux:heading>
                    <p class="text-xs text-zinc-500 mt-1">Sesuaikan teks sapaan dan keterangan subjudul pada bagian atas dashboard Guru dan Siswa.</p>
                </div>

                <!-- Bagian Dashboard Guru -->
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-sm font-semibold text-zinc-900 dark:text-white">Dashboard Guru</span>
                        </div>
                        <span class="text-[11px] text-zinc-400 font-mono">Tag: {nama} atau {nama_lengkap}</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <flux:input 
                            wire:model.live="dashboard_guru_title" 
                            label="Teks Sapaan Guru" 
                            placeholder="Halo, Guru {nama}. Siap mengajar hari ini?" 
                            required 
                        />
                        <flux:input 
                            wire:model.live="dashboard_guru_subtitle" 
                            label="Teks Semester / Keterangan Guru" 
                            placeholder="Semester Ganjil 2026/2027" 
                        />
                    </div>

                    <!-- Live Preview Guru -->
                    <div class="mt-2 p-3.5 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block mb-1">Pratinjau Dashboard Guru:</span>
                        <p class="text-[10px] font-semibold tracking-widest uppercase mb-0.5 text-zinc-500">
                            {{ strtoupper(now()->translatedFormat('l, d F Y')) }}@if(!empty($dashboard_guru_subtitle)) &bull; {{ strtoupper($dashboard_guru_subtitle) }}@endif
                        </p>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">
                            {{ str_replace(['{nama}', '{name}', '{nama_depan}', '{nama_panggilan}', '{nama_lengkap}', '{fullname}'], ['Andi', 'Andi', 'Andi', 'Andi', 'Andi Pratama, S.Kom', 'Andi Pratama, S.Kom'], $dashboard_guru_title ?: 'Halo, Guru {nama}. Siap mengajar hari ini?') }}
                        </h3>
                    </div>
                </div>

                <!-- Bagian Dashboard Siswa -->
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-sm font-semibold text-zinc-900 dark:text-white">Dashboard Siswa</span>
                        </div>
                        <span class="text-[11px] text-zinc-400 font-mono">Tag: {nama} atau {nama_depan}</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <flux:input 
                            wire:model.live="dashboard_siswa_title" 
                            label="Teks Sapaan Siswa" 
                            placeholder="Halo, {nama}. Siap lanjut belajar?" 
                            required 
                        />
                        <flux:input 
                            wire:model.live="dashboard_siswa_subtitle" 
                            label="Teks Semester / Keterangan Siswa (Opsional)" 
                            placeholder="Semester Ganjil 2026/2027" 
                        />
                    </div>

                    <!-- Live Preview Siswa -->
                    <div class="mt-2 p-3.5 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 block mb-1">Pratinjau Dashboard Siswa:</span>
                        <p class="text-[11px] font-mono uppercase text-zinc-500 mb-0.5">
                            {{ now()->translatedFormat('l, d F Y') }}@if(!empty($dashboard_siswa_subtitle)) &bull; {{ $dashboard_siswa_subtitle }}@endif
                        </p>
                        <h3 class="text-lg font-extrabold text-zinc-900 dark:text-white">
                            {{ str_replace(['{nama}', '{name}', '{nama_lengkap}', '{fullname}', '{nama_depan}', '{nama_panggilan}'], ['Budi Pratama', 'Budi Pratama', 'Budi Pratama', 'Budi Pratama', 'Budi', 'Budi'], $dashboard_siswa_title ?: 'Halo, {nama}. Siap lanjut belajar?') }}
                        </h3>
                    </div>
                </div>
            </div>
        </flux:card>

        <!-- Akademik -->
        <flux:card>
            <div class="space-y-4">
                <flux:heading size="lg" class="mb-2">Akademik</flux:heading>
                <flux:input wire:model="default_kktp" type="number" label="Default KKTP" description="Kriteria Ketercapaian Tujuan Pembelajaran bawaan (0-100)" required min="0" max="100" />
            </div>
        </flux:card>

        <!-- Akses & Pendaftaran -->
        <flux:card>
            <div class="space-y-4">
                <flux:heading size="lg" class="mb-2">Akses & Pendaftaran</flux:heading>
                <flux:switch wire:model="registration_open" label="Buka Pendaftaran" description="Izinkan pengguna baru untuk mendaftar mandiri" />
                <flux:switch wire:model="teacher_approval_required" label="Persetujuan Guru" description="Guru yang mendaftar baru memerlukan persetujuan Admin agar aktif" />
            </div>
        </flux:card>

        <div class="flex justify-end pt-2">
            <flux:button type="submit" variant="primary" icon="check">Simpan Pengaturan</flux:button>
        </div>
    </form>
</div>
