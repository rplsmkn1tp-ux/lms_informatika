<div>
    <x-slot:title>Tahun Ajaran & Semester</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · TAHUN AJARAN</x-slot>
    <x-slot:description>Hanya satu semester yang aktif. Kelas, materi, dan pendaftaran siswa mengikuti semester aktif.</x-slot>
    
    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start">
        
        <x-admin.card :title="$editingId ? 'Ubah semester' : 'Tambah semester'" style="flex: 1 1 100%; min-width: 0;">
            <form wire:submit="save">
                <div style="display: flex; flex-wrap: wrap; gap: 16px 20px; align-items: flex-end">
                    <div style="flex: 1 1 220px; display: flex; flex-direction: column; gap: 6px">
                        <label for="ta-nama" style="font-size: 14px; font-weight: 700">Tahun ajaran</label>
                        <input id="ta-nama" type="text" wire:model="name" placeholder="2027/2028" style="height: 44px; box-sizing: border-box; padding: 0 14px; border-radius: 12px; border: 1px solid #C9CDC4; background: #FFFFFF; font: inherit; font-size: 14px; color: #15191F">
                        @error('name') 
                            <span style="font-size: 13px; color: #B4441A">{{ $message }}</span>
                        @else
                            <span style="font-size: 13px; color: #4A515C">Format: 2026/2027, tanpa semester.</span>
                        @enderror
                    </div>

                    <fieldset style="flex: 1 1 260px; margin: 0; padding: 0; border: 0; display: flex; flex-direction: column; gap: 8px">
                        <legend style="font-size: 14px; font-weight: 700; padding: 0; margin-bottom: 6px">Semester</legend>
                        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
                            <label style="display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 0 14px; border-radius: 12px; border: {{ $semester === 'ganjil' ? '2px solid #2347C4' : '1px solid #D5D8D1' }}; background: {{ $semester === 'ganjil' ? '#E8EDFC' : '#FFFFFF' }}; font-size: 14px; font-weight: {{ $semester === 'ganjil' ? '700' : '600' }}; cursor: pointer">
                                <input type="radio" wire:model="semester" value="ganjil" style="accent-color: #2347C4; width: 18px; height: 18px">Ganjil
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 0 14px; border-radius: 12px; border: {{ $semester === 'genap' ? '2px solid #2347C4' : '1px solid #D5D8D1' }}; background: {{ $semester === 'genap' ? '#E8EDFC' : '#FFFFFF' }}; font-size: 14px; font-weight: {{ $semester === 'genap' ? '700' : '600' }}; cursor: pointer">
                                <input type="radio" wire:model="semester" value="genap" style="accent-color: #2347C4; width: 18px; height: 18px">Genap
                            </label>
                        </div>
                        @error('semester') <span style="font-size: 13px; color: #B4441A">{{ $message }}</span> @enderror
                    </fieldset>

                    <div style="flex: 1 1 260px; display: flex; flex-direction: column; justify-content: flex-end; gap: 10px; height: 82px;">
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1px solid #2347C4; background: #2347C4; color: #FFFFFF; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
                                {{ $editingId ? 'Simpan Perubahan' : 'Simpan semester' }}
                            </button>
                            @if($editingId)
                                <button type="button" wire:click="cancelEdit" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; border: 1px solid #D5D8D1; background: #FFFFFF; color: #15191F; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap">
                                    Batal
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </x-admin.card>

        <x-admin.card title="Daftar semester" description='Klik "Jadikan aktif" untuk berpindah semester. Semester lama tetap tersimpan sebagai arsip.' style="flex: 1 1 100%; min-width: 0; padding-bottom: 0;">
            <x-admin.table :headers="['Tahun ajaran', 'Semester', 'Kelas', 'Status', 'Aksi']">
                @foreach($academicYears as $ay)
                    <tr>
                        <x-admin.td>
                            <span style="font-weight: 700">{{ $ay->name }}</span>
                        </x-admin.td>
                        <x-admin.td>
                            {{ ucfirst($ay->semester) }}
                        </x-admin.td>
                        <x-admin.td>
                            @if($ay->classrooms_count > 0)
                                {{ $ay->classrooms_count }} kelas
                            @else
                                <span style="color: #4A515C">0 kelas</span>
                            @endif
                        </x-admin.td>
                        <x-admin.td>
                            <x-admin.badge :status="$ay->is_active ? 'aktif' : 'tidak aktif'" />
                        </x-admin.td>
                        <x-admin.td>
                            <div style="display: flex; flex-wrap: nowrap; gap: 6px">
                                @if(!$ay->is_active)
                                    <button type="button" wire:click="setActive({{ $ay->id }})" style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 12px; border-radius: 10px; border: 1px solid #2347C4; background: #2347C4; color: #FFFFFF; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; white-space: nowrap">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M8 12.5l2.5 2.5L16 9.5"></path></svg>
                                        Jadikan aktif
                                    </button>
                                @endif

                                <button type="button" wire:click="edit({{ $ay->id }})" style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 12px; border-radius: 10px; border: 1px solid #D5D8D1; background: #FFFFFF; color: #15191F; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; white-space: nowrap">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"></path></svg>
                                    Ubah
                                </button>

                                @if($ay->is_active || $ay->classrooms_count > 0)
                                    <button type="button" disabled title="{{ $ay->is_active ? 'Semester aktif tidak bisa dihapus' : 'Sudah punya kelas' }}" style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 12px; border-radius: 10px; border: 1px solid #E2E4DF; background: #F6F7F4; color: #8A929E; font: inherit; font-size: 13px; font-weight: 600; cursor: not-allowed; white-space: nowrap">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
                                        Hapus
                                    </button>
                                @else
                                    <button type="button" wire:click="delete({{ $ay->id }})" wire:confirm="Yakin ingin menghapus semester ini?" style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 12px; border-radius: 10px; border: 1px solid #E3B9A6; background: #FFFFFF; color: #8A3412; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"></path></svg>
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-admin.table>

            <div style="display: flex; gap: 10px; align-items: flex-start; padding: 14px 2px 18px; font-size: 13px; color: #3B414A">
                <span style="display: flex; color: #4A515C; margin-top: 1px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5M12 8h.01"></path></svg></span>
                <span>Semester yang sedang aktif atau sudah punya kelas tidak bisa dihapus, supaya data nilai dan rapor siswa tetap aman.</span>
            </div>
        </x-admin.card>

    </div>
</div>
