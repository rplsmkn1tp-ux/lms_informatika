<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Buku Nilai: {{ $classroom->name }}</h1>
            <p class="text-sm mt-1" style="color: #71717a;">Tahun Ajaran {{ $classroom->academicYear->name }} - Semester {{ ucfirst($classroom->academicYear->semester) }}</p>
        </div>
        <div class="flex gap-2 relative" x-data="{ dropdownOpen: false }">
            <a href="{{ route('guru.buku-nilai.index') }}" wire:navigate class="px-4 py-2 text-sm font-semibold rounded-xl transition-colors" style="background-color: #ffffff; color: #18181b; border: 1px solid #e4e4e7; text-decoration: none;">Kembali</a>
            
            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false" class="px-4 py-2 text-sm font-semibold rounded-xl flex items-center gap-2 transition-colors cursor-pointer shadow-sm" style="background-color: #2347C4; color: #ffffff; border: none;">
                    Ekspor
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="dropdownOpen" x-cloak class="absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden" style="display: none; border: 1px solid #e4e4e7;">
                    <a href="{{ route('guru.buku-nilai.export.pdf', [$classroom->id, $classroom->academic_year_id]) }}" target="_blank" class="block px-4 py-2 text-sm hover:bg-zinc-50 transition-colors" style="color: #181a20; text-decoration: none;">PDF</a>
                    <a href="{{ route('guru.buku-nilai.export.excel', [$classroom->id, $classroom->academic_year_id]) }}" target="_blank" class="block px-4 py-2 text-sm hover:bg-zinc-50 transition-colors" style="color: #181a20; text-decoration: none;">CSV</a>
                </div>
            </div>
            
            <button x-data @click="$dispatch('open-settings-modal')" class="px-4 py-2 text-sm font-semibold rounded-xl flex items-center gap-2 transition-colors cursor-pointer shadow-sm" style="background-color: #ffffff; color: #18181b; border: 1px solid #e4e4e7;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Pengaturan
            </button>
        </div>
    </div>

    @error('score')
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2" style="background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
            Gagal Menyimpan: {{ $message }}
        </div>
    @enderror

    <div class="rounded-2xl shadow-sm border overflow-hidden p-6" style="background-color: #ffffff; border-color: #e4e4e7;">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b" style="border-color: #e4e4e7; background-color: #fafafa;">
                        <th class="p-3 font-medium whitespace-nowrap sticky left-0 z-10" style="color: #52525b; background-color: #fafafa;">Siswa</th>
                        @foreach($columns as $col)
                            @php
                                $taskTitle = $columnTaskTitles[$col->group . '_' . $col->seq] ?? null;
                                $colTpIds = $col->getAllLearningObjectiveIds();
                                $linkedCodes = isset($tpsMap) ? $tpsMap->whereIn('id', $colTpIds)->pluck('code')->toArray() : [];
                            @endphp
                            <th class="p-3 font-medium text-center whitespace-nowrap min-w-[100px]" style="color: #52525b;">
                                <div class="font-bold text-zinc-800">{{ $col->label }}</div>
                                <div class="text-[11px] font-medium" style="color: #71717a;">({{ strtoupper($col->group) }})</div>
                                @if($taskTitle)
                                    <div class="text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-2 py-0.5 mt-1 max-w-[120px] truncate mx-auto" title="Tugas: {{ $taskTitle }}">
                                        {{ $taskTitle }}
                                    </div>
                                @endif
                                <div class="mt-1 flex justify-center">
                                    @if(!empty($linkedCodes))
                                        <button type="button" wire:click="openTpModal({{ $col->id }})" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-300 rounded px-1.5 py-0.5 hover:bg-emerald-100 transition-colors" title="Klik untuk ubah kaitan TP">
                                            <span>🎯 {{ implode(', ', $linkedCodes) }}</span>
                                        </button>
                                    @else
                                        <button type="button" wire:click="openTpModal({{ $col->id }})" class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800 bg-amber-50 border border-amber-300 rounded px-1.5 py-0.5 hover:bg-amber-100 transition-colors" title="Klik untuk mengaitkan kolom ini ke Tujuan Pembelajaran">
                                            <span>+ Kaitkan TP</span>
                                        </button>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                        <th class="p-3 font-medium text-center whitespace-nowrap" style="color: #52525b;">Rata UH<br><span class="text-xs font-normal" style="color: #a1a1aa;">{{ $weights->weight_uh }}%</span></th>
                        <th class="p-3 font-medium text-center whitespace-nowrap" style="color: #52525b;">Rata Tugas<br><span class="text-xs font-normal" style="color: #a1a1aa;">{{ $weights->weight_tugas }}%</span></th>
                        <th class="p-3 font-medium text-center whitespace-nowrap" style="color: #52525b;">Rata UAS/MID<br><span class="text-xs font-normal" style="color: #a1a1aa;">{{ $weights->weight_uas_mid ?? 30 }}%</span></th>
                        <th class="p-3 font-medium text-center whitespace-nowrap" style="color: #52525b;">Kehadiran<br><span class="text-xs font-normal" style="color: #a1a1aa;">{{ $weights->weight_absensi ?? 10 }}%</span></th>
                        <th class="p-3 font-bold text-center whitespace-nowrap border-l" style="color: #181a20; border-color: #e4e4e7; background-color: #f4f4f5;">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color: #e4e4e7;">
                    @forelse($students as $student)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="p-3 font-bold sticky left-0 z-10" style="color: #181a20; background-color: #ffffff;">{{ $student->name }}</td>
                            
                            @foreach($columns as $col)
                                @php
                                    $scoreVal = $entriesMap[$col->id . '_' . $student->id] ?? null;
                                    $score = ($scoreVal !== null && $scoreVal !== '') ? (float) $scoreVal : '';
                                @endphp
                                <td class="p-2 text-center">
                                    <input type="number" step="0.01" min="0" max="{{ $col->max_score }}" 
                                        value="{{ $score }}"
                                        wire:change="updateScore({{ $col->id }}, {{ $student->id }}, $event.target.value)"
                                        class="w-16 px-2 py-1 text-center text-sm rounded shadow-sm focus:ring-0"
                                        style="border: 1px solid #e4e4e7; color: #181a20;"
                                    >
                                </td>
                            @endforeach

                            <td class="p-3 text-center" style="color: #3f3f46; background-color: #fafafa;">
                                {{ $finalGrades[$student->id]['avg_uh'] ?? '-' }}
                            </td>
                            <td class="p-3 text-center" style="color: #3f3f46; background-color: #fafafa;">
                                {{ $finalGrades[$student->id]['avg_tugas'] ?? '-' }}
                            </td>
                            <td class="p-3 text-center" style="color: #3f3f46; background-color: #fafafa;">
                                {{ $finalGrades[$student->id]['avg_mid_uas'] ?? '-' }}
                            </td>
                            <td class="p-3 text-center" style="color: #3f3f46; background-color: #fafafa;">
                                {{ $finalGrades[$student->id]['attendance_percentage'] ?? 0 }}%
                            </td>
                            <td class="p-3 text-center font-bold border-l" style="color: #181a20; border-color: #e4e4e7; background-color: #f4f4f5;">
                                {{ $finalGrades[$student->id]['final_score'] ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="p-6 text-center" style="color: #71717a;">Belum ada siswa di kelas ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 text-xs flex gap-4" style="color: #71717a;">
            <p>* Kosongkan isian untuk mengabaikan nilai (tidak masuk pembagi rata-rata).</p>
            <p>* Isi angka 0 jika nilai memang nol.</p>
        </div>
    </div>

    <!-- Settings Modal -->
    <div x-data="{ showSettings: false }" 
         @open-settings-modal.window="showSettings = true"
         class="relative z-50">
        
        <div x-show="showSettings" x-cloak
             @click="showSettings = false"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 transition-opacity" style="display: none;" aria-hidden="true"></div>

        <div x-show="showSettings" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none" style="display: none;">
            <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-50 bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg mx-auto border pointer-events-auto" style="border-color: #e4e4e7;">
                
                <h2 class="text-lg font-bold mb-4 pb-3 border-b" style="color: #181a20; border-color: #e4e4e7;">Pengaturan Buku Nilai</h2>
                
                <form wire:submit="saveSettings">
                    <div class="space-y-6">
                        <div>
                            <h3 class="mb-3 font-bold text-sm" style="color: #181a20;">Jumlah Kolom Penilaian</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: #3f3f46;">Jumlah UH</label>
                                    <input wire:model="settingUhCount" type="number" min="1" max="10" class="w-full rounded-xl shadow-sm px-4 py-2" style="border: 1px solid #e4e4e7;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: #3f3f46;">Jumlah Tugas</label>
                                    <input wire:model="settingTugasCount" type="number" min="1" max="10" class="w-full rounded-xl shadow-sm px-4 py-2" style="border: 1px solid #e4e4e7;">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-3 font-bold text-sm" style="color: #181a20;">Persentase Bobot Nilai Akhir</h3>
                            @error('settingWeightTotal')
                                <p class="text-sm mb-2 font-medium" style="color: #dc2626;">{{ $message }}</p>
                            @enderror
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: #3f3f46;">Bobot UH (%)</label>
                                    <input wire:model="settingWeightUh" type="number" min="0" max="100" class="w-full rounded-xl shadow-sm px-4 py-2" style="border: 1px solid #e4e4e7;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: #3f3f46;">Bobot Tugas (%)</label>
                                    <input wire:model="settingWeightTugas" type="number" min="0" max="100" class="w-full rounded-xl shadow-sm px-4 py-2" style="border: 1px solid #e4e4e7;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: #3f3f46;">Bobot UAS/MID (%)</label>
                                    <input wire:model="settingWeightUasMid" type="number" min="0" max="100" class="w-full rounded-xl shadow-sm px-4 py-2" style="border: 1px solid #e4e4e7;">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" style="color: #3f3f46;">Bobot Kehadiran (%)</label>
                                    <input wire:model="settingWeightAbsensi" type="number" min="0" max="100" class="w-full rounded-xl shadow-sm px-4 py-2" style="border: 1px solid #e4e4e7;">
                                </div>
                            </div>
                            <p class="text-xs mt-2" style="color: #71717a;">Total bobot harus sama dengan 100%.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 pt-4 border-t" style="border-color: #e4e4e7;">
                        <button type="button" @click="showSettings = false" class="px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-colors cursor-pointer" style="background-color: #ffffff; color: #18181b; border: 1px solid #e4e4e7;">Batal</button>
                        <button type="submit" @click="showSettings = false" class="px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-colors cursor-pointer" style="background-color: #2347C4; color: #ffffff; border: none;">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Kaitkan TP ke Kolom Nilai -->
    @if($showTpModal && $editingColumn)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl p-6 shadow-2xl max-w-lg w-full border border-zinc-200 max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                    <div>
                        <h3 class="text-base font-bold text-zinc-900">Kaitkan TP ke Kolom: {{ $editingColumn->label }}</h3>
                        <p class="text-xs text-zinc-500">Pilih satu atau lebih Tujuan Pembelajaran (TP) untuk kolom nilai ini.</p>
                    </div>
                    <button type="button" wire:click="closeTpModal" class="text-zinc-400 hover:text-zinc-600 font-bold p-1">✕</button>
                </div>

                @error('selectedTpIds')
                    <div class="mt-3 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 font-semibold">
                        {{ $message }}
                    </div>
                @enderror

                <div class="my-4 overflow-y-auto space-y-4 pr-1 flex-1">
                    @foreach($elements as $el)
                        <div class="border border-zinc-200 rounded-xl p-3 bg-zinc-50/50">
                            <h4 class="text-xs font-bold text-zinc-700 uppercase mb-2">{{ $el->name }}</h4>
                            <div class="space-y-1.5">
                                @foreach($el->learningObjectives as $tp)
                                    <label class="flex items-start gap-2.5 p-1.5 rounded hover:bg-white cursor-pointer text-xs">
                                        <input type="checkbox" wire:model="selectedTpIds" value="{{ $tp->id }}" class="mt-0.5 rounded text-[#2347C4] focus:ring-[#2347C4]">
                                        <div>
                                            <span class="font-bold text-[#2347C4]">{{ $tp->code }}</span>
                                            <span class="text-zinc-700 ml-1">{{ $tp->title ?? $tp->description }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-3 border-t border-zinc-100 flex justify-end gap-2">
                    <button type="button" wire:click="closeTpModal" class="px-4 py-2 text-xs font-semibold rounded-xl border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="saveColumnTp" class="px-4 py-2 text-xs font-semibold rounded-xl bg-[#2347C4] text-white hover:bg-blue-800 shadow-sm cursor-pointer">
                        Simpan Kaitan TP
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
