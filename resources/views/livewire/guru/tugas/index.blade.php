<div>
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 tracking-tight">Manajemen Tugas</h1>
            <p class="text-sm text-zinc-500 mt-1">Kelola tugas siswa, pantau pengumpulan, dan berikan penilaian serta umpan balik.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.tugas.create') }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#2347C4] hover:bg-[#1A3596] !text-white font-bold text-sm shadow-sm transition-all cursor-pointer" style="background-color: #2347C4; color: #ffffff !important; text-decoration: none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color: #ffffff;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span style="color: #ffffff !important; font-weight: 700;">Buat Tugas Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Flash -->
    @if(session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-lg leading-none">&times;</button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-lg leading-none">&times;</button>
        </div>
    @endif

    <!-- Filter & Search Bar -->
    <div class="mb-6 bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm flex flex-col md:flex-row md:items-center gap-3 justify-between">
        <div class="flex-1 relative">
            <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari judul tugas..." 
                class="w-full pl-10 pr-4 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4] transition-all"
            >
        </div>

        <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
            <!-- Filter Kelas -->
            <select wire:model.live="classFilter" class="px-3.5 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4] text-zinc-700">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                @endforeach
            </select>

            <!-- Filter Status -->
            <select wire:model.live="statusFilter" class="px-3.5 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4] text-zinc-700">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="lewat">Lewat Tenggat</option>
                <option value="draft">Draft</option>
            </select>
        </div>
    </div>

    <!-- Daftar Tugas -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-600">
                <thead class="bg-zinc-50/80 border-b border-zinc-200 text-xs uppercase font-semibold text-zinc-500 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">Judul & Detail Tugas</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Tenggat Waktu (WIB)</th>
                        <th class="py-3.5 px-4">Progres Pengumpulan</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($tugasList as $tugas)
                        <tr class="hover:bg-zinc-50/60 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex flex-col">
                                    <a href="{{ route('guru.tugas.detail', $tugas->id) }}" wire:navigate class="font-bold text-zinc-900 text-base hover:text-[#2347C4] transition-colors">
                                        {{ $tugas->title }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-zinc-400 mt-1 flex-wrap">
                                        <span>Max Nilai: <strong>{{ $tugas->max_score }}</strong></span>
                                        @if($tugas->lesson)
                                            <span>&bull;</span>
                                            <span class="text-zinc-600">Materi: <span class="font-medium text-zinc-800">{{ $tugas->lesson->title }}</span></span>
                                        @endif
                                        @if($tugas->effectiveMeetingNo())
                                            <span>&bull;</span>
                                            <span class="text-zinc-600">Pertemuan ke-{{ $tugas->effectiveMeetingNo() }}</span>
                                        @endif
                                        @if(!$tugas->lesson && !$tugas->effectiveMeetingNo())
                                            <span>&bull;</span>
                                            <a href="{{ route('guru.tugas.edit', $tugas->id) }}" wire:navigate class="text-amber-700 hover:text-amber-800 font-medium inline-flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 text-[11px]" title="Klik untuk melengkapi materi dan pertemuan">+ Atur Materi & Pertemuan</a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-1 max-w-[200px]">
                                    @forelse($tugas->classrooms as $cls)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200/60">
                                            {{ $cls->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-zinc-400 italic">Belum di-assign</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($tugas->due_at)
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-zinc-800 text-xs">
                                            {{ \Carbon\Carbon::parse($tugas->due_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                        </span>
                                        <span class="text-[11px] text-zinc-400">
                                            {{ \Carbon\Carbon::parse($tugas->due_at)->setTimezone('Asia/Jakarta')->diffForHumans() }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Tanpa batas waktu</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="w-44">
                                    <div class="flex justify-between items-center text-xs mb-1">
                                        <span class="font-bold text-zinc-700">{{ $tugas->submittedCount }}/{{ $tugas->totalStudents }} Siswa</span>
                                        <span class="text-[11px] text-zinc-400">{{ $tugas->progressPercentage }}%</span>
                                    </div>
                                    <div class="w-full bg-zinc-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-[#2347C4] h-2 rounded-full transition-all duration-300" style="width: {{ $tugas->progressPercentage }}%"></div>
                                    </div>
                                    <div class="text-[10px] text-zinc-400 mt-1">
                                        Dinilai: {{ $tugas->gradedCount }} siswa
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $tugas->statusBadge }}">
                                    {{ $tugas->statusLabel }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Detail / Penilaian -->
                                    <a href="{{ route('guru.tugas.detail', $tugas->id) }}" wire:navigate class="p-1.5 text-zinc-600 hover:text-[#2347C4] hover:bg-blue-50 rounded-lg transition-colors" title="Periksa & Nilai">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </a>

                                    <!-- Ubah -->
                                    <a href="{{ route('guru.tugas.edit', $tugas->id) }}" wire:navigate class="p-1.5 text-zinc-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Ubah Tugas">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>

                                    <!-- Duplikat -->
                                    <button type="button" wire:click="duplicate({{ $tugas->id }})" wire:loading.attr="disabled" class="p-1.5 text-zinc-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Duplikat Tugas">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button" wire:click="delete({{ $tugas->id }})" wire:confirm="Apakah Anda yakin ingin menghapus tugas ini? (Tugas tidak dapat dihapus jika sudah ada siswa yang mengumpulkan jawaban)" class="p-1.5 text-zinc-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Tugas">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-zinc-100 text-zinc-400 flex items-center justify-center mb-4">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-zinc-800">Belum ada tugas</h3>
                                    <p class="text-xs text-zinc-500 mt-1 mb-4">Tugas yang Anda buat untuk kelas akan muncul di sini.</p>
                                    <a href="{{ route('guru.tugas.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#2347C4] !text-white text-xs font-bold shadow-sm hover:bg-[#1A3596] transition-colors cursor-pointer" style="background-color: #2347C4; color: #ffffff !important; text-decoration: none;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #ffffff;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        <span style="color: #ffffff !important; font-weight: 700;">Buat Tugas Sekarang</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tugasList->hasPages())
            <div class="p-4 border-t border-zinc-200">
                {{ $tugasList->links() }}
            </div>
        @endif
    </div>
</div>
