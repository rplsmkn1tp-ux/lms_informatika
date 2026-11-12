<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Peta Ketercapaian Kompetensi</h1>
            <p class="text-sm mt-1" style="color: #71717a;">Visualisasi Ketercapaian Ulangan Harian (TP) Siswa</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold mb-1" style="color: #181a20;">Tahun Ajaran</label>
            <select wire:model.live="selectedYear" class="w-full rounded-xl shadow-sm px-4 py-2 focus:ring-0" style="background-color: #ffffff; color: #181a20; border: 1px solid #e4e4e7;">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1" style="color: #181a20;">Kelas</label>
            <select wire:model.live="selectedClass" class="w-full rounded-xl shadow-sm px-4 py-2 focus:ring-0" style="background-color: #ffffff; color: #181a20; border: 1px solid #e4e4e7;">
                <option value="">Pilih Kelas</option>
                @foreach($classrooms as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($selectedClass && $selectedYear)
    <div class="rounded-2xl shadow-sm overflow-hidden" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
        <div class="flex justify-between items-center p-4 border-b" style="border-color: #e4e4e7;">
            <h3 class="text-lg font-bold" style="color: #181a20;">Matriks Ketuntasan (KKM: 75)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr style="background-color: #fafafa; border-bottom: 1px solid #e4e4e7; color: #71717a;">
                        <th class="px-5 py-3 font-semibold w-16">No</th>
                        <th class="px-5 py-3 font-semibold">Nama Siswa</th>
                        @foreach($columns as $col)
                            <th class="px-5 py-3 font-semibold text-center" title="{{ $col->label }}">{{ \Illuminate\Support\Str::limit($col->label, 15) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color: #e4e4e7;">
                    @forelse($matrix as $index => $row)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-5 py-4 font-medium" style="color: #181a20;">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4">
                                <div class="font-bold" style="color: #181a20;">{{ $row['student']->name }}</div>
                                <div class="text-[13px] font-mono mt-0.5" style="color: #71717a;">{{ $row['student']->nisn ?? '-' }}</div>
                            </td>
                            
                            @foreach($columns as $col)
                                @php
                                    $score = $row['scores'][$col->id];
                                @endphp
                                <td class="px-5 py-4 text-center">
                                    @if(is_null($score))
                                        <span style="color: #a1a1aa;">-</span>
                                    @elseif($score >= 75)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" style="background-color: #dcfce7; color: #166534;">
                                            {{ $score }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" style="background-color: #fee2e2; color: #991b1b;">
                                            {{ $score }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($columns) }}" class="px-5 py-8 text-center font-medium" style="color: #71717a;">
                                Tidak ada data siswa atau kolom Ulangan Harian di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="text-center py-12 rounded-2xl shadow-sm border" style="background-color: #ffffff; border-color: #e4e4e7; color: #71717a;">
            <svg class="mx-auto h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #d4d4d8;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="font-medium text-[15px]">Silakan pilih tahun ajaran dan kelas untuk melihat peta ketercapaian.</span>
        </div>
    @endif
</div>
