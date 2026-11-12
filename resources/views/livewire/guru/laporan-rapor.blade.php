<div>
    <div class="mb-4">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Laporan Rapor</h1>
        <p class="text-sm mt-1" style="color: #71717a;">Rekapitulasi Nilai Akhir Siswa</p>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1" style="color: #181a20;">Tahun Ajaran</label>
            <select wire:model.live="selectedYear" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1" style="color: #181a20;">Kelas</label>
            <select wire:model.live="selectedClass" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                <option value="">Pilih Kelas</option>
                @foreach($classrooms as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($selectedClass && $selectedYear)
    <div class="rounded-2xl shadow-sm overflow-hidden" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-5 border-b" style="border-color: #e4e4e7;">
            <h3 class="text-lg font-bold mb-3 sm:mb-0" style="color: #181a20;">Daftar Nilai Akhir</h3>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                @if(!$isPublished && count($students) > 0)
                    <button wire:click="publishRapor" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl text-sm font-bold inline-flex items-center justify-center gap-2 transition-colors shadow-sm w-full sm:w-auto" style="background-color: #f59e0b; color: #ffffff; border: none;">
                        <span wire:loading.remove wire:target="publishRapor">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="publishRapor" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        Terbitkan Rapor
                    </button>
                @elseif(count($students) > 0)
                    <span class="px-3 py-2 rounded-xl text-sm font-bold inline-flex items-center gap-2 w-full sm:w-auto justify-center" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                        Rapor Diterbitkan
                    </span>
                @endif

                <a href="{{ route('guru.buku-nilai.export.pdf', ['classroom' => $selectedClass, 'academicYear' => $selectedYear]) }}" target="_blank" class="px-4 py-2 rounded-xl text-sm font-bold inline-flex items-center justify-center gap-2 transition-colors shadow-sm w-full sm:w-auto" style="background-color: #2347C4; color: #ffffff; text-decoration: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Cetak Buku Nilai
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr style="background-color: #fafafa; border-bottom: 1px solid #e4e4e7; color: #71717a;">
                        <th class="px-4 py-3 font-semibold">No</th>
                        <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 font-semibold">UH (30%)</th>
                        <th class="px-4 py-3 font-semibold">Tugas (30%)</th>
                        <th class="px-4 py-3 font-semibold">MID/UAS (30%)</th>
                        <th class="px-4 py-3 font-semibold">Absensi (10%)</th>
                        <th class="px-4 py-3 font-semibold">Nilai Akhir</th>
                        <th class="px-4 py-3 font-semibold">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color: #e4e4e7;">
                @forelse($students as $index => $student)
                    @php
                        $data = $reportData[$student->id] ?? null;
                    @endphp
                    <tr class="hover:bg-zinc-50/50 transition-colors">
                        <td class="px-4 py-3" style="color: #71717a;">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3">
                            <div class="font-bold" style="color: #181a20;">{{ $student->name }}</div>
                            <div class="text-[13px] font-mono" style="color: #a1a1aa;">{{ $student->nisn ?? '-' }}</div>
                        </td>
                        
                        @if($data)
                            <td class="px-4 py-3" style="color: #181a20;">{{ $data['avg_uh'] }}</td>
                            <td class="px-4 py-3" style="color: #181a20;">{{ $data['avg_tugas'] }}</td>
                            <td class="px-4 py-3" style="color: #181a20;">{{ $data['avg_uas_mid'] }}</td>
                            <td class="px-4 py-3" style="color: #181a20;">{{ $data['attendance_score'] }}%</td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-lg" style="color: {{ $data['final_score'] >= 75 ? '#059669' : '#e11d48' }};">
                                    {{ $data['final_score'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $predStyle = $data['predicate'] == 'A' || $data['predicate'] == 'B' 
                                        ? 'background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;' 
                                        : ($data['predicate'] == 'C' 
                                            ? 'background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;' 
                                            : 'background-color: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3;');
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold inline-block" style="{{ $predStyle }}">
                                    {{ $data['predicate'] }}
                                </span>
                            </td>
                        @else
                            <td colspan="6" class="px-4 py-3 text-center italic" style="color: #a1a1aa;">Data belum tersedia</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center" style="color: #71717a;">
                            Tidak ada data siswa di kelas ini.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="text-center py-12 rounded-2xl shadow-sm font-medium" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #71717a;">
            <svg class="mx-auto h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #d4d4d8;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Silakan pilih tahun ajaran dan kelas untuk melihat rapor.
        </div>
    @endif
</div>
