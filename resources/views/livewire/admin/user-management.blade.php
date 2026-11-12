<div>
    <x-slot:title>Pengguna</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · PENGGUNA</x-slot>
    <x-slot:description>Kelola akun guru dan siswa. Akun dibuat sendiri oleh pengguna lewat halaman Daftar, jadi admin tidak perlu menginput data.</x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4L12 14.01l-3-3"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <x-admin.card class="overflow-hidden">
        <div class="p-4 sm:p-5 flex flex-col gap-4">
            <div class="flex gap-2 border-b border-zinc-200" role="group" aria-label="Jenis akun">
                <button type="button" 
                        wire:click="setTab('siswa')" 
                        class="inline-flex items-center gap-2 min-h-[48px] px-3 md:px-4 border-b-3 -mb-[1px] font-bold text-sm md:text-[15px] cursor-pointer transition-colors {{ $activeTab === 'siswa' ? 'border-[#2347C4] text-zinc-900' : 'border-transparent text-zinc-600 hover:text-zinc-900' }}">
                    Siswa 
                    <span class="px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-700 text-xs font-semibold">{{ $siswaCount }}</span>
                </button>
                <button type="button" 
                        wire:click="setTab('guru')" 
                        class="inline-flex items-center gap-2 min-h-[48px] px-3 md:px-4 border-b-3 -mb-[1px] font-bold text-sm md:text-[15px] cursor-pointer transition-colors {{ $activeTab === 'guru' ? 'border-[#2347C4] text-zinc-900' : 'border-transparent text-zinc-600 hover:text-zinc-900' }}">
                    Guru 
                    <span class="px-2 py-0.5 rounded-full bg-zinc-100 text-zinc-700 text-xs font-semibold">{{ $guruCount }}</span>
                </button>
                
                <div class="flex-1 flex justify-end items-center pr-2">
                    @if($activeTab === 'siswa')
                    <x-admin.button type="button" variant="primary" wire:click="$set('showImportModal', true)">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Import Siswa
                    </x-admin.button>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pb-2">
                <label class="flex-1 shrink-0 basis-[260px] flex items-center gap-2 h-11 px-3 border border-zinc-300 rounded-xl bg-white text-zinc-500 focus-within:border-[#F2A93B] focus-within:ring-2 focus-within:ring-[#F2A93B]/20 transition-shadow">
                    <svg class="w-[18px] h-[18px] flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>
                    <span class="sr-only">Cari pengguna</span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email{{ $activeTab === 'siswa' ? ', NISN' : '' }}..." class="flex-1 min-w-0 border-0 outline-none bg-transparent font-inherit text-sm text-zinc-900 placeholder:text-zinc-400">
                </label>
                
                <label class="flex items-center gap-2 text-sm font-semibold">
                    Status
                    <select wire:model.live="statusFilter" class="h-11 px-3 border border-zinc-300 rounded-xl bg-white font-inherit text-sm text-zinc-900 focus:border-[#F2A93B] focus:ring-2 focus:ring-[#F2A93B]/20 outline-none transition-shadow">
                        <option value="">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="pending">Menunggu verifikasi</option>
                    </select>
                </label>

                @if($activeTab === 'siswa')
                <label class="flex items-center gap-2 text-sm font-semibold">
                    Kelas
                    <select wire:model.live="classroomFilter" class="h-11 px-3 border border-zinc-300 rounded-xl bg-white font-inherit text-sm text-zinc-900 focus:border-[#F2A93B] focus:ring-2 focus:ring-[#F2A93B]/20 outline-none transition-shadow">
                        <option value="">Semua kelas</option>
                        @foreach($classroomsList as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->grade }} {{ $cr->major }}</option>
                        @endforeach
                    </select>
                </label>
                @endif

                @if(count($selectedUsers) > 0)
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-zinc-600">{{ count($selectedUsers) }} terpilih</span>
                    <x-admin.button variant="danger" wire:click="deleteSelected" wire:confirm="Anda yakin ingin menghapus {{ count($selectedUsers) }} akun ini secara permanen?">
                        <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Hapus Terpilih
                    </x-admin.button>
                </div>
                @endif
            </div>
        </div>

        <div class="px-0">
            <x-admin.table>
                <x-slot:head>
                        <x-admin.table.th style="width: 48px; text-align: center; padding: 12px;"></x-admin.table.th>
                        <x-admin.table.th>Nama</x-admin.table.th>
                        @if($activeTab === 'siswa')
                            <x-admin.table.th>NISN</x-admin.table.th>
                            <x-admin.table.th>Kelas</x-admin.table.th>
                        @else
                            <x-admin.table.th>NIP/NUPTK</x-admin.table.th>
                            <x-admin.table.th>Mata Pelajaran</x-admin.table.th>
                        @endif
                        <x-admin.table.th>Status</x-admin.table.th>
                        <x-admin.table.th>Aksi</x-admin.table.th>
                </x-slot:head>
                    @forelse($users as $user)
                        <tr>
                            <x-admin.table.td style="text-align: center; vertical-align: middle;">
                                <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}" class="w-4 h-4 rounded border-zinc-300 text-[#2347C4] focus:ring-[#2347C4]">
                            </x-admin.table.td>
                            <x-admin.table.td>
                                <div class="flex items-center gap-3 min-w-[220px]">
                                    <span class="w-9 h-9 flex-none rounded-full bg-[#E8EDFC] text-[#2347C4] flex items-center justify-center text-[13px] font-extrabold uppercase">
                                        {{ substr($user->name, 0, 2) }}
                                    </span>
                                    <div class="flex flex-col min-w-0 leading-tight">
                                        <span class="font-bold truncate">{{ $user->name }}</span>
                                        <span class="text-[13px] text-zinc-600 truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </x-admin.table.td>
                            
                            @if($activeTab === 'siswa')
                                <x-admin.table.td>
                                    <span class="font-mono text-[13px]">{{ $user->nisn ?: '-' }}</span>
                                </x-admin.table.td>
                                <x-admin.table.td>
                                    @php
                                        $activeCr = $user->activeClassroom();
                                    @endphp
                                    {{ $activeCr ? $activeCr->grade . ' ' . $activeCr->major : '-' }}
                                </x-admin.table.td>
                            @else
                                <x-admin.table.td>
                                    <span class="font-mono text-[13px]">{{ $user->nip_nuptk ?: '-' }}</span>
                                </x-admin.table.td>
                                <x-admin.table.td>
                                    {{ $user->subject ?: '-' }}
                                </x-admin.table.td>
                            @endif

                            <x-admin.table.td>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($user->status === 'active')
                                        <x-admin.badge variant="success">Aktif</x-admin.badge>
                                    @elseif($user->status === 'inactive')
                                        <x-admin.badge variant="neutral">Nonaktif</x-admin.badge>
                                    @elseif($user->status === 'pending')
                                        <x-admin.badge variant="warning">Menunggu</x-admin.badge>
                                    @endif

                                    @if($user->needs_password_reset)
                                        <x-admin.badge variant="warning">Sandi sementara</x-admin.badge>
                                    @endif
                                </div>
                            </x-admin.table.td>

                            <x-admin.table.td style="white-space: nowrap;">
                                <div class="flex flex-nowrap gap-1.5">
                                    <x-admin.button variant="secondary" wire:click="editUser({{ $user->id }})">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16z"></path></svg>
                                        Ubah
                                    </x-admin.button>
                                    
                                    <x-admin.button variant="secondary" wire:click="resetPassword({{ $user->id }})">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"></circle><path d="M11 12l8-8M16 7l2 2"></path></svg>
                                        Atur sandi
                                    </x-admin.button>

                                    <x-admin.button variant="secondary" wire:click="resetToDefaultPassword({{ $user->id }})" wire:confirm="Anda yakin ingin mereset sandi pengguna ini ke 'password'?">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                                        Reset sandi
                                    </x-admin.button>

                                    @if($activeTab === 'siswa')
                                    <x-admin.button variant="secondary" wire:click="openChangeClassModal({{ $user->id }})">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h11l-3-3M17 17H6l3 3"></path></svg>
                                        Pindah kelas
                                    </x-admin.button>
                                    @endif

                                    @if(auth()->id() !== $user->id)
                                        @if($user->status === 'active')
                                            <x-admin.button variant="danger" icon-only wire:click="toggleStatus({{ $user->id }})" title="Nonaktifkan akun" aria-label="Nonaktifkan akun">
                                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M6 6l12 12"></path></svg>
                                            </x-admin.button>
                                        @else
                                            <x-admin.button variant="primary" icon-only wire:click="toggleStatus({{ $user->id }})" title="Aktifkan akun" aria-label="Aktifkan akun">
                                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v8"></path><path d="M6.5 7a8 8 0 1 0 11 0"></path></svg>
                                            </x-admin.button>
                                        @endif
                                        <x-admin.button variant="danger" wire:click="deleteUser({{ $user->id }})" wire:confirm="Anda yakin ingin menghapus akun {{ $user->name }} secara permanen? Data yang terkait mungkin ikut terhapus.">
                                            <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            Hapus
                                        </x-admin.button>
                                    @endif
                                </div>
                            </x-admin.table.td>
                        </tr>
                    @empty
                        <tr>
                            <x-admin.table.td colspan="5" class="text-center text-zinc-500 py-8">
                                Tidak ada data pengguna yang ditemukan.
                            </x-admin.table.td>
                        </tr>
                    @endforelse
            </x-admin.table>
        </div>

        <div class="p-4 border-t border-zinc-200">
            {{ $users->links(data: ['scrollTo' => false]) }}
        </div>
    </x-admin.card>

    <!-- Modal Edit Pengguna -->
    @if($showEditModal && $selectedUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col" @click.away="$wire.set('showEditModal', false)">
            <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                <h2 class="text-lg font-bold text-zinc-900">Ubah Data {{ ucfirst($selectedUser->role) }}</h2>
                <button wire:click="$set('showEditModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveUser" class="p-5 flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-900">Nama Lengkap</label>
                    <input type="text" wire:model="editName" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                    @error('editName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-900">Email</label>
                    <input type="email" wire:model="editEmail" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                    @error('editEmail') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                @if($selectedUser->role === 'siswa')
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">NISN (Opsional)</label>
                        <input type="text" wire:model="editNisn" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        @error('editNisn') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">NIP/NUPTK (Opsional)</label>
                        <input type="text" wire:model="editNip" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        @error('editNip') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Mata Pelajaran (Opsional)</label>
                        <input type="text" wire:model="editMapel" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        @error('editMapel') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="pt-2 flex justify-end gap-2">
                    <x-admin.button type="button" variant="secondary" wire:click="$set('showEditModal', false)">Batal</x-admin.button>
                    <x-admin.button type="submit" variant="primary">Simpan Perubahan</x-admin.button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Atur Sandi -->
    @if($showPasswordModal && $selectedUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col" @click.away="closePasswordModal">
            <div class="px-5 py-4 border-b border-zinc-200">
                <h2 class="text-lg font-bold text-zinc-900">Sandi Sementara Dibuat</h2>
            </div>
            <div class="p-5 flex flex-col gap-4">
                <p class="text-[15px] text-zinc-600">
                    Sandi sementara untuk <strong>{{ $selectedUser->name }}</strong> berhasil dibuat. Salin dan berikan sandi ini kepada pengguna. 
                    Mereka akan diminta mengganti sandi saat login.
                </p>
                <div class="p-4 bg-zinc-50 border border-zinc-200 rounded-xl flex items-center justify-between gap-4">
                    <span class="font-mono text-xl font-bold tracking-widest text-zinc-900 select-all">{{ $generatedPassword }}</span>
                </div>
                <p class="text-sm text-amber-700 bg-amber-50 p-3 rounded-lg flex gap-2">
                    <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span><strong>Peringatan:</strong> Sandi ini hanya ditampilkan sekali. Jangan tutup dialog ini sebelum menyalin sandi.</span>
                </p>
                <div class="pt-2 flex justify-end">
                    <x-admin.button type="button" variant="primary" wire:click="closePasswordModal">Tutup</x-admin.button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Pindah Kelas -->
    @if($showChangeClassModal && $selectedUser)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col" @click.away="$wire.set('showChangeClassModal', false)">
            <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                <h2 class="text-lg font-bold text-zinc-900">Pindah Kelas</h2>
                <button wire:click="$set('showChangeClassModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="changeClass" class="p-5 flex flex-col gap-4">
                <div class="p-3 bg-blue-50 text-blue-800 rounded-xl text-sm mb-2">
                    Memindahkan siswa <strong>{{ $selectedUser->name }}</strong> ke kelas lain di tahun ajaran aktif.
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-900">Pilih Kelas Baru</label>
                    <select wire:model="newClassroomId" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($availableClassrooms as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->grade }} {{ $cr->major }}</option>
                        @endforeach
                    </select>
                    @error('newClassroomId') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex justify-end gap-2 border-t border-zinc-100">
                    <x-admin.button type="button" variant="secondary" wire:click="$set('showChangeClassModal', false)">Batal</x-admin.button>
                    <x-admin.button type="submit" variant="primary">Simpan Pindah Kelas</x-admin.button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Import Siswa -->
    @if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col" @click.away="$wire.set('showImportModal', false)">
            <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                <h2 class="text-lg font-bold text-zinc-900">Import Data Siswa</h2>
                <button wire:click="$set('showImportModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="importSiswa" class="p-5 flex flex-col gap-4">
                <div class="p-3 bg-blue-50 text-blue-800 border border-blue-200 rounded-xl text-sm">
                    <strong>Format yang didukung:</strong> .xlsx atau .xls<br><br>
                    Pastikan baris pertama adalah judul kolom. Susunan kolom wajib:<br>
                    <strong>Kolom A:</strong> No<br>
                    <strong>Kolom B:</strong> Nama Siswa<br>
                    <strong>Kolom C:</strong> NISN (opsional)<br>
                    <strong>Kolom D:</strong> Kelas (contoh: 10 Akuntansi 1)<br>
                    <strong>Kolom E:</strong> Email
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-900">Pilih File Excel</label>
                    <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <div wire:loading wire:target="importFile" class="text-sm text-blue-600 font-medium">Mengunggah file...</div>
                    @error('importFile') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex justify-end gap-2 border-t border-zinc-100 mt-2">
                    <x-admin.button type="button" variant="secondary" wire:click="$set('showImportModal', false)">Batal</x-admin.button>
                    <x-admin.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="importSiswa, importFile">
                        <span wire:loading.remove wire:target="importSiswa">Mulai Import</span>
                        <span wire:loading wire:target="importSiswa">Memproses...</span>
                    </x-admin.button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
