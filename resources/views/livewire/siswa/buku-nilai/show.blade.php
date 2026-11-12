<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Buku Nilai</flux:heading>
            <flux:subheading>Lihat daftar nilai untuk setiap kelas.</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:select wire:model.live="selectedYear" class="w-48">
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }} - {{ ucfirst($year->semester) }}</option>
                @endforeach
            </flux:select>
        </div>
    </div>

    @if($classrooms->isEmpty())
        <flux:card>
            <div class="text-center py-8 text-zinc-500">
                <flux:icon.inbox class="w-12 h-12 mx-auto mb-4 text-zinc-400" />
                <p>Belum ada data kelas untuk tahun ajaran ini.</p>
            </div>
        </flux:card>
    @else
        <div class="flex gap-6 flex-col md:flex-row mb-6">
            <div class="w-full md:w-64 flex-shrink-0">
                <flux:navlist>
                    <flux:navlist.group heading="Pilih Kelas">
                        @foreach($classrooms as $classroom)
                            <flux:navlist.item 
                                wire:click="$set('selectedClass', {{ $classroom->id }})"
                                :current="$selectedClass === $classroom->id"
                            >
                                {{ $classroom->name }}
                            </flux:navlist.item>
                        @endforeach
                    </flux:navlist.group>
                </flux:navlist>
            </div>

            <div class="flex-1">
                @if($selectedClass && $finalGrades)
                    <flux:card>
                        <flux:heading size="lg" class="mb-6">Rincian Nilai - {{ \App\Models\Classroom::find($selectedClass)->name }}</flux:heading>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                            <div class="p-4 rounded-lg bg-zinc-50 border border-zinc-200">
                                <div class="text-sm text-zinc-500 mb-1">Rata-rata UH</div>
                                <div class="text-2xl font-semibold">{{ $finalGrades['avg_uh'] ?? '-' }}</div>
                            </div>
                            <div class="p-4 rounded-lg bg-zinc-50 border border-zinc-200">
                                <div class="text-sm text-zinc-500 mb-1">Rata-rata Tugas</div>
                                <div class="text-2xl font-semibold">{{ $finalGrades['avg_tugas'] ?? '-' }}</div>
                            </div>
                            <div class="p-4 rounded-lg bg-zinc-50 border border-zinc-200">
                                <div class="text-sm text-zinc-500 mb-1">Nilai UAS/MID</div>
                                <div class="text-2xl font-semibold">{{ $finalGrades['avg_mid_uas'] ?? '-' }}</div>
                            </div>
                            <div class="p-4 rounded-lg bg-zinc-50 border border-zinc-200">
                                <div class="text-sm text-zinc-500 mb-1">Kehadiran</div>
                                <div class="text-2xl font-semibold">{{ $finalGrades['attendance_percentage'] ?? 0 }}%</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-6 bg-indigo-50 border border-indigo-100 rounded-lg mb-8">
                            <div>
                                <h3 class="text-lg font-semibold text-indigo-900">Nilai Akhir</h3>
                                <p class="text-indigo-700 text-sm">Berdasarkan bobot penilaian guru.</p>
                            </div>
                            <div class="text-4xl font-bold text-indigo-600">
                                {{ $finalGrades['final_score'] ?? '-' }}
                            </div>
                        </div>

                        <h3 class="font-medium text-zinc-900 mb-4 border-b pb-2">Rincian Per Kolom</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b text-zinc-600 bg-zinc-50">
                                        <th class="p-3 font-medium">Jenis</th>
                                        <th class="p-3 font-medium">Kolom Nilai</th>
                                        <th class="p-3 font-medium text-right">Nilai Maks</th>
                                        <th class="p-3 font-medium text-right">Nilai Diperoleh</th>
                                        <th class="p-3 font-medium text-center">Status KKTP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @forelse($gradeItems as $item)
                                        <tr class="hover:bg-zinc-50/60 transition-colors">
                                            <td class="p-3 text-zinc-600 font-semibold">{{ strtoupper($item->grade_group) }}</td>
                                            <td class="p-3 font-medium text-zinc-900">
                                                <div>{{ $item->nama_kolom }}</div>
                                                @if($item->raw_title && $item->raw_title !== $item->nama_kolom)
                                                    <div class="text-xs text-zinc-500 font-normal">{{ $item->raw_title }}</div>
                                                @endif
                                            </td>
                                            <td class="p-3 text-right text-zinc-500">{{ $item->max_score }}</td>
                                            <td class="p-3 text-right font-semibold">
                                                @if($item->is_graded)
                                                    <span class="{{ $item->is_achieved ? 'text-zinc-900' : 'text-rose-600' }}">
                                                        {{ $item->score }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                        Belum dinilai
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center">
                                                @if($item->is_graded)
                                                    @if($item->is_achieved)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            Tercapai
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                                            Belum Tercapai
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                                                        Belum dinilai
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-4 text-center text-zinc-500">Belum ada rincian nilai diinputkan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </flux:card>
                @endif
            </div>
        </div>
    @endif
</div>
