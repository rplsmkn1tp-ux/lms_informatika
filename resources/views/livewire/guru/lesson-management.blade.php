<div>
    {{-- Flash Notifications --}}
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] rounded-xl flex items-center gap-3 font-medium shadow-sm">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('material_feedback'))
        <div class="mb-6 p-4 bg-[#EBF3FF] text-[#2347C4] border border-[#BDD7FF] rounded-xl flex items-center gap-3 font-medium shadow-sm">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>{{ session('material_feedback') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-[#FEE2E2] text-[#DC2626] border border-[#FCA5A5] rounded-xl flex items-center gap-3 font-medium shadow-sm">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($isModalOpen)
        <!-- ==================== FORM BUAT / UBAH MATERI (FULL VIEW) ==================== -->
        <div class="bg-white min-h-screen rounded-2xl border border-[#E2E4DF] shadow-sm overflow-hidden mb-12">
            <!-- Header Form -->
            <div class="sticky top-0 z-20 bg-white flex justify-between items-center px-6 py-4 border-b border-[#E2E4DF]">
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="closeModal" class="p-2 text-[#4A515C] hover:text-[#15191F] hover:bg-[#F4F5F2] rounded-lg transition-colors" title="Kembali">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-[#15191F]">{{ $lessonId ? 'Ubah Materi Pembelajaran' : 'Buat Materi Pembelajaran Baru' }}</h1>
                        <p class="text-xs text-[#4A515C] mt-0.5">Lengkapi bahan bacaan literasi (PDF), video pembelajaran (YouTube), dan penugasan siswa.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-semibold text-sm hover:bg-[#F4F5F2] transition-colors">
                        Batal
                    </button>
                    <button type="button" wire:click="save" class="px-5 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Simpan Materi</span>
                    </button>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6 md:p-8 max-w-5xl mx-auto flex flex-col gap-8">
                <form wire:submit="save" class="flex flex-col gap-8">
                    
                    <!-- 1. IDENTITAS MATERI -->
                    <div class="bg-[#F8F9FA] p-6 rounded-2xl border border-[#E2E4DF] flex flex-col gap-6">
                        <div class="flex items-center justify-between border-b border-[#E2E4DF] pb-3">
                            <h2 class="text-base font-bold text-[#15191F] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#2347C4] text-white text-xs flex items-center justify-center font-bold">1</span>
                                <span>Informasi & Tujuan Pembelajaran</span>
                            </h2>
                            <span class="text-xs text-[#DC2626] font-semibold">* Wajib diisi</span>
                        </div>

                        <!-- Judul & Estimasi Waktu -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-[#15191F] mb-1.5">
                                    Judul Materi <span class="text-[#DC2626]">*</span>
                                </label>
                                <input type="text" wire:model="title" placeholder="Contoh: Berpikir Komputasional dan Dekomposisi Masalah" 
                                       class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5">
                                @error('title') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-[#15191F] mb-1.5">
                                    Estimasi Waktu (Menit)
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="estimated_minutes" min="1" placeholder="45" 
                                           class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5 pr-14">
                                    <span class="absolute right-3.5 top-2.5 text-xs text-[#4A515C] font-semibold">menit</span>
                                </div>
                                @error('estimated_minutes') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Dropdown Bertingkat: Elemen & ATP -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-[#15191F] mb-1.5">
                                    Elemen Pembelajaran <span class="text-[#DC2626]">*</span>
                                </label>
                                <select wire:model.live="element_id" class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5">
                                    <option value="">-- Pilih Elemen Pembelajaran --</option>
                                    @foreach($availableElements as $el)
                                        <option value="{{ $el->id }}">{{ $el->code ? $el->code . ' - ' : '' }}{{ $el->name }}</option>
                                    @endforeach
                                </select>
                                @error('element_id') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-sm font-bold text-[#15191F]">
                                        Tujuan Pembelajaran (ATP / TP) <span class="text-[#DC2626]">*</span>
                                    </label>
                                    <button type="button" wire:click="openQuickAtpModal" class="text-xs font-bold text-[#2347C4] hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        <span>+ Tambah ATP baru</span>
                                    </button>
                                </div>
                                <select wire:model.live="learning_objective_id" class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5" {{ empty($element_id) ? 'disabled' : '' }}>
                                    <option value="">{{ empty($element_id) ? '-- Pilih Elemen Terlebih Dahulu --' : '-- Pilih ATP / TP --' }}</option>
                                    @foreach($availableTps as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->code }} - {{ $tp->title }}</option>
                                    @endforeach
                                </select>
                                @error('learning_objective_id') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Kode Subbab & Urutan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-[#15191F] mb-1.5">
                                    Kode Materi / Subbab <span class="text-[#DC2626]">*</span>
                                </label>
                                <input type="text" wire:model="code" placeholder="Contoh: BK-1.1" 
                                       class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm font-mono focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5">
                                @error('code') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-[#15191F] mb-1.5">
                                    Nomor Urutan Tampil
                                </label>
                                <input type="number" wire:model="sort_order" min="1" 
                                       class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5">
                                @error('sort_order') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Tujuan Pembelajaran Materi (Summary, Editable) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-bold text-[#15191F]">
                                    Tujuan Pembelajaran Materi <span class="text-[#DC2626]">*</span>
                                </label>
                                <span class="text-[11px] text-[#4A515C]">Terisi otomatis dari ATP terpilih, bisa disesuaikan khusus untuk materi ini.</span>
                            </div>
                            <textarea wire:model="summary" rows="2" placeholder="Tuliskan tujuan khusus yang ingin dicapai siswa setelah mempelajari materi ini..." 
                                      class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5"></textarea>
                            @error('summary') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Uraian / Pengantar Materi (Content, Opsional) -->
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1.5">
                                Uraian / Pengantar Materi <span class="text-xs font-normal text-[#4A515C]">(Opsional)</span>
                            </label>
                            <textarea wire:model="content" rows="4" placeholder="Tuliskan pengantar singkat, instruksi belajar bagi siswa, atau rangkuman konsep penting sebelum membuka bahan bacaan..." 
                                      class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2.5"></textarea>
                            @error('content') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- 2. KELAS TUJUAN & STATUS PENERBITAN -->
                    <div class="bg-[#F8F9FA] p-6 rounded-2xl border border-[#E2E4DF] flex flex-col gap-6">
                        <div class="flex items-center justify-between border-b border-[#E2E4DF] pb-3">
                            <h2 class="text-base font-bold text-[#15191F] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#2347C4] text-white text-xs flex items-center justify-center font-bold">2</span>
                                <span>Distribusi Kelas & Status Penerbitan</span>
                            </h2>
                            <span class="text-xs text-[#DC2626] font-semibold">* Wajib dipilih</span>
                        </div>

                        <!-- Kelas Tujuan Checkboxes -->
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-2">
                                Kelas Tujuan (Bisa lebih dari satu) <span class="text-[#DC2626]">*</span>
                            </label>
                            @if($myClassrooms->isEmpty())
                                <p class="text-sm text-[#DC2626] bg-red-50 p-3 rounded-xl border border-red-200">
                                    Akun Anda belum memiliki penugasan kelas mengajar. Hubungi admin untuk ditugaskan ke kelas.
                                </p>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach($myClassrooms as $cls)
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors {{ in_array((string)$cls->id, $selectedClassrooms) ? 'border-[#2347C4] bg-[#EEF2FF]' : 'border-[#E2E4DF] bg-white hover:bg-[#F4F5F2]' }}">
                                            <input type="checkbox" wire:model="selectedClassrooms" value="{{ $cls->id }}" 
                                                   class="w-4 h-4 text-[#2347C4] rounded border-[#E2E4DF] focus:ring-[#2347C4]">
                                            <span class="text-sm font-semibold text-[#15191F]">{{ $cls->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('selectedClassrooms') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status & Jadwal Tayang -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                            <div>
                                <label class="block text-sm font-bold text-[#15191F] mb-2">
                                    Status Publikasi <span class="text-[#DC2626]">*</span>
                                </label>
                                <div class="flex flex-col gap-2.5">
                                    <label class="flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer {{ $status === 'draft' ? 'border-[#4A515C] bg-gray-50' : 'border-[#E2E4DF] bg-white' }}">
                                        <input type="radio" wire:model.live="status" value="draft" class="w-4 h-4 text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]">
                                        <div>
                                            <span class="text-sm font-bold text-[#15191F]">Draf</span>
                                            <span class="text-[11px] text-[#4A515C] block">Hanya guru yang bisa melihat</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer {{ $status === 'published' ? 'border-[#2E7D32] bg-[#E8F5E9]/40' : 'border-[#E2E4DF] bg-white' }}">
                                        <input type="radio" wire:model.live="status" value="published" class="w-4 h-4 text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]">
                                        <div>
                                            <span class="text-sm font-bold text-[#2E7D32]">Terbit Sekarang</span>
                                            <span class="text-[11px] text-[#4A515C] block">Langsung dapat diakses siswa</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer {{ $status === 'scheduled' ? 'border-[#B45309] bg-[#FEF3C7]/40' : 'border-[#E2E4DF] bg-white' }}">
                                        <input type="radio" wire:model.live="status" value="scheduled" class="w-4 h-4 text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]">
                                        <div>
                                            <span class="text-sm font-bold text-[#B45309]">Terjadwal</span>
                                            <span class="text-[11px] text-[#4A515C] block">Terbit otomatis pada waktu yang ditentukan</span>
                                        </div>
                                    </label>
                                </div>
                                @error('status') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if($status === 'scheduled')
                                <div class="md:col-span-2 bg-[#FFFBEB] p-4 rounded-xl border border-[#FDE68A]">
                                    <label class="block text-sm font-bold text-[#92400E] mb-1.5 flex items-center gap-1.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span>Waktu Tayang (Zona Waktu WIB) <span class="text-[#DC2626]">*</span></span>
                                    </label>
                                    <input type="datetime-local" wire:model="scheduled_at" 
                                           class="w-full rounded-xl border-[#FDE68A] bg-white text-[#15191F] text-sm focus:border-[#B45309] focus:ring-[#B45309] px-3.5 py-2.5">
                                    <p class="text-xs text-[#92400E] mt-1.5">
                                        Materi akan tersimpan dalam status Terjadwal dan otomatis dapat dibuka oleh siswa pada tanggal dan jam tersebut.
                                    </p>
                                    @error('scheduled_at') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. BAHAN BACAAN (PDF LITERASI) -->
                    <div class="bg-[#F8F9FA] p-6 rounded-2xl border border-[#E2E4DF] flex flex-col gap-6">
                        <div class="flex items-center justify-between border-b border-[#E2E4DF] pb-3">
                            <div>
                                <h2 class="text-base font-bold text-[#15191F] flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#2347C4] text-white text-xs flex items-center justify-center font-bold">3</span>
                                    <span>Bahan Bacaan Siswa (Dokumen PDF Literasi)</span>
                                </h2>
                                <p class="text-xs text-[#4A515C] mt-0.5">Unggah satu atau beberapa modul/materi dalam format PDF (maks. 20 MB per file). Disimpan aman di storage privat.</p>
                            </div>
                        </div>

                        <!-- Form Tambah PDF -->
                        <div class="bg-white p-4 rounded-xl border border-[#E2E4DF] flex flex-col gap-4">
                            <h3 class="text-sm font-bold text-[#15191F] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <span>Tambah Dokumen PDF Baru</span>
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-bold text-[#4A515C] mb-1">Judul / Keterangan Dokumen <span class="text-[#DC2626]">*</span></label>
                                    <input type="text" wire:model="newPdfLabel" placeholder="Contoh: Modul 1 - Konsep Dekomposisi" 
                                           class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                                    @error('newPdfLabel') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-5">
                                    <label class="block text-xs font-bold text-[#4A515C] mb-1">Pilih File PDF (Maks. 20 MB) <span class="text-[#DC2626]">*</span></label>
                                    <input type="file" wire:model="newPdfFile" accept="application/pdf"
                                           class="w-full text-xs text-[#4A515C] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#EEF2FF] file:text-[#2347C4] hover:file:bg-[#E0E7FF] file:cursor-pointer border border-[#E2E4DF] rounded-xl bg-white">
                                    @error('newPdfFile') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <button type="button" wire:click="addPdf" class="w-full py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        <span>Tambah PDF</span>
                                    </button>
                                </div>
                            </div>
                            <div wire:loading wire:target="newPdfFile" class="text-xs text-[#E0892A] font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>
                                <span>Mengunggah file PDF ke memori...</span>
                            </div>
                        </div>

                        <!-- Daftar PDF yang Sudah Ada / Pending -->
                        <div>
                            <h3 class="text-xs font-bold text-[#4A515C] uppercase tracking-wider mb-2">Daftar Bahan Bacaan PDF</h3>
                            @php
                                $totalPdfs = count($existingPdfs) + count($pendingPdfs);
                            @endphp

                            @if($totalPdfs === 0)
                                <div class="text-center py-6 bg-white rounded-xl border border-dashed border-[#E2E4DF]">
                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <p class="text-xs text-[#4A515C]">Belum ada bahan bacaan PDF untuk materi ini.</p>
                                </div>
                            @else
                                <div class="flex flex-col gap-2">
                                    {{-- Existing PDFs --}}
                                    @foreach($existingPdfs as $idx => $pdf)
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-[#E2E4DF] shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold text-xs shrink-0">
                                                    PDF
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-[#15191F]">{{ $pdf['label'] }}</h4>
                                                    <span class="text-[11px] text-[#4A515C]">
                                                        Urutan: {{ $pdf['sort_order'] ?? ($idx + 1) }} • {{ isset($pdf['file_size']) && $pdf['file_size'] ? round($pdf['file_size'] / 1024) . ' KB' : 'PDF' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                {{-- Reorder buttons --}}
                                                <button type="button" wire:click="movePdf({{ $idx }}, 'up', true)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === 0 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Naik" {{ $idx === 0 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                </button>
                                                <button type="button" wire:click="movePdf({{ $idx }}, 'down', true)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === count($existingPdfs) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Turun" {{ $idx === count($existingPdfs) - 1 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </button>
                                                {{-- Stream/View button --}}
                                                <a href="{{ route('materi.attachment.stream', [$pdf['lesson_id'] ?? $lessonId, $pdf['id']]) }}" target="_blank" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors" title="Buka PDF">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </a>
                                                {{-- Delete button --}}
                                                <button type="button" wire:confirm="Hapus bahan bacaan ini?" wire:click="removeExistingPdf({{ $pdf['id'] }})" class="p-1.5 text-[#4A515C] hover:text-[#DC2626] hover:bg-red-50 rounded-lg transition-colors" title="Hapus PDF">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Pending PDFs --}}
                                    @foreach($pendingPdfs as $idx => $pdf)
                                        <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs shrink-0">
                                                    BARU
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-[#15191F]">{{ $pdf['label'] }}</h4>
                                                    <span class="text-[11px] text-amber-700">
                                                        Akan disimpan saat materi disimpan • {{ isset($pdf['file_size']) && $pdf['file_size'] ? round($pdf['file_size'] / 1024) . ' KB' : 'PDF' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" wire:click="movePdf({{ $idx }}, 'up', false)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === 0 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Naik" {{ $idx === 0 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                </button>
                                                <button type="button" wire:click="movePdf({{ $idx }}, 'down', false)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === count($pendingPdfs) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Turun" {{ $idx === count($pendingPdfs) - 1 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </button>
                                                <button type="button" wire:click="removePendingPdf({{ $idx }})" class="p-1.5 text-[#4A515C] hover:text-[#DC2626] hover:bg-red-50 rounded-lg transition-colors" title="Batalkan">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 4. VIDEO PEMBELAJARAN (YOUTUBE) -->
                    <div class="bg-[#F8F9FA] p-6 rounded-2xl border border-[#E2E4DF] flex flex-col gap-6">
                        <div class="flex items-center justify-between border-b border-[#E2E4DF] pb-3">
                            <div>
                                <h2 class="text-base font-bold text-[#15191F] flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#2347C4] text-white text-xs flex items-center justify-center font-bold">4</span>
                                    <span>Video Pembelajaran (YouTube)</span>
                                </h2>
                                <p class="text-xs text-[#4A515C] mt-0.5">Tautkan video YouTube yang relevan (mendukung format youtube.com/watch, youtu.be, atau Shorts). Diputar langsung di LMS secara aman.</p>
                            </div>
                        </div>

                        <!-- Form Tambah Video -->
                        <div class="bg-white p-4 rounded-xl border border-[#E2E4DF] flex flex-col gap-4">
                            <h3 class="text-sm font-bold text-[#15191F] flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
                                <span>Tambah Tautan Video YouTube</span>
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-bold text-[#4A515C] mb-1">Judul Video <span class="text-[#DC2626]">*</span></label>
                                    <input type="text" wire:model="newVideoTitle" placeholder="Contoh: Video Penjelasan Dekomposisi Berpikir Komputasional" 
                                           class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                                    @error('newVideoTitle') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-5">
                                    <label class="block text-xs font-bold text-[#4A515C] mb-1">Tautan YouTube (URL) <span class="text-[#DC2626]">*</span></label>
                                    <input type="url" wire:model.live.debounce.400ms="newVideoUrl" placeholder="https://www.youtube.com/watch?v=... atau https://youtu.be/..." 
                                           class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                                    @error('newVideoUrl') <span class="text-xs text-[#DC2626] font-medium mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <button type="button" wire:click="addVideo" class="w-full py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                        <span>Tambah Video</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Live Video Preview --}}
                            @if($newVideoPreviewId)
                                <div class="mt-2 p-3 bg-[#F8F9FA] rounded-xl border border-[#E2E4DF] flex flex-col sm:flex-row items-center gap-4">
                                    <div class="w-full sm:w-44 aspect-video bg-black rounded-lg overflow-hidden shrink-0 shadow-sm">
                                        <img src="https://img.youtube.com/vi/{{ $newVideoPreviewId }}/mqdefault.jpg" alt="Preview Video" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 text-center sm:text-left">
                                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-bold rounded-full mb-1">Tautan YouTube Terverifikasi</span>
                                        <p class="text-xs text-[#15191F] font-bold">ID Video: <span class="font-mono text-[#2347C4]">{{ $newVideoPreviewId }}</span></p>
                                        <p class="text-[11px] text-[#4A515C] mt-0.5">Preview thumbnail terdeteksi otomatis. Klik tombol "Tambah Video" untuk memasukkannya ke daftar materi.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Daftar Video -->
                        <div>
                            <h3 class="text-xs font-bold text-[#4A515C] uppercase tracking-wider mb-2">Daftar Video Pembelajaran</h3>
                            @php
                                $totalVideos = count($existingVideos) + count($pendingVideos);
                            @endphp

                            @if($totalVideos === 0)
                                <div class="text-center py-6 bg-white rounded-xl border border-dashed border-[#E2E4DF]">
                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                                    <p class="text-xs text-[#4A515C]">Belum ada video YouTube untuk materi ini.</p>
                                </div>
                            @else
                                <div class="flex flex-col gap-2">
                                    {{-- Existing Videos --}}
                                    @foreach($existingVideos as $idx => $vid)
                                        <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-[#E2E4DF] shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-8 rounded bg-black overflow-hidden shrink-0">
                                                    <img src="https://img.youtube.com/vi/{{ $vid['file_path'] }}/mqdefault.jpg" alt="Thumb" class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-[#15191F]">{{ $vid['label'] }}</h4>
                                                    <span class="text-[11px] text-[#4A515C]">
                                                        Urutan: {{ $vid['sort_order'] ?? ($idx + 1) }} • ID: {{ $vid['file_path'] }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" wire:click="moveVideo({{ $idx }}, 'up', true)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === 0 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Naik" {{ $idx === 0 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                </button>
                                                <button type="button" wire:click="moveVideo({{ $idx }}, 'down', true)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === count($existingVideos) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Turun" {{ $idx === count($existingVideos) - 1 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </button>
                                                <a href="https://www.youtube.com/watch?v={{ $vid['file_path'] }}" target="_blank" class="p-1.5 text-[#4A515C] hover:text-red-600 hover:bg-[#F4F5F2] rounded-lg transition-colors" title="Tonton di YouTube">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                                </a>
                                                <button type="button" wire:confirm="Hapus video ini?" wire:click="removeExistingVideo({{ $vid['id'] }})" class="p-1.5 text-[#4A515C] hover:text-[#DC2626] hover:bg-red-50 rounded-lg transition-colors" title="Hapus Video">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Pending Videos --}}
                                    @foreach($pendingVideos as $idx => $vid)
                                        <div class="flex items-center justify-between p-3 bg-amber-50/50 rounded-xl border border-amber-200 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-8 rounded bg-black overflow-hidden shrink-0">
                                                    <img src="https://img.youtube.com/vi/{{ $vid['file_path'] }}/mqdefault.jpg" alt="Thumb" class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-[#15191F]">{{ $vid['label'] }}</h4>
                                                    <span class="text-[11px] text-amber-700">
                                                        Akan disimpan saat materi disimpan • ID: {{ $vid['file_path'] }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" wire:click="moveVideo({{ $idx }}, 'up', false)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === 0 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Naik" {{ $idx === 0 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                </button>
                                                <button type="button" wire:click="moveVideo({{ $idx }}, 'down', false)" class="p-1.5 text-[#4A515C] hover:text-[#2347C4] hover:bg-[#F4F5F2] rounded-lg transition-colors {{ $idx === count($pendingVideos) - 1 ? 'opacity-30 cursor-not-allowed' : '' }}" title="Geser Turun" {{ $idx === count($pendingVideos) - 1 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </button>
                                                <button type="button" wire:click="removePendingVideo({{ $idx }})" class="p-1.5 text-[#4A515C] hover:text-[#DC2626] hover:bg-red-50 rounded-lg transition-colors" title="Batalkan">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 5. TUGAS UNTUK MATERI INI -->
                    <div class="bg-[#F8F9FA] p-6 rounded-2xl border border-[#E2E4DF] flex flex-col gap-6">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-[#E2E4DF] pb-3">
                            <div>
                                <h2 class="text-base font-bold text-[#15191F] flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#2347C4] text-white text-xs flex items-center justify-center font-bold">5</span>
                                    <span>Tugas & Penugasan Siswa</span>
                                </h2>
                                <p class="text-xs text-[#4A515C] mt-0.5">Hubungkan tugas untuk materi ini (siswa mengumpulkan file PDF, Word, PPT, link, dsb). Nilai tugas otomatis masuk ke Buku Nilai.</p>
                            </div>
                            
                            @if($lessonId)
                                <a href="{{ route('guru.tugas.create', ['lesson_id' => $lessonId]) }}" target="_blank" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors flex items-center gap-1.5 shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    <span>+ Buat Tugas Baru</span>
                                </a>
                            @endif
                        </div>

                        <!-- Pilih dari Tugas yang Sudah Ada -->
                        <div>
                            <label class="block text-xs font-bold text-[#4A515C] uppercase tracking-wider mb-2">
                                Hubungkan Tugas yang Sudah Dibuat
                            </label>
                            @if($availableTasksForModal->isEmpty())
                                <div class="text-center py-6 bg-white rounded-xl border border-dashed border-[#E2E4DF]">
                                    <p class="text-xs text-[#4A515C]">Belum ada data tugas pada sistem.</p>
                                    <a href="{{ route('guru.tugas.create', $lessonId ? ['lesson_id' => $lessonId] : []) }}" target="_blank" class="inline-block mt-2 text-xs font-bold text-[#2347C4] hover:underline">
                                        + Buat Tugas di Menu Tugas
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto p-1">
                                    @foreach($availableTasksForModal as $task)
                                        @php
                                            $isSelected = in_array($task->id, $selectedTaskIds);
                                        @endphp
                                        <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors {{ $isSelected ? 'border-[#2347C4] bg-[#EEF2FF]' : 'border-[#E2E4DF] bg-white hover:bg-[#F8F9FA]' }}">
                                            <input type="checkbox" wire:model="selectedTaskIds" value="{{ $task->id }}" class="w-4 h-4 text-[#2347C4] rounded border-[#E2E4DF] focus:ring-[#2347C4] mt-0.5">
                                            <div class="flex-1 min-w-0">
                                                <span class="text-sm font-bold text-[#15191F] block truncate">{{ $task->title }}</span>
                                                <span class="text-[11px] text-[#4A515C] block">
                                                    {{ $task->due_at ? 'Tenggat: ' . \Carbon\Carbon::parse($task->due_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : 'Tanpa tenggat' }}
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Aksi Bawah -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E2E4DF]">
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-bold text-sm hover:bg-[#F4F5F2] transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="save" class="px-6 py-2.5 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] shadow-md transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            <span>Simpan Materi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <!-- ==================== TAMPILAN DAFTAR MATERI (GURU LIST VIEW - MASTER DETAIL) ==================== -->
        <div class="bg-white rounded-2xl border border-[#E2E4DF] shadow-sm p-6 md:p-8">
            <!-- Header Page -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-[#15191F]">Materi Pembelajaran</h1>
                    <p class="text-sm text-[#4A515C] mt-1">
                        Kelola alur materi per Elemen dan Tujuan Pembelajaran (ATP). Siswa membaca materi PDF literasi, menonton video YouTube, dan mengerjakan tugas.
                    </p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('guru.tp') }}" class="px-4 py-2.5 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-xl font-bold text-sm hover:bg-[#F4F5F2] hover:text-[#15191F] transition-colors shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        <span>Kelola ATP</span>
                    </a>
                    <button type="button" wire:click="create" class="px-5 py-2.5 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] transition-colors shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Buat Subbab</span>
                    </button>
                </div>
            </div>

            <!-- Toolbar (Filter & Search & Status Tabs) -->
            <div class="flex flex-col gap-4 mb-6">
                <!-- Dropdown & Search & Toggle Bar -->
                <div class="p-3 bg-[#F8F9FA] rounded-2xl border border-[#E2E4DF] shadow-2xs">
                    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                        <!-- Left Controls: Filter Elemen, Filter Kelas, & Search Bar -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1 min-w-0">
                            <!-- Filter Elemen -->
                            <div class="relative w-full sm:w-56 lg:w-60 flex-none">
                                <select wire:model.live="filterElement" 
                                        class="w-full text-xs font-semibold text-[#15191F] rounded-xl pl-3.5 pr-8 transition-all cursor-pointer shadow-xs focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 appearance-none"
                                        style="height: 42px; border: 1px solid #E2E4DF; background-color: #FFFFFF; color: #15191F;">
                                    <option value="">Semua Elemen</option>
                                    @foreach($availableElements as $el)
                                        <option value="{{ $el->id }}">{{ $el->code ? $el->code . ' - ' : '' }}{{ $el->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#8C95A3] flex items-center">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <!-- Filter Kelas -->
                            <div class="relative w-full sm:w-44 lg:w-48 flex-none">
                                <select wire:model.live="filterClass" 
                                        class="w-full text-xs font-semibold text-[#15191F] rounded-xl pl-3.5 pr-8 transition-all cursor-pointer shadow-xs focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 appearance-none"
                                        style="height: 42px; border: 1px solid #E2E4DF; background-color: #FFFFFF; color: #15191F;">
                                    <option value="">Semua Kelas</option>
                                    @foreach($myClassrooms as $cls)
                                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#8C95A3] flex items-center">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>

                            <!-- Search Input (Bulletproof flex label container with perfectly centered icon) -->
                            <label class="flex-1 min-w-[200px] w-full flex items-center gap-2.5 px-3.5 rounded-xl transition-all cursor-text shadow-xs focus-within:ring-2 focus-within:ring-[#2347C4]/20"
                                   style="height: 42px; border: 1px solid #E2E4DF; background-color: #FFFFFF; box-sizing: border-box;">
                                <svg class="w-4 h-4 flex-none text-[#8C95A3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <input type="text" 
                                       wire:model.live.debounce.300ms="search" 
                                       placeholder="Cari ATP / subbab / materi..." 
                                       class="flex-1 min-w-0 border-0 outline-none bg-transparent text-xs font-medium text-[#15191F] placeholder:text-[#8C95A3] p-0 focus:ring-0 focus:outline-none"
                                       style="border: none; outline: none; background: transparent; padding: 0; box-shadow: none;">
                                @if($search)
                                    <button type="button" 
                                            wire:click="$set('search', '')" 
                                            class="text-sm font-bold text-[#8C95A3] hover:text-[#15191F] p-0.5 rounded cursor-pointer leading-none"
                                            title="Hapus pencarian">
                                        &times;
                                    </button>
                                @endif
                            </label>
                        </div>

                        <!-- Right Controls: Toggle Hanya ATP Bermateri -->
                        <div class="flex items-center gap-2 flex-none">
                            <label class="h-[42px] px-3.5 inline-flex items-center gap-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer select-none shadow-xs flex-none"
                                   style="height: 42px; border: 1px solid {{ $onlyWithLessons ? '#BFDBFE' : '#E2E4DF' }}; background: {{ $onlyWithLessons ? '#EEF2FF' : '#FFFFFF' }}; color: {{ $onlyWithLessons ? '#2347C4' : '#15191F' }};">
                                <input type="checkbox" 
                                       wire:model.live="onlyWithLessons" 
                                       class="w-4 h-4 rounded text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]">
                                <span class="whitespace-nowrap">Hanya ATP bermateri</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Status Tabs -->
                <div class="flex items-center gap-2 border-b border-[#E2E4DF] pt-1 overflow-x-auto">
                    <button type="button" wire:click="setTab('semua')" 
                            class="pb-3 px-3 text-xs md:text-sm font-bold flex items-center gap-2 transition-all whitespace-nowrap cursor-pointer"
                            style="{{ $activeTab === 'semua' ? 'color: #2347C4; border-bottom: 2px solid #2347C4;' : 'color: #4A515C;' }}">
                        <span>Semua Subbab</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                              style="{{ $activeTab === 'semua' ? 'background: #EEF2FF; color: #2347C4;' : 'background: #F4F5F2; color: #4A515C;' }}">
                            {{ $statusCounts['semua'] }}
                        </span>
                    </button>
                    <button type="button" wire:click="setTab('terbit')" 
                            class="pb-3 px-3 text-xs md:text-sm font-bold flex items-center gap-2 transition-all whitespace-nowrap cursor-pointer"
                            style="{{ $activeTab === 'terbit' ? 'color: #059669; border-bottom: 2px solid #059669;' : 'color: #4A515C;' }}">
                        <span>Terbit</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                              style="{{ $activeTab === 'terbit' ? 'background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0;' : 'background: #F4F5F2; color: #4A515C;' }}">
                            {{ $statusCounts['terbit'] }}
                        </span>
                    </button>
                    <button type="button" wire:click="setTab('terjadwal')" 
                            class="pb-3 px-3 text-xs md:text-sm font-bold flex items-center gap-2 transition-all whitespace-nowrap cursor-pointer"
                            style="{{ $activeTab === 'terjadwal' ? 'color: #D97706; border-bottom: 2px solid #D97706;' : 'color: #4A515C;' }}">
                        <span>Terjadwal</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                              style="{{ $activeTab === 'terjadwal' ? 'background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;' : 'background: #F4F5F2; color: #4A515C;' }}">
                            {{ $statusCounts['terjadwal'] }}
                        </span>
                    </button>
                    <button type="button" wire:click="setTab('draf')" 
                            class="pb-3 px-3 text-xs md:text-sm font-bold flex items-center gap-2 transition-all whitespace-nowrap cursor-pointer"
                            style="{{ $activeTab === 'draf' ? 'color: #15191F; border-bottom: 2px solid #15191F;' : 'color: #4A515C;' }}">
                        <span>Draf</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                              style="{{ $activeTab === 'draf' ? 'background: #E5E7EB; color: #1F2937;' : 'background: #F4F5F2; color: #4A515C;' }}">
                            {{ $statusCounts['draf'] }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Mobile ATP Selector -->
            <div class="lg:hidden mb-6 flex flex-col gap-2">
                <label class="text-xs font-bold text-[#4A515C] uppercase tracking-wider">Pilih Tujuan Pembelajaran (ATP):</label>
                <div class="relative w-full">
                    <select wire:model.live="selectedTpId" 
                            class="w-full rounded-xl pl-3.5 pr-8 py-3 text-[#15191F] text-sm font-semibold focus:border-[#2347C4] focus:ring-2 focus:ring-[#2347C4]/20 shadow-xs appearance-none"
                            style="border: 1px solid #E2E4DF; background-color: #FFFFFF;">
                        @foreach($groupedData as $group)
                            <optgroup label="{{ $group['element']->code }} - {{ $group['element']->name }}">
                                @foreach($group['tps'] as $tpItem)
                                    @php
                                        $mDisplayCount = ($activeTab === 'semua') ? $tpItem['raw_count'] : ($tpItem['tab_count'] ?? $tpItem['raw_count']);
                                    @endphp
                                    <option value="{{ $tpItem['tp']->id }}">
                                        {{ $tpItem['tp']->code }} - {{ $tpItem['tp']->title }} ({{ $mDisplayCount }} subbab{{ $activeTab !== 'semua' ? ' ' . $activeTab : '' }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-[#8C95A3] flex items-center">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Master-Detail 2-Panel Layout (Desktop) -->
            <div class="flex flex-col lg:flex-row gap-6 items-start">
                
                <!-- Panel Kiri: Daftar ATP per Elemen -->
                <div class="hidden lg:flex w-80 xl:w-96 shrink-0 bg-[#F8F9FA] rounded-2xl border border-[#E2E4DF] overflow-hidden flex-col sticky top-6 max-h-[calc(100vh-140px)] shadow-xs">
                    <div class="px-4 py-3 bg-white border-b border-[#E2E4DF] flex items-center justify-between shrink-0">
                        <span class="text-xs font-bold text-[#15191F] uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"></path></svg>
                            <span>Daftar ATP / TP</span>
                        </span>
                        <span class="text-[11px] font-semibold text-[#4A515C] bg-gray-100 px-2 py-0.5 rounded-full">
                            {{ array_sum(array_map(fn($g) => count($g['tps']), $groupedData)) }} ATP
                        </span>
                    </div>

                    <!-- Scrollable list of TPs -->
                    <div class="overflow-y-auto flex-1 divide-y divide-[#E2E4DF] p-2 space-y-3">
                        @forelse($groupedData as $group)
                            <div class="pt-2 first:pt-0">
                                <!-- Element Header -->
                                <div class="px-2 py-1.5 flex items-center gap-2 text-xs font-bold text-[#4A515C] uppercase tracking-wider">
                                    <span class="px-1.5 py-0.5 rounded bg-white border border-[#E2E4DF] text-[#2347C4] text-[10px] font-mono font-bold">{{ $group['element']->code }}</span>
                                    <span class="truncate">{{ $group['element']->name }}</span>
                                </div>

                                <!-- TPs in this element -->
                                <div class="mt-1 space-y-1">
                                    @foreach($group['tps'] as $tpItem)
                                        @php
                                            $tp = $tpItem['tp'];
                                            $rawCount = $tpItem['raw_count'];
                                            $tabCount = $tpItem['tab_count'] ?? $rawCount;
                                            $displayCount = ($activeTab === 'semua') ? $rawCount : $tabCount;
                                            $isActive = ($selectedTpId === $tp->id);
                                            $hasMatch = $tpItem['has_match'];
                                        @endphp

                                        <button type="button" 
                                                wire:click="selectTp({{ $tp->id }})" 
                                                class="w-full text-left p-2.5 rounded-xl transition-all flex items-center justify-between gap-2 border {{ $isActive ? 'bg-white border-[#2347C4] shadow-sm text-[#2347C4]' : 'bg-transparent border-transparent hover:bg-white hover:border-[#E2E4DF] text-[#15191F]' }}">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="font-mono text-xs font-bold px-1.5 py-0.5 rounded shrink-0 {{ $isActive ? 'bg-[#EEF2FF] text-[#2347C4]' : 'bg-white text-zinc-700 border border-[#E2E4DF]' }}">
                                                    {{ $tp->code }}
                                                </span>
                                                <span class="text-xs font-semibold truncate {{ $isActive ? 'text-[#2347C4] font-bold' : 'text-[#15191F]' }}" title="{{ $tp->title }}">
                                                    {{ $tp->title }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                @if($hasMatch && !empty($search))
                                                    <span class="w-2 h-2 rounded-full bg-amber-500" title="Cocok dengan pencarian"></span>
                                                @endif
                                                @if($activeTab === 'terbit')
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $displayCount > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-400' }}">
                                                        {{ $displayCount }}
                                                    </span>
                                                @elseif($activeTab === 'terjadwal')
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $displayCount > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-400' }}">
                                                        {{ $displayCount }}
                                                    </span>
                                                @elseif($activeTab === 'draf')
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $displayCount > 0 ? 'bg-gray-200 text-gray-800' : 'bg-gray-100 text-gray-400' }}">
                                                        {{ $displayCount }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $displayCount > 0 ? ($isActive ? 'bg-[#2347C4] text-white' : 'bg-[#EBF3FF] text-[#2347C4]') : 'bg-gray-100 text-gray-500' }}">
                                                        {{ $displayCount }}
                                                    </span>
                                                @endif
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-xs text-[#4A515C]">Tidak ada ATP yang sesuai filter.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Panel Kanan: Konten ATP Terpilih & Grid Subbab -->
                <div class="flex-1 min-w-0 w-full">
                    @if($selectedTp)
                        <!-- Header ATP Terpilih -->
                        <div class="bg-white rounded-2xl border border-[#E2E4DF] p-5 shadow-xs mb-5">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 bg-[#EEF2FF] text-[#2347C4] font-mono text-xs font-bold rounded-lg border border-[#C7D2FE]">
                                        {{ $selectedTp->element?->code }} · {{ $selectedTp->element?->name }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 font-mono text-xs font-bold rounded">
                                        {{ $selectedTp->code }}
                                    </span>
                                    @if($selectedTp->kktp)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 text-[11px] font-semibold rounded border border-emerald-200">
                                            KKTP: {{ $selectedTp->kktp }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Single '+ Buat subbab' button (no double plus) -->
                                <button type="button" 
                                        wire:click="createForTp({{ $selectedTp->id }})" 
                                        class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors flex items-center gap-1.5 shrink-0" 
                                        title="Buat subbab materi baru untuk ATP ini">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    <span>Buat subbab</span>
                                </button>
                            </div>

                            <h2 class="text-lg font-bold text-[#15191F] mt-1">{{ $selectedTp->title }}</h2>
                            @if($selectedTp->description)
                                <p class="text-xs text-[#4A515C] mt-1 leading-relaxed">{{ $selectedTp->description }}</p>
                            @endif
                        </div>

                        <!-- Grid Kartu Subbab Ringkas (2 Kolom di Desktop, 1 Kolom di Mobile) -->
                        @if($selectedLessons->isEmpty())
                            @php
                                $otherTpWithLesson = null;
                                if ($activeTab !== 'semua') {
                                    foreach ($groupedData as $grp) {
                                        foreach ($grp['tps'] as $it) {
                                            if ($it['tp']->id !== $selectedTp->id && ($it['tab_count'] ?? 0) > 0) {
                                                $otherTpWithLesson = $it['tp'];
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <div class="text-center py-12 px-6 bg-[#F8F9FA] rounded-2xl border border-dashed border-[#E2E4DF]">
                                <div class="w-12 h-12 rounded-xl {{ $activeTab === 'terbit' ? 'bg-emerald-50 text-emerald-600' : ($activeTab === 'terjadwal' ? 'bg-amber-50 text-amber-600' : 'bg-[#EEF2FF] text-[#2347C4]') }} flex items-center justify-center mx-auto mb-3 shadow-xs">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                </div>
                                @if($activeTab !== 'semua')
                                    <h3 class="text-sm font-bold text-[#15191F] mb-1">
                                        Tidak Ada Subbab berstatus "{{ ucfirst($activeTab) }}" pada ATP Ini
                                    </h3>
                                    @if($otherTpWithLesson)
                                        <p class="text-xs text-[#4A515C] max-w-md mx-auto mb-4 leading-relaxed">
                                            ATP ini belum memiliki subbab materi yang berstatus <strong class="capitalize">{{ $activeTab }}</strong>. 
                                            Materi berstatus ini tersedia pada ATP <strong class="text-[#15191F]">{{ $otherTpWithLesson->code }} — {{ $otherTpWithLesson->title }}</strong>.
                                        </p>
                                        <div class="flex flex-wrap items-center justify-center gap-2">
                                            <button type="button" wire:click="selectTp({{ $otherTpWithLesson->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer">
                                                <span>Buka ATP {{ $otherTpWithLesson->code }}</span>
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            </button>
                                            <button type="button" wire:click="setTab('semua')" class="px-4 py-2 bg-white border border-[#E2E4DF] text-[#15191F] rounded-xl font-bold text-xs hover:bg-gray-50 shadow-xs transition-colors cursor-pointer">
                                                Lihat Semua Subbab di ATP Ini
                                            </button>
                                            <button type="button" wire:click="createForTp({{ $selectedTp->id }})" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors cursor-pointer">
                                                + Buat Subbab Baru
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-xs text-[#4A515C] max-w-sm mx-auto mb-4">
                                            Belum ada subbab materi yang berstatus {{ $activeTab }} pada ATP ini.
                                        </p>
                                        <div class="flex flex-wrap items-center justify-center gap-2">
                                            <button type="button" wire:click="setTab('semua')" class="px-4 py-2 bg-white border border-[#E2E4DF] text-[#15191F] rounded-xl font-bold text-xs hover:bg-gray-50 shadow-xs transition-colors cursor-pointer">
                                                Lihat Semua Subbab di ATP Ini
                                            </button>
                                            <button type="button" wire:click="createForTp({{ $selectedTp->id }})" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors cursor-pointer">
                                                + Buat Subbab Baru
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <h3 class="text-sm font-bold text-[#15191F] mb-1">Belum Ada Subbab pada ATP Ini</h3>
                                    <p class="text-xs text-[#4A515C] max-w-sm mx-auto mb-4">
                                        Mulai tambahkan subbab materi, modul bacaan PDF, video YouTube, dan tugas untuk siswa.
                                    </p>
                                    <button type="button" wire:click="createForTp({{ $selectedTp->id }})" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors cursor-pointer">
                                        Buat Subbab Pertama
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($selectedLessons as $lesson)
                                    <div class="bg-white rounded-xl border border-[#E2E4DF] p-4 shadow-xs hover:shadow-md hover:border-[#2347C4]/30 transition-all flex flex-col justify-between min-w-0">
                                        <!-- Top: Code & Status -->
                                        <div>
                                            <div class="flex items-center justify-between gap-2 mb-2">
                                                <span class="font-mono text-xs font-bold text-zinc-700 bg-zinc-100 px-2 py-0.5 rounded border border-zinc-200">
                                                    {{ $lesson->code }}
                                                </span>

                                                @if($lesson->status === 'published')
                                                    <span class="px-2 py-0.5 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] text-[10px] font-bold rounded-full">
                                                        Terbit
                                                    </span>
                                                @elseif($lesson->status === 'scheduled')
                                                    <span class="px-2 py-0.5 bg-[#FEF3C7] text-[#B45309] border border-[#FDE68A] text-[10px] font-bold rounded-full flex items-center gap-1" title="Tayang: {{ \Carbon\Carbon::parse($lesson->scheduled_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB">
                                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                        <span>{{ \Carbon\Carbon::parse($lesson->scheduled_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M, H:i') }}</span>
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 border border-gray-200 text-[10px] font-bold rounded-full">
                                                        Draf
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Title (Max 2 lines) -->
                                            <a href="{{ route('guru.lessons.preview', $lesson->id) }}" class="group block" title="Buka Materi, Alur Belajar & Diskusi">
                                                <h4 class="font-bold text-[#15191F] text-sm line-clamp-2 leading-snug group-hover:text-[#2347C4] transition-colors">
                                                    {{ $lesson->title }}
                                                </h4>
                                            </a>

                                            <!-- Summary (1 line) -->
                                            @if($lesson->summary)
                                                <p class="text-xs text-[#4A515C] line-clamp-1 mt-1">{{ $lesson->summary }}</p>
                                            @endif
                                        </div>

                                        <!-- Middle: Single-line summary badges -->
                                        <div class="mt-3 pt-2.5 border-t border-[#F0F2ED] space-y-2">
                                            <!-- Metrik Konten -->
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <!-- PDF -->
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded text-[11px] font-semibold whitespace-nowrap shrink-0" title="Bahan Bacaan PDF">
                                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                                    <span>{{ $lesson->pdf_count }} PDF</span>
                                                </span>

                                                <!-- Video -->
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[11px] font-semibold whitespace-nowrap shrink-0" title="Video Pembelajaran">
                                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                                                    <span>{{ $lesson->video_count }} Video</span>
                                                </span>

                                                <!-- Tugas -->
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded text-[11px] font-semibold whitespace-nowrap shrink-0" title="Tugas Terhubung">
                                                    <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                                    <span>{{ $lesson->tugas_count }} Tugas</span>
                                                </span>

                                                <!-- Diskusi -->
                                                @if(!empty($lesson->discussions_count) && $lesson->discussions_count > 0)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded text-[11px] font-semibold whitespace-nowrap shrink-0" title="Diskusi Kelas Aktif">
                                                        <svg class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                                        <span>{{ $lesson->discussions_count }} Diskusi</span>
                                                    </span>
                                                @endif

                                                <!-- Menit -->
                                                @if($lesson->estimated_minutes)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-zinc-100 text-zinc-700 rounded text-[11px] font-medium whitespace-nowrap shrink-0">
                                                        <span>{{ $lesson->estimated_minutes }} mnt</span>
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Target Kelas -->
                                            @if($lesson->classrooms && $lesson->classrooms->count() > 0)
                                                <div class="flex flex-wrap items-center gap-1 pt-0.5">
                                                    <span class="text-[10px] text-zinc-400 font-medium mr-0.5">Kelas:</span>
                                                    @foreach($lesson->classrooms as $cls)
                                                        <span class="px-1.5 py-0.5 bg-zinc-50 border border-zinc-200 text-zinc-600 rounded text-[10px] font-medium whitespace-nowrap">
                                                            {{ $cls->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                            <!-- Bottom: Main Actions & Three-Dot Dropdown -->
                                            <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-[#F0F2ED]">
                                                <!-- Main Visible Actions -->
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('guru.lessons.preview', $lesson->id) }}" 
                                                       class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors" 
                                                       title="Buka Alur Belajar & Diskusi Kelas">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                                        <span>Buka</span>
                                                    </a>

                                                    <button type="button" 
                                                            wire:click="edit({{ $lesson->id }})" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-[#2347C4] bg-[#EEF2FF] hover:bg-[#E0E7FF] rounded-lg transition-colors" 
                                                            title="Ubah Materi">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                        <span>Ubah</span>
                                                    </button>

                                                    <button type="button" 
                                                            wire:click="openProgressModal({{ $lesson->id }})" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors" 
                                                            title="Lihat Progres Literasi & Tugas Siswa">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                                                        <span>Progres</span>
                                                    </button>
                                                </div>

                                                <!-- Three-Dot Dropdown Menu (Alpine.js) -->
                                                <div x-data="{ open: false }" class="relative" :class="{ 'z-40': open }">
                                                    <button @click="open = !open" 
                                                            @click.outside="open = false" 
                                                            type="button" 
                                                            class="p-1.5 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-lg transition-colors cursor-pointer" 
                                                            title="Menu Lainnya">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"></circle><circle cx="12" cy="12" r="1.5"></circle><circle cx="12" cy="19" r="1.5"></circle></svg>
                                                    </button>

                                                    <div x-show="open" 
                                                         x-cloak 
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-75"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         class="absolute right-0 bottom-full mb-1.5 w-48 bg-white rounded-xl shadow-xl border border-[#E2E4DF] py-1.5 z-50">
                                                        <!-- Buka Materi & Diskusi -->
                                                        <a href="{{ route('guru.lessons.preview', $lesson->id) }}" 
                                                           class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-[#EEF2FF] hover:text-[#2347C4] transition-colors">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                                            <span>Buka Materi & Diskusi</span>
                                                        </a>

                                                        <!-- Duplikat -->
                                                        <button type="button" 
                                                                wire:click="duplicate({{ $lesson->id }})" 
                                                                @click="open = false" 
                                                                class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-[#EEF2FF] hover:text-[#2347C4] transition-colors text-left">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                            <span>Duplikat</span>
                                                        </button>

                                                        <div class="border-t border-zinc-100 my-1"></div>

                                                        <!-- Hapus -->
                                                        <button type="button" 
                                                                wire:confirm="Yakin ingin menghapus materi '{{ $lesson->title }}'? Seluruh file PDF dan progres siswa terkait juga akan dihapus." 
                                                                wire:click="delete({{ $lesson->id }})" 
                                                                @click="open = false" 
                                                                class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                            <span>Hapus</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-center py-16 bg-[#F8F9FA] rounded-2xl border border-dashed border-[#E2E4DF]">
                            <div class="w-16 h-16 rounded-2xl bg-[#EEF2FF] text-[#2347C4] flex items-center justify-center mx-auto mb-4 shadow-xs">
                                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-[#15191F] mb-1">Pilih Tujuan Pembelajaran</h3>
                            <p class="text-sm text-[#4A515C] max-w-md mx-auto mb-6">
                                Silakan pilih Tujuan Pembelajaran (ATP) pada panel kiri untuk melihat dan mengelola subbab materi terkait.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== MODAL TAMBAH ATP CEPAT ==================== -->
    <div x-data="{ show: @entangle('isAtpModalOpen').live }" 
         x-show="show" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-[#E2E4DF] flex flex-col gap-6">
            
            <div class="flex items-center justify-between border-b border-[#E2E4DF] pb-3">
                <h3 class="text-lg font-bold text-[#15191F] flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    <span>Tambah ATP / TP Cepat</span>
                </h3>
                <button type="button" wire:click="closeQuickAtpModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#15191F] mb-1">Elemen Pembelajaran <span class="text-rose-500">*</span></label>
                    <select wire:model="atpElementId" class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                        @foreach($availableElements as $el)
                            <option value="{{ $el->id }}">{{ $el->code ? $el->code . ' - ' : '' }}{{ $el->name }}</option>
                        @endforeach
                    </select>
                    @error('atpElementId') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#15191F] mb-1">Kode ATP <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="atpCode" placeholder="Contoh: BK-6" class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] font-mono focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                        @error('atpCode') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#15191F] mb-1">KKTP (Opsional)</label>
                        <input type="number" wire:model="atpKktp" min="0" max="100" placeholder="75" class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                        @error('atpKktp') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#15191F] mb-1">Judul / Capaian ATP <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="atpTitle" placeholder="Contoh: Memahami Rekursi dan Algoritma Greedy" class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2">
                    @error('atpTitle') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#15191F] mb-1">Deskripsi Tambahan (Opsional)</label>
                    <textarea wire:model="atpDescription" rows="2" placeholder="Penjelasan atau indikator pencapaian kompetensi..." class="w-full text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] px-3.5 py-2"></textarea>
                    @error('atpDescription') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E2E4DF]">
                <button type="button" wire:click="closeQuickAtpModal" class="px-4 py-2 border border-[#E2E4DF] text-[#4A515C] rounded-xl font-bold text-xs hover:bg-[#F4F5F2] transition-colors">
                    Batal
                </button>
                <button type="button" wire:click="saveQuickAtp" class="px-5 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-xs hover:bg-[#1A3596] shadow-sm transition-colors">
                    Simpan & Pilih ATP
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL REKAP PROGRES SISWA ==================== -->
    <div x-data="{ show: @entangle('isProgressModalOpen').live }" 
         x-show="show" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        
        <div x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-xl border border-[#E2E4DF] flex flex-col gap-6 max-h-[90vh] overflow-hidden">
            
            <div class="flex items-center justify-between border-b border-[#E2E4DF] pb-3 shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-[#15191F] flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                        <span>Rekap Progres Literasi & Belajar Siswa</span>
                    </h3>
                    @if($progressLesson)
                        <p class="text-xs text-[#4A515C] mt-0.5">
                            Materi: <strong class="text-[#15191F]">{{ $progressLesson->code }} - {{ $progressLesson->title }}</strong>
                        </p>
                    @endif
                </div>
                <button type="button" wire:click="closeProgressModal" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <!-- Toolbar Filter Kelas & Pencarian -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0 bg-[#F8F9FA] p-3 rounded-xl border border-[#E2E4DF]">
                <!-- Tombol Pill Filter Kelas -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-bold text-[#4A515C] mr-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                        Filter Kelas:
                    </span>
                    <button type="button" 
                            wire:click="$set('progressClassFilter', null)" 
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $progressClassFilter === null ? 'bg-[#2347C4] text-white shadow-xs' : 'bg-white text-[#4A515C] border border-[#E2E4DF] hover:bg-gray-100' }}"
                            style="{{ $progressClassFilter === null ? 'background-color: #2347C4; color: #FFFFFF !important;' : '' }}">
                        Semua Kelas ({{ count($progressStudentList) }})
                    </button>
                    @if($progressLesson)
                        @foreach($progressLesson->classrooms as $cls)
                            @php
                                $clsCount = collect($progressStudentList)->where('classroom_id', $cls->id)->count();
                                $isSelected = (int)$progressClassFilter === (int)$cls->id;
                            @endphp
                            <button type="button" 
                                    wire:click="$set('progressClassFilter', {{ $cls->id }})" 
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $isSelected ? 'bg-[#2347C4] text-white shadow-xs' : 'bg-white text-[#4A515C] border border-[#E2E4DF] hover:bg-gray-100' }}"
                                    style="{{ $isSelected ? 'background-color: #2347C4; color: #FFFFFF !important;' : '' }}">
                                {{ $cls->name }} ({{ $clsCount }})
                            </button>
                        @endforeach
                    @endif
                </div>

                <!-- Input Cari Siswa -->
                <div class="relative w-full sm:w-56">
                    <input type="text" 
                           wire:model.live.debounce.250ms="progressSearch" 
                           placeholder="Cari nama / NISN..." 
                           class="w-full text-xs font-medium bg-white border border-[#E2E4DF] rounded-lg pl-8 pr-7 py-1.5 focus:border-[#2347C4] focus:ring-1 focus:ring-[#2347C4] text-[#15191F]">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    @if(!empty($progressSearch))
                        <button type="button" wire:click="$set('progressSearch', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-bold leading-none">
                            &times;
                        </button>
                    @endif
                </div>
            </div>

            <!-- Scrollable Table -->
            <div class="overflow-y-auto flex-1 border border-[#E2E4DF] rounded-xl">
                @if(empty($progressStudentList))
                    <div class="text-center py-12">
                        <p class="text-sm text-[#4A515C]">Belum ada siswa pada kelas yang ditugaskan ke materi ini.</p>
                    </div>
                @elseif(empty($this->filteredProgressStudents))
                    <div class="text-center py-12 flex flex-col items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <p class="text-sm text-[#4A515C]">Tidak ada siswa yang sesuai dengan filter kelas atau kata kunci pencarian.</p>
                        <button type="button" wire:click="$set('progressClassFilter', null); $set('progressSearch', '')" class="px-3 py-1.5 bg-[#EEF2FF] text-[#2347C4] text-xs font-bold rounded-lg hover:bg-[#E0E7FF] transition-colors">
                            Reset Filter Kelas
                        </button>
                    </div>
                @else
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#F8F9FA] text-[#4A515C] uppercase tracking-wider font-bold sticky top-0 border-b border-[#E2E4DF] z-10">
                            <tr>
                                <th class="p-3">Siswa</th>
                                <th class="p-3">
                                    <div class="flex items-center gap-1.5">
                                        <span>Kelas</span>
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" 
                                                    @click="open = !open" 
                                                    class="p-1 rounded hover:bg-gray-200 transition-colors {{ $progressClassFilter !== null ? 'text-[#2347C4] bg-indigo-50 font-bold' : 'text-gray-400' }}" 
                                                    title="Filter per kelas">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                            </button>
                                            <div x-show="open" 
                                                 @click.outside="open = false" 
                                                 x-transition 
                                                 class="absolute left-0 mt-1 w-48 rounded-xl bg-white shadow-xl border border-[#E2E4DF] py-1.5 z-40 font-normal normal-case" 
                                                 style="display: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);">
                                                <div class="px-3 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                                    Pilih Kelas
                                                </div>
                                                <button type="button" 
                                                        wire:click="$set('progressClassFilter', null); open = false;" 
                                                        class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 flex items-center justify-between {{ $progressClassFilter === null ? 'font-bold text-[#2347C4] bg-[#EEF2FF]' : 'text-[#15191F]' }}">
                                                    <span>Semua Kelas</span>
                                                    @if($progressClassFilter === null)
                                                        <svg class="w-3 h-3 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    @endif
                                                </button>
                                                @if($progressLesson)
                                                    @foreach($progressLesson->classrooms as $cls)
                                                        @php
                                                            $cCount = collect($progressStudentList)->where('classroom_id', $cls->id)->count();
                                                            $isAct = (int)$progressClassFilter === (int)$cls->id;
                                                        @endphp
                                                        <button type="button" 
                                                                wire:click="$set('progressClassFilter', {{ $cls->id }}); open = false;" 
                                                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 flex items-center justify-between {{ $isAct ? 'font-bold text-[#2347C4] bg-[#EEF2FF]' : 'text-[#15191F]' }}">
                                                            <span>{{ $cls->name }} ({{ $cCount }})</span>
                                                            @if($isAct)
                                                                <svg class="w-3 h-3 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                            @endif
                                                        </button>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="p-3">Bahan Selesai</th>
                                <th class="p-3">Progres</th>
                                <th class="p-3">Tugas Dikirim</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Aktivitas Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E4DF]">
                            @foreach($this->filteredProgressStudents as $item)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="p-3">
                                        <span class="font-bold text-[#15191F] block">{{ $item['student_name'] }}</span>
                                        <span class="text-[10px] text-gray-500 font-mono">{{ $item['student_nisn'] }}</span>
                                    </td>
                                    <td class="p-3">
                                        <button type="button" 
                                                wire:click="$set('progressClassFilter', {{ $item['classroom_id'] }})"
                                                class="px-2 py-0.5 bg-gray-100 hover:bg-[#EEF2FF] hover:text-[#2347C4] rounded text-gray-700 font-semibold text-[11px] transition-colors cursor-pointer"
                                                title="Klik untuk filter kelas ini">
                                            {{ $item['classroom_name'] }}
                                        </button>
                                    </td>
                                    <td class="p-3 font-semibold text-[#15191F]">
                                        {{ $item['completed_items_count'] }} / {{ $item['total_items'] }}
                                    </td>
                                    <td class="p-3 w-36">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-[#2347C4] rounded-full" style="width: {{ $item['progress_percent'] }}%"></div>
                                            </div>
                                            <span class="font-bold text-[11px] text-[#2347C4]">{{ $item['progress_percent'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        @if($item['total_tasks'] > 0)
                                            <span class="font-bold {{ $item['tasks_submitted'] >= $item['total_tasks'] ? 'text-green-700' : 'text-amber-700' }}">
                                                {{ $item['tasks_submitted'] }} / {{ $item['total_tasks'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($item['is_lesson_completed'])
                                            <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-[10px] font-bold">Selesai</span>
                                        @elseif($item['completed_items_count'] > 0)
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-bold">Sedang Belajar</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-medium">Belum Buka</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-gray-500 text-[11px]">
                                        {{ $item['last_activity'] ?? 'Belum ada aktivitas' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-[#E2E4DF] pt-3 shrink-0">
                <span class="text-xs text-[#4A515C]">
                    Menampilkan <strong class="text-[#15191F]">{{ count($this->filteredProgressStudents) }}</strong> dari <strong>{{ count($progressStudentList) }}</strong> siswa
                    @if($progressClassFilter !== null)
                        @php
                            $activeCls = $progressLesson ? $progressLesson->classrooms->firstWhere('id', $progressClassFilter) : null;
                        @endphp
                        (Filter: <strong class="text-[#2347C4]">{{ $activeCls ? $activeCls->name : 'Kelas Terpilih' }}</strong>)
                    @endif
                </span>
                <button type="button" wire:click="closeProgressModal" class="px-5 py-2 bg-white border border-[#E2E4DF] text-[#15191F] rounded-xl font-bold text-xs hover:bg-gray-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
