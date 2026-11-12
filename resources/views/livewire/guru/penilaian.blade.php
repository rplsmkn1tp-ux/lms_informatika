<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: #181a20;">Antrean Penilaian</h1>
            <p class="text-sm mt-1" style="color: #71717a;">Kelola antrean tugas yang belum dinilai</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4L12 14.01l-3-3"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2" style="background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
            <svg class="w-5 h-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl shadow-sm overflow-hidden" style="background-color: #ffffff; border: 1px solid #e4e4e7;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr style="background-color: #fafafa; border-bottom: 1px solid #e4e4e7; color: #71717a;">
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Siswa</th>
                        <th class="px-5 py-3 font-semibold">Tugas / Asesmen</th>
                        <th class="px-5 py-3 font-semibold text-center">Percobaan</th>
                        <th class="px-5 py-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color: #e4e4e7;">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <span class="text-sm font-bold block" style="color: #181a20;">{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y') : '-' }}</span>
                                <span class="text-xs font-mono" style="color: #71717a;">{{ $submission->submitted_at ? $submission->submitted_at->format('H:i') : '' }}</span>
                            </td>
                            <td class="px-5 py-4 font-bold" style="color: #181a20;">
                                {{ $submission->student->name }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium" style="color: #181a20;">{{ $submission->assessment->title }}</div>
                                <div class="text-xs mt-1">
                                    <span class="px-2 py-0.5 rounded border" style="background-color: #f4f4f5; color: #52525b; border-color: #e4e4e7;">TP: {{ $submission->assessment->learningObjective->code ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold" style="background-color: #f4f4f5; color: #3f3f46;">
                                    Ke-{{ $submission->attempt }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <button type="button" wire:click="openGradingModal({{ $submission->id }})" class="text-xs font-semibold py-1.5 px-3 rounded-lg flex items-center justify-center transition-colors shadow-sm cursor-pointer" style="background-color: #2347C4; color: #ffffff; border: none;">
                                    Beri Nilai
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: #d4d4d8;"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                    <span class="font-medium text-[15px]" style="color: #71717a;">Hore! Tidak ada antrean tugas yang perlu dinilai saat ini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($submissions->hasPages())
                <div class="p-4 border-t" style="border-color: #e4e4e7;">
                    {{ $submissions->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Form Penilaian -->
    <div x-data="{ show: @entangle('showGradingModal').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div x-show="show" 
             @click="$wire.closeGradingModal()"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 transition-opacity" aria-hidden="true"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-50 bg-white p-6 rounded-2xl shadow-xl w-full max-w-3xl mx-auto border pointer-events-auto max-h-[90vh] flex flex-col" style="border-color: #e4e4e7;">
                
                <h2 class="text-lg font-bold mb-4 pb-3 border-b" style="color: #181a20; border-color: #e4e4e7;">Penilaian Tugas</h2>
                
                <div class="overflow-y-auto pr-2" style="max-height: calc(90vh - 120px);">
                    @if($selectedSubmission)
                        <div class="mb-6 p-4 rounded-xl border" style="background-color: #fafafa; border-color: #e4e4e7;">
                            <div class="grid grid-cols-2 gap-4 text-[15px] mb-4">
                                <div>
                                    <span class="text-sm font-medium block" style="color: #71717a;">Nama Siswa</span>
                                    <span class="font-bold" style="color: #181a20;">{{ $selectedSubmission->student->name }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium block" style="color: #71717a;">Tugas</span>
                                    <span class="font-bold" style="color: #181a20;">{{ $selectedSubmission->assessment->title }}</span>
                                </div>
                            </div>
                            
                            @if($selectedSubmission->file_path)
                                <div class="mt-4 pt-4 border-t" style="border-color: #e4e4e7;">
                                    <span class="block text-sm font-medium mb-2" style="color: #71717a;">File Jawaban</span>
                                    <button type="button" wire:click="downloadFile({{ $selectedSubmission->id }})" class="text-sm font-semibold py-2 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-sm cursor-pointer" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #27272a;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Unduh File Jawaban
                                    </button>
                                </div>
                            @endif
                            
                            @if($selectedSubmission->answers && $selectedSubmission->answers->count() > 0)
                                <div class="mt-4 pt-4 border-t" style="border-color: #e4e4e7;">
                                    <span class="block text-sm font-medium mb-3" style="color: #71717a;">Jawaban Tertulis / Esai</span>
                                    <ul class="space-y-3">
                                        @foreach($selectedSubmission->answers as $answer)
                                            <li class="p-4 border rounded-xl shadow-sm" style="background-color: #ffffff; border-color: #e4e4e7;">
                                                <div class="text-sm font-bold mb-2 pb-2 border-b" style="color: #3f3f46; border-color: #f4f4f5;">Pertanyaan: {{ $answer->question->content ?? 'Soal' }}</div>
                                                <div class="text-[15px] whitespace-pre-wrap" style="color: #181a20;">{{ $answer->answer_text }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <form wire:submit="saveGrade" class="mt-4">
                            <div class="mb-4">
                                <label class="block text-sm font-bold mb-1" style="color: #181a20;">Skor / Nilai (0-100)</label>
                                <input type="number" wire:model="score" min="0" max="100" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-bold mb-1" style="color: #181a20;">Umpan Balik (Feedback)</label>
                                <textarea wire:model="feedback" placeholder="Tuliskan catatan untuk siswa di sini..." rows="4" class="w-full rounded-xl shadow-sm px-4 py-2" style="background-color: #ffffff; border: 1px solid #e4e4e7; color: #181a20;"></textarea>
                            </div>

                            <div class="mt-6 pt-4 border-t flex justify-end gap-3" style="border-color: #e4e4e7;">
                                <button type="button" wire:click="closeGradingModal" class="px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-colors cursor-pointer" style="background-color: #ffffff; color: #4A515C; border: 1px solid #e4e4e7;">Batal</button>
                                <button type="submit" class="px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition-colors cursor-pointer" style="background-color: #2347C4; color: #ffffff; border: none;">Simpan Nilai</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
