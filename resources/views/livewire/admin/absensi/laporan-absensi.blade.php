<div class="space-y-6">
    <div>
        <flux:heading size="xl">Laporan Absensi Siswa</flux:heading>
        <flux:subheading>Rekapitulasi tingkat kehadiran siswa per kelas.</flux:subheading>
    </div>

    <flux:card>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <flux:select wire:model.live="academicYearId" label="Semester">
                <option value="">-- Pilih Semester --</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}</option>
                @endforeach
            </flux:select>
            
            <flux:select wire:model.live="classroomId" label="Kelas">
                <option value="">-- Pilih Kelas --</option>
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                @endforeach
            </flux:select>
        </div>
        
        @if($classroomId)
            <div class="flex justify-end mb-4">
                <flux:button size="sm" icon="document-arrow-down" wire:click="export" disabled>Ekspor (TBA)</flux:button>
            </div>
            
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nama Siswa</flux:table.column>
                        <flux:table.column>NISN</flux:table.column>
                        <flux:table.column>Kehadiran (%)</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($students as $student)
                            <flux:table.row>
                                <flux:table.cell>{{ $student->name }}</flux:table.cell>
                                <flux:table.cell>{{ $student->nisn }}</flux:table.cell>
                                <flux:table.cell>
                                    <span class="{{ $student->attendance_percentage < 75 ? 'text-red-600 font-bold' : '' }}">
                                        {{ $student->attendance_percentage }}%
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($student->attendance_percentage < 75)
                                        <flux:badge color="danger">Perlu Perhatian</flux:badge>
                                    @else
                                        <flux:badge color="success">Baik</flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-zinc-500">Tidak ada siswa di kelas ini.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif
    </flux:card>
</div>
