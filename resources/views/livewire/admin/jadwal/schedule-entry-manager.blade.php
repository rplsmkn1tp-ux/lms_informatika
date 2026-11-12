<div>
    <x-slot:title>Atur Jadwal Mengajar</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN A PLOT MENGAJAR A JADWAL</x-slot>
    <x-slot:description>Jadwal mengajar untuk {{ $assignment->teacher->name }} di kelas {{ $assignment->classroom->grade }} {{ $assignment->classroom->major ?: $assignment->classroom->name }} ({{ $assignment->subject }}).</x-slot>

    <x-slot:headerActions>
        <x-admin.button variant="secondary" wire:navigate href="{{ route('admin.teaching-assignments') }}">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Kembali
        </x-admin.button>
        <x-admin.button variant="primary" wire:click="create">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
            Tambah Jadwal
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
                    <x-admin.table.th>Hari</x-admin.table.th>
                    <x-admin.table.th>Jam Ke-</x-admin.table.th>
                    <x-admin.table.th>Ruangan</x-admin.table.th>
                    <x-admin.table.th>Catatan</x-admin.table.th>
                    <x-admin.table.th>Aksi</x-admin.table.th>
                </x-slot:head>

                @php
                    $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                @endphp
                
                @forelse($entries as $entry)
                <tr>
                    <x-admin.table.td>
                        <span class="font-semibold text-zinc-900">{{ $days[$entry->day_of_week] ?? $entry->day_of_week }}</span>
                    </x-admin.table.td>
                    <x-admin.table.td>
                        {{ $entry->start_period_no }} @if($entry->start_period_no !== $entry->end_period_no) - {{ $entry->end_period_no }} @endif
                    </x-admin.table.td>
                    <x-admin.table.td>
                        {{ $entry->room ?: '-' }}
                    </x-admin.table.td>
                    <x-admin.table.td>
                        {{ $entry->note ?: '-' }}
                    </x-admin.table.td>
                    <x-admin.table.td>
                        <div class="flex flex-nowrap gap-1.5">
                            <x-admin.button variant="secondary" icon-only wire:click.prevent="edit({{ $entry->id }})" title="Ubah" aria-label="Ubah">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </x-admin.button>
                            <x-admin.button variant="danger" icon-only wire:click.prevent="deleteEntry({{ $entry->id }})" wire:confirm="Yakin ingin menghapus jadwal ini?" title="Hapus" aria-label="Hapus">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
                            </x-admin.button>
                        </div>
                    </x-admin.table.td>
                </tr>
                @empty
                <tr>
                    <x-admin.table.td colspan="5" class="text-center text-zinc-500 py-8">
                        Belum ada jadwal untuk plot mengajar ini.
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
        
        <div x-show="show" 
             class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm transition-opacity"></div>
        
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="show" 
                 @click.outside="$wire.set('showModal', false)"
                 class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden flex flex-col relative z-50">
                 
                <div class="px-5 py-4 border-b border-zinc-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-zinc-900">{{ $entryId ? 'Ubah Jadwal' : 'Tambah Jadwal' }}</h2>
                    <button type="button" wire:click="$set('showModal', false)" class="text-zinc-500 hover:text-zinc-900 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="save" class="p-5 flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Hari</label>
                        <select wire:model="day_of_week" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] outline-none">
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                            <option value="7">Minggu</option>
                        </select>
                        @error('day_of_week') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-zinc-900">Mulai Jam Ke-</label>
                            <input type="number" wire:model="start_period_no" min="1" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] outline-none">
                            @error('start_period_no') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-zinc-900">Sampai Jam Ke-</label>
                            <input type="number" wire:model="end_period_no" min="1" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] outline-none">
                            @error('end_period_no') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Ruangan (Opsional)</label>
                        <input type="text" wire:model="room" placeholder="Misal: Lab Komputer 1" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] outline-none">
                        @error('room') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-900">Catatan (Opsional)</label>
                        <input type="text" wire:model="note" class="h-11 px-3 border border-zinc-300 rounded-xl focus:border-[#2347C4] outline-none">
                        @error('note') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
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
