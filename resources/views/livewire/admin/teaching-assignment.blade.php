<div>
    <x-slot:title>Plot Mengajar</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN A PLOT MENGAJAR</x-slot>
    <x-slot:description>Tugaskan guru ke kelas-kelas untuk mengajar.</x-slot>

    <x-slot:headerActions>
        <x-admin.button variant="secondary" wire:navigate href="{{ route('admin.jadwal.waktu') }}">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Waktu Pelajaran
        </x-admin.button>
        <x-admin.button variant="primary" onclick="Livewire.dispatch('createAssignment')">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
            Tambah Plot Mengajar
        </x-admin.button>
    </x-slot:headerActions>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4L12 14.01l-3-3"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <x-admin.table>
                <x-slot:head>
                    <x-admin.table.th>Guru</x-admin.table.th>
                    <x-admin.table.th>Kelas</x-admin.table.th>
                    <x-admin.table.th>Tahun Ajaran</x-admin.table.th>
                    <x-admin.table.th>Mata Pelajaran</x-admin.table.th>
                    <x-admin.table.th>Aksi</x-admin.table.th>
                </x-slot:head>
                @forelse($assignments as $assignment)
                <tr>
                    <x-admin.table.td>
                        <div class="flex items-center gap-3 min-w-[200px]">
                            <span class="w-9 h-9 flex-none rounded-full bg-[#E8EDFC] text-[#2347C4] flex items-center justify-center text-[13px] font-extrabold uppercase">
                                {{ substr($assignment->teacher->name, 0, 2) }}
                            </span>
                            <div class="flex flex-col min-w-0 leading-tight">
                                <span class="font-bold truncate">{{ $assignment->teacher->name }}</span>
                            </div>
                        </div>
                    </x-admin.table.td>
                    <x-admin.table.td>
                        <span class="font-semibold text-zinc-900">{{ $assignment->classroom->grade }} {{ $assignment->classroom->major ?: $assignment->classroom->name }}</span>
                    </x-admin.table.td>
                    <x-admin.table.td>
                        {{ $assignment->classroom->academicYear->name }}
                    </x-admin.table.td>
                    <x-admin.table.td>
                        {{ $assignment->subject }}
                    </x-admin.table.td>
                    <x-admin.table.td>
                        <div class="flex flex-nowrap gap-1.5">
                            <x-admin.button variant="secondary" icon-only wire:navigate href="{{ route('admin.jadwal.assignment', $assignment->id) }}" title="Atur Jadwal Mengajar" aria-label="Atur Jadwal Mengajar">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </x-admin.button>
                            <x-admin.button variant="secondary" icon-only wire:click.prevent="edit({{ $assignment->id }})" title="Ubah plot" aria-label="Ubah plot">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </x-admin.button>
                            <x-admin.button variant="danger" icon-only wire:click.prevent="deleteAssignment({{ $assignment->id }})" wire:confirm="Yakin ingin menghapus plot mengajar ini?" title="Hapus plot" aria-label="Hapus plot">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
                            </x-admin.button>
                        </div>
                    </x-admin.table.td>
                </tr>
                @empty
                <tr>
                    <x-admin.table.td colspan="5" class="text-center text-zinc-500 py-8">
                        Belum ada data plot mengajar.
                    </x-admin.table.td>
                </tr>
                @endforelse
            </x-admin.table>
        </div>
    </x-admin.card>

    <!-- Modal Form -->
    <div x-data="{ show: @entangle('showModal').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50">
        
        <!-- Backdrop -->
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Modal Panel -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="show" 
                 @click.outside="$wire.set('showModal', false)"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col relative z-50">
                 
                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-zinc-900">{{ $assignmentId ? 'Ubah Plot Mengajar' : 'Tambah Plot Mengajar' }}</h2>
                    <button type="button" wire:click="$set('showModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="save" class="p-5 flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Guru (Hanya yg Aktif)</label>
                        <select wire:model="teacher_id" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Kelas (Tahun Ajaran Aktif)</label>
                        <select wire:model="classroom_id" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}">{{ $classroom->grade }} {{ $classroom->major ?: $classroom->name }}</option>
                            @endforeach
                        </select>
                        @error('classroom_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Mata Pelajaran</label>
                        <input type="text" wire:model="subject" placeholder="Misal: Informatika" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 outline-none w-full font-inherit text-[15px]">
                        @error('subject') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-zinc-100">
                        <x-admin.button type="button" variant="secondary" wire:click="$set('showModal', false)">Batal</x-admin.button>
                        <x-admin.button type="submit" variant="primary">Simpan</x-admin.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
