<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb & Nav -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <a href="{{ route('guru.tugas.index') }}" wire:navigate class="hover:text-[#2347C4] flex items-center gap-1 font-medium transition-colors">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Kembali ke Daftar Tugas
            </a>
            <span>/</span>
            <span class="text-zinc-800 font-semibold">{{ $isEdit ? 'Ubah Tugas' : 'Buat Tugas Baru' }}</span>
        </div>
    </div>

    <form wire:submit.prevent="save('published')">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Main Column (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Utama -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-zinc-200 shadow-sm space-y-5">
                    <h2 class="text-lg font-bold text-zinc-900 border-b border-zinc-100 pb-3">Informasi Tugas</h2>
                    
                    <!-- Judul -->
                    <div>
                        <label for="title" class="block text-sm font-semibold text-zinc-800 mb-1">Judul Tugas <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            id="title"
                            wire:model="title" 
                            placeholder="Contoh: Praktikum Topologi Jaringan & Subnetting"
                            class="w-full px-4 py-2.5 text-base bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4] transition-all"
                            required
                        >
                        @error('title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Instruksi -->
                    <div>
                        <label for="instructions" class="block text-sm font-semibold text-zinc-800 mb-1">Instruksi Tugas</label>
                        <textarea 
                            id="instructions"
                            wire:model="instructions" 
                            rows="6"
                            placeholder="Tuliskan petunjuk pengerjaan tugas secara rinci untuk siswa..."
                            class="w-full px-4 py-3 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4] transition-all leading-relaxed"
                        ></textarea>
                        @error('instructions') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Lampiran dari Guru -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-zinc-200 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900">Lampiran Materi / Lembar Kerja</h2>
                            <p class="text-xs text-zinc-500">Unggah berkas soal/panduan (Maks. 10 MB) atau sertakan tautan eksternal.</p>
                        </div>
                    </div>

                    <!-- Daftar Lampiran yang sudah ada -->
                    @if(!empty($existingAttachments))
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-zinc-700 uppercase tracking-wider block">Lampiran Terpasang:</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($existingAttachments as $index => $att)
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-zinc-200 bg-zinc-50/60">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            @if(($att['type'] ?? '') === 'file')
                                                <div class="w-9 h-9 rounded-lg bg-blue-100 text-[#2347C4] flex items-center justify-center flex-shrink-0">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                                </div>
                                            @else
                                                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                                </div>
                                            @endif
                                            <div class="truncate">
                                                <div class="text-sm font-semibold text-zinc-800 truncate">{{ $att['name'] }}</div>
                                                <div class="text-[11px] text-zinc-400">
                                                    @if(($att['type'] ?? '') === 'file')
                                                        {{ isset($att['size']) ? round($att['size'] / 1024, 1) . ' KB' : 'File Dokumen' }}
                                                    @else
                                                        <a href="{{ $att['url'] }}" target="_blank" class="text-blue-600 hover:underline truncate block max-w-[180px]">{{ $att['url'] }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="removeExistingAttachment({{ $index }})" class="p-1.5 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors ml-2">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Upload Berkas Baru -->
                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">Unggah File Lampiran</label>
                        <div class="border-2 border-dashed border-zinc-200 hover:border-[#2347C4] rounded-2xl p-6 text-center bg-zinc-50/50 hover:bg-blue-50/20 transition-all cursor-pointer relative">
                            <input 
                                type="file" 
                                wire:model="newFiles" 
                                multiple 
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.jpg,.jpeg,.png,.webp"
                            >
                            <div class="flex flex-col items-center pointer-events-none">
                                <svg class="w-10 h-10 text-zinc-400 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                <span class="text-sm font-semibold text-zinc-700">Klik atau geser file ke sini</span>
                                <span class="text-xs text-zinc-400 mt-1">Mendukung PDF, Word, Excel, PPT, Gambar, ZIP (Maks. 10 MB per file)</span>
                            </div>
                        </div>

                        <div wire:loading wire:target="newFiles" class="text-xs text-[#2347C4] mt-2 flex items-center gap-1 font-medium">
                            <svg class="animate-spin h-3.5 w-3.5 text-[#2347C4]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengunggah berkas...
                        </div>

                        @error('newFiles.*') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                        <!-- Preview new files selected -->
                        @if(!empty($newFiles))
                            <div class="mt-3 space-y-2">
                                <span class="text-xs font-semibold text-zinc-600">Berkas Baru Siap Diunggah:</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($newFiles as $idx => $f)
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-blue-50/60 border border-blue-200 text-xs">
                                            <span class="truncate font-medium text-blue-900">{{ $f->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="removeNewFile({{ $idx }})" class="text-rose-600 hover:text-rose-800 ml-2">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Tambah Tautan Eksternal -->
                    <div class="pt-4 border-t border-zinc-100">
                        <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">Atau Tambah Tautan Eksternal (Link)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                            <input 
                                type="text" 
                                wire:model="newLinkTitle" 
                                placeholder="Judul Tautan (misal: Panduan Praktik)" 
                                class="sm:col-span-2 px-3 py-2 text-xs bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20"
                            >
                            <input 
                                type="url" 
                                wire:model="newLinkUrl" 
                                placeholder="https://..." 
                                class="sm:col-span-2 px-3 py-2 text-xs bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20"
                            >
                            <button 
                                type="button" 
                                wire:click="addLinkAttachment" 
                                class="px-3 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-1"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Tambah
                            </button>
                        </div>
                        @error('newLinkTitle') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        @error('newLinkUrl') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Tipe Pengumpulan Jawaban -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-zinc-200 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-zinc-900 border-b border-zinc-100 pb-3">Tipe Pengumpulan Jawaban Siswa</h2>
                    <p class="text-xs text-zinc-500">Tentukan format respon yang diperbolehkan untuk dikumpulkan oleh siswa (bisa kombinasi):</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <!-- File Upload -->
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-zinc-200 bg-zinc-50/50 hover:bg-white cursor-pointer transition-all">
                            <input type="checkbox" wire:model="submission_types" value="file" class="mt-1 rounded text-[#2347C4] focus:ring-[#2347C4]">
                            <div>
                                <span class="text-sm font-semibold text-zinc-900 block">Unggah File</span>
                                <span class="text-xs text-zinc-500">Dokumen PDF, Word, Gambar, atau ZIP.</span>
                            </div>
                        </label>

                        <!-- Tulis Teks -->
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-zinc-200 bg-zinc-50/50 hover:bg-white cursor-pointer transition-all">
                            <input type="checkbox" wire:model="submission_types" value="text" class="mt-1 rounded text-[#2347C4] focus:ring-[#2347C4]">
                            <div>
                                <span class="text-sm font-semibold text-zinc-900 block">Tulis Teks</span>
                                <span class="text-xs text-zinc-500">Mengetik jawaban langsung di sistem.</span>
                            </div>
                        </label>

                        <!-- Tautan URL -->
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-zinc-200 bg-zinc-50/50 hover:bg-white cursor-pointer transition-all">
                            <input type="checkbox" wire:model="submission_types" value="link" class="mt-1 rounded text-[#2347C4] focus:ring-[#2347C4]">
                            <div>
                                <span class="text-sm font-semibold text-zinc-900 block">Tautan (URL)</span>
                                <span class="text-xs text-zinc-500">Google Docs, GitHub, Canva, dll.</span>
                            </div>
                        </label>
                    </div>
                    @error('submission_types') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Right Sidebar Column (1 col) -->
            <div class="space-y-6">
                <!-- Penugasan Kelas & Waktu -->
                <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-zinc-900 border-b border-zinc-100 pb-2">Sasaran Kelas</h3>
                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider">Pilih Kelas <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="selectAllClassrooms" class="text-[11px] font-semibold text-[#2347C4] hover:underline">Pilih Semua</button>
                                <span class="text-zinc-300">|</span>
                                <button type="button" wire:click="deselectAllClassrooms" class="text-[11px] font-semibold text-zinc-400 hover:text-zinc-600">Batal Semua</button>
                            </div>
                        </div>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            @forelse($classrooms as $cls)
                                <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-zinc-50 cursor-pointer text-sm font-medium text-zinc-800">
                                    <input type="checkbox" wire:model="selectedClassrooms" value="{{ $cls->id }}" class="rounded text-[#2347C4] focus:ring-[#2347C4]">
                                    <span>{{ $cls->name }}</span>
                                </label>
                            @empty
                                <span class="text-xs text-zinc-400 italic">Tidak ada kelas yang Anda ampu saat ini.</span>
                            @endforelse
                        </div>
                        @error('selectedClassrooms') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-zinc-100 space-y-3">
                        <div>
                            <label for="opens_at" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">Tanggal Mulai (WIB)</label>
                            <input 
                                type="datetime-local" 
                                id="opens_at"
                                wire:model="opens_at" 
                                class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20"
                            >
                        </div>

                        <div>
                            <label for="due_at" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">Tenggat Waktu (WIB)</label>
                            <input 
                                type="datetime-local" 
                                id="due_at"
                                wire:model="due_at" 
                                class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20"
                            >
                            @error('due_at') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="max_score" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">Nilai Maksimal</label>
                            <input 
                                type="number" 
                                id="max_score"
                                wire:model="max_score" 
                                min="1" 
                                max="1000"
                                class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20"
                            >
                            @error('max_score') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Opsi Kebijakan Pengumpulan -->
                <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm space-y-4">
                    <h3 class="text-base font-bold text-zinc-900 border-b border-zinc-100 pb-2">Kebijakan Pengumpulan</h3>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="allow_late" class="mt-1 rounded text-[#2347C4] focus:ring-[#2347C4]">
                        <div>
                            <span class="text-sm font-semibold text-zinc-800 block">Izinkan Terlambat</span>
                            <span class="text-xs text-zinc-500">Siswa masih dapat mengirim setelah tenggat, namun ditandai terlambat.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="allow_resubmit" class="mt-1 rounded text-[#2347C4] focus:ring-[#2347C4]">
                        <div>
                            <span class="text-sm font-semibold text-zinc-800 block">Izinkan Kumpul Ulang</span>
                            <span class="text-xs text-zinc-500">Siswa dapat memperbarui jawabannya sebelum dinilai atau jika diminta revisi.</span>
                        </div>
                    </label>
                <!-- Sinkronisasi Buku Nilai -->
                <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm space-y-4">
                    <div class="border-b border-zinc-100 pb-2 flex items-center justify-between">
                        <h3 class="text-base font-bold text-zinc-900">Sinkronisasi Buku Nilai</h3>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Otomatis Masuk</span>
                    </div>
                    <p class="text-xs text-zinc-500 leading-relaxed">Nilai yang diberikan guru pada tugas ini akan langsung otomatis masuk ke kolom Buku Nilai kelas terkait.</p>

                    <div class="space-y-3">
                        <div>
                            <label for="grade_group" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">Kategori Nilai</label>
                            <select id="grade_group" wire:model.live="grade_group" class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 text-zinc-800">
                                <option value="tugas">Tugas (Rata Tugas)</option>
                                <option value="uh">Ulangan Harian (UH)</option>
                                <option value="mid">Ujian Tengah Semester (MID)</option>
                                <option value="uas">Ujian Akhir Semester (UAS)</option>
                            </select>
                        </div>

                        <div>
                            <label for="grade_seq" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">Kolom Target di Buku Nilai</label>
                            <select id="grade_seq" wire:model.live="grade_seq" class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 text-zinc-800 font-semibold">
                                @if($grade_group === 'mid')
                                    <option value="1">Kolom MID</option>
                                @elseif($grade_group === 'uas')
                                    <option value="1">Kolom UAS</option>
                                @else
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}">
                                            {{ $grade_group === 'uh' ? 'Kolom UH ' . $i : 'Kolom Tugas ' . $i }}
                                        </option>
                                    @endfor
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Hubungkan dengan Materi & Pertemuan -->
                <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm space-y-4">
                    <div class="border-b border-zinc-100 pb-2">
                        <h3 class="text-base font-bold text-zinc-900 flex items-center gap-2">
                            <span>TP, Materi & Pertemuan</span>
                            <span class="text-[10px] font-bold text-[#2347C4] bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">Kurikulum Merdeka</span>
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Tugas dikaitkan dengan Capaian TP dan materi agar terstruktur rapi.</p>
                    </div>

                    <!-- Pilihan Tujuan Pembelajaran (TP) -->
                    <div>
                        <label for="learning_objective_id" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">
                            Tujuan Pembelajaran (TP) <span class="text-rose-500">*</span>
                        </label>
                        <select id="learning_objective_id" wire:model.live="learning_objective_id" class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 text-zinc-800 font-medium">
                            <option value="">-- Pilih Tujuan Pembelajaran (Wajib) --</option>
                            @foreach($learningObjectives as $tp)
                                <option value="{{ $tp->id }}">
                                    {{ $tp->code }} - {{ $tp->title ?? $tp->description }} ({{ $tp->element?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('learning_objective_id') <span class="text-xs text-rose-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                        <span class="text-[11px] text-zinc-400 mt-1 block">Kaitan TP ini akan otomatis masuk ke Tab Ketercapaian TP Siswa.</span>
                    </div>

                    <!-- Pilihan Materi -->
                    <div>
                        <label for="lesson_id" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">
                            Materi Pelajaran (Opsional)
                        </label>
                        <select id="lesson_id" wire:model.live="lesson_id" class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 text-zinc-800">
                            <option value="">-- Pilih Materi Pembelajaran --</option>
                            @foreach($lessons as $lsn)
                                <option value="{{ $lsn->id }}">
                                    {{ $lsn->title }} {{ $lsn->learningObjective?->element ? '(' . $lsn->learningObjective->element->name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[11px] text-zinc-400 mt-1 block">Menghubungkan tugas dengan bab materi informatika terkait.</span>
                    </div>

                    <!-- Pilihan Pertemuan Ke- -->
                    <div>
                        <label for="meeting_no" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">
                            Pertemuan Ke-
                        </label>
                        <select id="meeting_no" wire:model.live="meeting_no" class="w-full px-3 py-2 text-sm bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 text-zinc-800 font-semibold">
                            <option value="">-- Pilih Nomor Pertemuan --</option>
                            @for($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}">Pertemuan ke-{{ $i }}</option>
                            @endfor
                        </select>
                        <span class="text-[11px] text-zinc-400 mt-1 block">Nomor urut pertemuan/tatap muka pembelajaran.</span>
                    </div>

                    <!-- Hubungkan ke Sesi Presensi / Agenda (Opsional) -->
                    @if(!empty($attendanceSessions) && $attendanceSessions->count() > 0)
                        <div>
                            <label for="attendance_session_id" class="block text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">
                                Tautkan ke Sesi Agenda/Presensi (Opsional)
                            </label>
                            <select id="attendance_session_id" wire:model.live="attendance_session_id" class="w-full px-3 py-2 text-xs bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 text-zinc-700">
                                <option value="">-- Tanpa Tautan Sesi Presensi --</option>
                                @foreach($attendanceSessions as $attSession)
                                    <option value="{{ $attSession->id }}">
                                        Pertemuan {{ $attSession->meeting_no }} ({{ \Carbon\Carbon::parse($attSession->meeting_date)->format('d/m/Y') }}) - Kelas {{ $attSession->classroom?->name }} {{ $attSession->topic ? '[' . $attSession->topic . ']' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Live Preview Keterangan Tugas -->
                    <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200 text-xs space-y-1">
                        <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider block">Tampilan Detail di Tabel:</span>
                        <div class="flex items-center gap-2 text-zinc-600 font-medium flex-wrap">
                            <span>Max Nilai: <strong>{{ $max_score }}</strong></span>
                            @php
                                $selectedLessonObj = $lesson_id ? $lessons->firstWhere('id', $lesson_id) : null;
                            @endphp
                            @if($selectedLessonObj)
                                <span>&bull;</span>
                                <span class="text-[#2347C4]">Materi: {{ $selectedLessonObj->title }}</span>
                            @endif
                            @if($meeting_no)
                                <span>&bull;</span>
                                <span class="text-purple-700 font-bold">Pertemuan ke-{{ $meeting_no }}</span>
                            @endif
                            @if(!$selectedLessonObj && !$meeting_no)
                                <span class="text-zinc-400 italic">(Pilih materi & pertemuan di atas untuk menampilkan label)</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Simpan -->
                <div class="space-y-3 pt-2">
                    <button 
                        type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-[#2347C4] hover:bg-[#1A3596] text-white font-bold text-sm shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        {{ $isEdit ? 'Simpan & Perbarui Tugas' : 'Terbitkan Tugas Sekarang' }}
                    </button>

                    <button 
                        type="button" 
                        wire:click="save('draft')" 
                        class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-zinc-50 text-zinc-700 border border-zinc-200 font-semibold text-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        Simpan sebagai Draft
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
