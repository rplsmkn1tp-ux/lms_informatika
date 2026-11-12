<div>
    <x-slot:title>Kurikulum Master</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · KURIKULUM</x-slot>
    <x-slot:description>Kelola elemen dan Tujuan Pembelajaran (TP) master untuk seluruh sistem.</x-slot>

    <flux:heading size="xl" class="mb-6">Kurikulum Master</flux:heading>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Elements List -->
        <div class="lg:col-span-1 space-y-4">
            <flux:heading size="lg">Elemen</flux:heading>
            @foreach($elements as $element)
                <flux:card class="cursor-pointer transition-colors {{ $selectedElementId == $element->id ? 'border-primary-500 ring-1 ring-primary-500' : 'hover:border-zinc-300' }}" wire:click="selectElement({{ $element->id }})">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-lg">{{ $element->code }}</div>
                            <div class="text-sm font-medium">{{ $element->name }}</div>
                        </div>
                        <flux:button size="sm" variant="subtle" icon="pencil" class="!px-2" wire:click.stop="editElement({{ $element->id }})" />
                    </div>
                    <div class="text-xs text-zinc-500 mt-2 line-clamp-2">{{ $element->description ?: 'Tidak ada deskripsi.' }}</div>
                </flux:card>
            @endforeach

            @if($editingElementId)
                <flux:modal wire:model="editingElementId" show class="min-w-[22rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Ubah Deskripsi Elemen</flux:heading>
                        </div>
                        <flux:textarea wire:model="elementDescription" label="Deskripsi" rows="4" />
                        <div class="flex gap-2 justify-end">
                            <flux:button wire:click="cancelEditElement" variant="subtle">Batal</flux:button>
                            <flux:button wire:click="saveElement" variant="primary">Simpan</flux:button>
                        </div>
                    </div>
                </flux:modal>
            @endif
        </div>

        <!-- TPs List -->
        <div class="lg:col-span-3">
            @if($selectedElementId)
                <flux:card>
                    <div class="flex justify-between items-center mb-4">
                        <flux:heading size="lg">Tujuan Pembelajaran (TP) Master</flux:heading>
                    </div>

                    <form wire:submit="saveTp" class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <flux:heading size="md" class="mb-4">{{ $tpEditingId ? 'Ubah TP' : 'Tambah TP Baru' }}</flux:heading>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <flux:input wire:model="tpCode" label="Kode TP" placeholder="Misal: BK-1" required />
                            <flux:input wire:model="tpSortOrder" type="number" label="Urutan" required />
                            <div class="md:col-span-2">
                                <flux:input wire:model="tpTitle" label="Judul TP" required />
                            </div>
                            <div class="md:col-span-2">
                                <flux:textarea wire:model="tpDescription" label="Deskripsi" rows="2" />
                            </div>
                            <flux:input wire:model="tpKktp" type="number" label="KKTP Khusus" description="Kosongkan untuk menggunakan default ({{ $default_kktp }})" min="0" max="100" />
                        </div>
                        <div class="flex gap-2">
                            <flux:button type="submit" variant="primary">{{ $tpEditingId ? 'Simpan Perubahan' : 'Tambah TP' }}</flux:button>
                            @if($tpEditingId)
                                <flux:button type="button" wire:click="cancelEditTp" variant="subtle">Batal</flux:button>
                            @endif
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Urutan</flux:table.column>
                                <flux:table.column>Kode</flux:table.column>
                                <flux:table.column>Judul</flux:table.column>
                                <flux:table.column>KKTP</flux:table.column>
                                <flux:table.column>Aksi</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @forelse($tps as $tp)
                                    <flux:table.row>
                                        <flux:table.cell>{{ $tp->sort_order }}</flux:table.cell>
                                        <flux:table.cell class="font-medium">{{ $tp->code }}</flux:table.cell>
                                        <flux:table.cell>{{ $tp->title }}</flux:table.cell>
                                        <flux:table.cell>{{ $tp->kktp ?? $default_kktp }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex gap-2">
                                                <flux:button size="sm" wire:click="editTp({{ $tp->id }})">Ubah</flux:button>
                                                <flux:button size="sm" wire:click="deleteTp({{ $tp->id }})" wire:confirm="Hapus TP ini?" variant="danger">Hapus</flux:button>
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="5" class="text-center text-zinc-500">Belum ada Tujuan Pembelajaran.</flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>
            @else
                <div class="text-center py-12 text-zinc-500">
                    Pilih elemen di samping untuk mengelola Tujuan Pembelajaran.
                </div>
            @endif
        </div>
    </div>
</div>
