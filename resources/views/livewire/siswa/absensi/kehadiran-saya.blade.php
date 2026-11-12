<div class="space-y-6">
    <div>
        <flux:heading size="xl">Kehadiran Saya</flux:heading>
        <flux:subheading>Riwayat kehadiran Anda pada semester ini.</flux:subheading>
    </div>

    @if(!$classroom)
        <flux:card>
            <div class="text-center text-zinc-500 py-4">
                Anda belum tergabung dalam kelas aktif untuk semester ini.
            </div>
        </flux:card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:card class="flex flex-col items-center justify-center py-6">
                <div class="text-4xl font-bold {{ $percentage < 75 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $percentage }}%
                </div>
                <div class="text-sm text-zinc-500 mt-2">Total Kehadiran</div>
                @if($percentage < 75)
                    <flux:badge color="danger" class="mt-2">Perlu perhatian</flux:badge>
                @endif
            </flux:card>
            
            <flux:card class="flex flex-col items-center justify-center py-6">
                <div class="text-3xl font-bold text-blue-600">{{ $summary['hadir'] }}</div>
                <div class="text-sm text-zinc-500 mt-2">Hadir</div>
            </flux:card>
            
            <flux:card class="flex flex-col items-center justify-center py-6">
                <div class="text-3xl font-bold text-amber-600">{{ $summary['izin'] + $summary['sakit'] }}</div>
                <div class="text-sm text-zinc-500 mt-2">Izin / Sakit</div>
            </flux:card>
            
            <flux:card class="flex flex-col items-center justify-center py-6">
                <div class="text-3xl font-bold text-red-600">{{ $summary['alpa'] }}</div>
                <div class="text-sm text-zinc-500 mt-2">Alpa</div>
            </flux:card>
        </div>

        <flux:card>
            <flux:heading size="lg" class="mb-4">Riwayat Pertemuan</flux:heading>
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Pertemuan</flux:table.column>
                        <flux:table.column>Tanggal</flux:table.column>
                        <flux:table.column>Materi</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Catatan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($records as $record)
                            <flux:table.row>
                                <flux:table.cell>{{ $record->session->meeting_no }}</flux:table.cell>
                                <flux:table.cell>{{ $record->session->meeting_date->format('d/m/Y') }}</flux:table.cell>
                                <flux:table.cell>{{ $record->session->topic ?? '-' }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($record->status === 'hadir')
                                        <flux:badge color="success">Hadir</flux:badge>
                                    @elseif($record->status === 'izin')
                                        <flux:badge color="warning">Izin</flux:badge>
                                    @elseif($record->status === 'sakit')
                                        <flux:badge color="warning">Sakit</flux:badge>
                                    @else
                                        <flux:badge color="danger">Alpa</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>{{ $record->note ?? '-' }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-zinc-500">Belum ada riwayat kehadiran.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    @endif
</div>
