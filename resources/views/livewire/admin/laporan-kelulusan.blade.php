<div>
    <x-slot:title>Laporan Kelulusan</x-slot>
    <x-slot:breadcrumbs>PANEL ADMIN · LAPORAN</x-slot>
    <x-slot:description>Persentase kelulusan seluruh kelas</x-slot>

    <div class="mb-6 max-w-sm">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tahun Ajaran</label>
        <select wire:model.live="selectedYear" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 focus:border-blue-500 focus:ring-blue-500">
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto">
            <x-admin.table>
                <x-slot:head>
                    <x-admin.table.th>No</x-admin.table.th>
                    <x-admin.table.th>Kelas</x-admin.table.th>
                    <x-admin.table.th class="text-center">Total Siswa</x-admin.table.th>
                    <x-admin.table.th class="text-center">Lulus (Tuntas Semua TP)</x-admin.table.th>
                    <x-admin.table.th class="text-center">Persentase</x-admin.table.th>
                </x-slot:head>
                @forelse($reportData as $index => $row)
                    <tr>
                        <x-admin.table.td>{{ $index + 1 }}</x-admin.table.td>
                        <x-admin.table.td class="font-medium text-zinc-900 dark:text-white">{{ $row['class']->name }}</x-admin.table.td>
                        <x-admin.table.td class="text-center">{{ $row['students_count'] }}</x-admin.table.td>
                        <x-admin.table.td class="text-center text-green-600 font-medium">{{ $row['lulus_count'] }}</x-admin.table.td>
                        <x-admin.table.td class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-24 bg-zinc-200 rounded-full h-2.5 dark:bg-zinc-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $row['percentage'] }}%"></div>
                                </div>
                                <span class="text-sm font-medium">{{ $row['percentage'] }}%</span>
                            </div>
                        </x-admin.table.td>
                    </tr>
                @empty
                    <tr>
                        <x-admin.table.td colspan="5" class="text-center text-zinc-500">Tidak ada data kelas untuk tahun ajaran ini.</x-admin.table.td>
                    </tr>
                @endforelse
            </x-admin.table>
        </div>
    </x-admin.card>
</div>
