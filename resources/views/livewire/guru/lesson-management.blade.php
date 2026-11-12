<div>
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] rounded-xl flex items-center gap-3 font-medium" style="background-color: #E8F5E9; color: #2E7D32; border-color: #A5D6A7;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('message') }}
        </div>
    @endif

    @if($isModalOpen)
        <!-- ==================== HALAMAN UBAH / TAMBAH SUBBAB (FULL PAGE) ==================== -->
        <div class="bg-white min-h-screen">
            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-[#E2E4DF]" style="border-color: #E2E4DF;">
                <div class="flex items-center gap-4">
                    <button type="button" wire:click="closeModal" class="text-[#4A515C] hover:text-[#15191F] transition-colors" style="color: #4A515C;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    </button>
                    <h1 class="text-xl font-bold text-[#15191F]" style="color: #15191F;">{{ $lessonId ? 'Ubah subbab' : 'Buat subbab' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-lg font-bold text-sm hover:bg-[#F4F5F2] transition-colors" style="color: #4A515C; border-color: #E2E4DF;">Batal</button>
                    <button type="button" wire:click="save" class="px-4 py-2 bg-[#2347C4] text-white rounded-lg font-bold text-sm hover:bg-[#1A3596] transition-colors" style="background-color: #2347C4; color: #ffffff;">Simpan subbab</button>
                </div>
            </div>

            <!-- Form Layout -->
            <div class="p-6 max-w-4xl mx-auto">
                <form wire:submit="save" class="flex flex-col gap-6">
                    <!-- Judul & TP -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Judul subbab</label>
                            <input type="text" wire:model="title" placeholder="Contoh: Pengenalan Algoritma" class="w-full rounded-lg border-[#E2E4DF] bg-[#F4F5F2] text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="background-color: #F4F5F2; color: #15191F; border-color: #E2E4DF;">
                            @error('title') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Tujuan Pembelajaran (TP)</label>
                            <select wire:model="learning_objective_id" class="w-full rounded-lg border-[#E2E4DF] bg-[#F4F5F2] text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="background-color: #F4F5F2; color: #15191F; border-color: #E2E4DF;">
                                <option value="">Pilih TP</option>
                                @foreach($groupedLessons as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->code }} - {{ $tp->title }}</option>
                                @endforeach
                            </select>
                            @error('learning_objective_id') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Kode & Estimasi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Kode</label>
                            <input type="text" wire:model="code" placeholder="Contoh: 1.1" class="w-full font-mono rounded-lg border-[#E2E4DF] bg-[#F4F5F2] text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="background-color: #F4F5F2; color: #15191F; border-color: #E2E4DF;">
                            @error('code') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Estimasi (menit)</label>
                            <input type="number" wire:model="estimated_minutes" min="1" placeholder="Misal: 45" class="w-full rounded-lg border-[#E2E4DF] bg-[#F4F5F2] text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="background-color: #F4F5F2; color: #15191F; border-color: #E2E4DF;">
                            @error('estimated_minutes') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Ringkasan -->
                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Ringkasan</label>
                        <textarea wire:model="summary" rows="2" placeholder="Satu atau dua kalimat tentang materi ini..." class="w-full rounded-lg border-[#E2E4DF] bg-[#F4F5F2] text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="background-color: #F4F5F2; color: #15191F; border-color: #E2E4DF;"></textarea>
                        @error('summary') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Isi Materi -->
                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Isi materi</label>
                        <div class="border border-[#E2E4DF] rounded-lg overflow-hidden" style="border-color: #E2E4DF;">
                            <textarea wire:model="content" rows="10" placeholder="Tuliskan materi pembelajaran secara rinci di sini..." class="w-full border-0 bg-white text-[#15191F] text-sm focus:ring-0 p-4" style="color: #15191F;"></textarea>
                        </div>
                        @error('content') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Video YouTube -->
                    <div>
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Video YouTube (opsional)</label>
                        <div class="flex gap-2">
                            <input type="url" wire:model="video_url" placeholder="Paste link YouTube di sini" class="flex-1 rounded-lg border-[#E2E4DF] bg-[#F4F5F2] text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="background-color: #F4F5F2; color: #15191F; border-color: #E2E4DF;">
                            <button type="button" wire:click="previewVideo" class="px-4 py-2 bg-gray-100 text-[#15191F] hover:bg-gray-200 transition-colors font-bold text-sm rounded-lg border border-[#E2E4DF]" style="border-color: #E2E4DF;">Pratinjau</button>
                        </div>
                        @error('video_url') <span class="text-xs text-[#DC2626] font-medium" style="color: #DC2626;">{{ $message }}</span> @enderror
                    </div>

                    <!-- Lampiran -->
                    @if($lessonId)
                    <div class="border border-[#E2E4DF] rounded-lg p-4" style="border-color: #E2E4DF;">
                        <div class="flex justify-between items-center mb-4">
                            <label class="block text-sm font-bold text-[#15191F]" style="color: #15191F;">Lampiran</label>
                            <button type="button" wire:click="openAttachmentModal({{ $lessonId }})" class="text-sm font-bold text-[#2347C4] hover:underline" style="color: #2347C4;">+ Tambah lampiran</button>
                        </div>
                        <!-- Note: attachments are managed in the modal for now, or we can list them here -->
                        <p class="text-sm text-[#4A515C]" style="color: #4A515C;">Klik tombol tambah untuk mengelola lampiran.</p>
                    </div>
                    @else
                    <div class="border border-[#E2E4DF] rounded-lg p-4" style="border-color: #E2E4DF;">
                        <label class="block text-sm font-bold text-[#15191F] mb-1" style="color: #15191F;">Lampiran</label>
                        <p class="text-sm text-[#4A515C]" style="color: #4A515C;">Simpan subbab terlebih dahulu untuk menambahkan lampiran.</p>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    @else
        <!-- ==================== HALAMAN INDEX MATERI (LIST & SIDEBAR) ==================== -->
        <div class="flex flex-col lg:flex-row h-full min-h-[calc(100vh-4rem)] bg-white">
            
            <!-- Area Kiri: Daftar Materi -->
            <div class="flex-1 p-6 lg:border-r border-[#E2E4DF]" style="border-color: #E2E4DF;">
                
                <!-- Header Materi -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <h1 class="text-2xl font-bold text-[#15191F]" style="color: #15191F;">Materi</h1>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="copyFromTemplate" class="px-4 py-2 border border-[#E2E4DF] bg-white text-[#4A515C] rounded-lg font-bold text-sm hover:bg-[#F4F5F2] hover:text-[#15191F] transition-colors shadow-sm" style="color: #4A515C; border-color: #E2E4DF;">Salin dari template</button>
                        <button type="button" wire:click="create" class="px-4 py-2 bg-[#2347C4] text-white rounded-lg font-bold text-sm hover:bg-[#1A3596] transition-colors shadow-sm" style="background-color: #2347C4; color: #ffffff;">Buat subbab</button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <select wire:model.live="filterElement" class="rounded-lg border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] shadow-sm" style="border-color: #E2E4DF;">
                        <option value="">Semua Elemen</option>
                    </select>
                    <select wire:model.live="filterClass" class="rounded-lg border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] shadow-sm" style="border-color: #E2E4DF;">
                        <option value="">Semua Kelas</option>
                    </select>
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari subbab..." class="w-full pl-9 pr-4 py-2 rounded-lg border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4] shadow-sm" style="border-color: #E2E4DF;">
                        <svg class="absolute left-3 top-2.5 text-gray-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex items-center gap-6 border-b border-[#E2E4DF] mb-6" style="border-color: #E2E4DF;">
                    <button type="button" wire:click="setTab('semua')" class="pb-3 text-sm font-bold {{ $activeTab === 'semua' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors" style="{{ $activeTab === 'semua' ? 'color: #2347C4; border-color: #2347C4;' : 'color: #4A515C;' }}">Semua</button>
                    <button type="button" wire:click="setTab('terbit')" class="pb-3 text-sm font-bold {{ $activeTab === 'terbit' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors" style="{{ $activeTab === 'terbit' ? 'color: #2347C4; border-color: #2347C4;' : 'color: #4A515C;' }}">Terbit</button>
                    <button type="button" wire:click="setTab('terjadwal')" class="pb-3 text-sm font-bold {{ $activeTab === 'terjadwal' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors" style="{{ $activeTab === 'terjadwal' ? 'color: #2347C4; border-color: #2347C4;' : 'color: #4A515C;' }}">Terjadwal</button>
                    <button type="button" wire:click="setTab('draf')" class="pb-3 text-sm font-bold {{ $activeTab === 'draf' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors" style="{{ $activeTab === 'draf' ? 'color: #2347C4; border-color: #2347C4;' : 'color: #4A515C;' }}">Draf</button>
                </div>

                <!-- List TPs & Lessons -->
                <div class="flex flex-col gap-6">
                    @forelse($groupedLessons as $tp)
                        <div class="bg-white rounded-xl border border-[#E2E4DF] shadow-sm overflow-hidden" style="border-color: #E2E4DF;">
                            <!-- TP Header -->
                            <div class="px-5 py-4 border-b border-[#E2E4DF] flex justify-between items-center" style="border-color: #E2E4DF;">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#F4F5F2] flex items-center justify-center text-[#4A515C]" style="background-color: #F4F5F2;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-[#15191F]" style="color: #15191F;">{{ $tp->code }} {{ $tp->title }}</h3>
                                </div>
                                <button type="button" wire:click="create" class="text-sm font-bold text-[#2347C4] hover:underline" style="color: #2347C4;">+ Buat subbab</button>
                            </div>
                            
                            <!-- Subbabs -->
                            <div class="p-2">
                                @if($tp->lessons->isEmpty())
                                    <div class="text-center py-6">
                                        <p class="text-[#4A515C] text-sm" style="color: #4A515C;">Belum ada subbab.</p>
                                    </div>
                                @else
                                    <div class="flex flex-col gap-1">
                                        @foreach($tp->lessons as $lesson)
                                            <!-- Check if this is the active selected lesson for publishing -->
                                            @php
                                                $isSelected = $isPublishModalOpen && $lessonId === $lesson->id;
                                            @endphp
                                            <div class="group flex flex-col md:flex-row items-start md:items-center justify-between p-3 rounded-lg border {{ $isSelected ? 'border-[#2347C4] bg-[#F4F5F2]' : 'border-transparent hover:border-[#E2E4DF] hover:bg-[#F9FAFB]' }} transition-colors cursor-pointer" 
                                                 style="{{ $isSelected ? 'border-color: #2347C4; background-color: #F4F5F2;' : '' }}"
                                                 wire:click="openPublishModal({{ $lesson->id }})">
                                                
                                                <div class="flex items-start gap-3 flex-1">
                                                    <div class="pt-1 text-[#4A515C]">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <h4 class="font-bold text-[#15191F] text-sm" style="color: #15191F;">{{ $lesson->code }} {{ $lesson->title }}</h4>
                                                            @if($lesson->status === 'published')
                                                                <span class="px-2 py-0.5 bg-[#E8F5E9] text-[#2E7D32] text-[10px] font-bold rounded" style="background-color: #E8F5E9; color: #2E7D32;">Terbit</span>
                                                            @else
                                                                <span class="px-2 py-0.5 bg-[#F3F4F6] text-[#4B5563] text-[10px] font-bold rounded" style="background-color: #F3F4F6; color: #4B5563;">Draf</span>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="flex flex-wrap items-center gap-2 mt-1">
                                                            @foreach($lesson->classrooms as $cls)
                                                                <span class="px-2 py-0.5 bg-white border border-[#E2E4DF] text-[#4A515C] text-[10px] font-medium rounded" style="border-color: #E2E4DF; color: #4A515C;">{{ $cls->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons (Shown on hover or selected) -->
                                                <div class="flex items-center gap-1 mt-3 md:mt-0 opacity-0 group-hover:opacity-100 {{ $isSelected ? 'opacity-100' : '' }} transition-opacity">
                                                    <button type="button" wire:click.stop="edit({{ $lesson->id }})" class="p-2 text-[#4A515C] hover:text-[#2347C4] rounded transition-colors" title="Ubah Materi" style="color: #4A515C;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                    </button>
                                                    <button type="button" wire:click.stop="openAttachmentModal({{ $lesson->id }})" class="p-2 text-[#4A515C] hover:text-[#2347C4] rounded transition-colors" title="Lampiran" style="color: #4A515C;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                                    </button>
                                                    <button type="button" wire:confirm="Hapus subbab ini?" wire:click.stop="delete({{ $lesson->id }})" class="p-2 text-[#4A515C] hover:text-[#DC2626] rounded transition-colors" title="Hapus" style="color: #4A515C;">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-white rounded-xl border border-[#E2E4DF]" style="border-color: #E2E4DF;">
                            <p class="text-sm text-[#4A515C] mb-4" style="color: #4A515C;">Belum ada Tujuan Pembelajaran.</p>
                            <a href="{{ route('guru.tp') }}" class="px-4 py-2 border border-[#E2E4DF] bg-white text-[#15191F] rounded-lg font-bold text-sm hover:bg-[#F4F5F2]" style="border-color: #E2E4DF;">Ke Halaman TP</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Area Kanan: Panel SUBBAB TERPILIH (Penerbitan) -->
            @if($isPublishModalOpen && $lessonId)
                @php
                    $selectedLessonObj = \App\Models\Lesson::find($lessonId);
                @endphp
                <div class="w-full lg:w-[350px] shrink-0 bg-white shadow-[-4px_0_15px_rgba(0,0,0,0.05)] border-l border-[#E2E4DF] p-6 lg:h-[calc(100vh-4rem)] lg:sticky lg:top-16 overflow-y-auto" style="border-color: #E2E4DF;">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xs font-bold text-[#4A515C] uppercase tracking-wider" style="color: #4A515C;">Subbab Terpilih</h2>
                        <button type="button" wire:click="closePublishModal" class="text-[#4A515C] hover:text-[#15191F]" style="color: #4A515C;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-[#15191F] leading-tight" style="color: #15191F;">{{ $selectedLessonObj ? $selectedLessonObj->code . ' ' . $selectedLessonObj->title : '' }}</h3>
                    </div>

                    <form wire:submit="savePublish" class="flex flex-col gap-6">
                        
                        <!-- Status Panel -->
                        <div class="bg-white border border-[#E2E4DF] rounded-xl overflow-hidden" style="border-color: #E2E4DF;">
                            <div class="px-4 py-3 border-b border-[#E2E4DF] bg-[#F9FAFB]" style="border-color: #E2E4DF; background-color: #F9FAFB;">
                                <h4 class="text-sm font-bold text-[#15191F]" style="color: #15191F;">Status</h4>
                            </div>
                            <div class="p-4 flex flex-col gap-3">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" wire:model.live="status" value="draft" class="w-4 h-4 text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]" style="color: #2347C4; border-color: #E2E4DF;">
                                    <span class="text-sm font-medium text-[#15191F]" style="color: #15191F;">Draf</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" wire:model.live="status" value="published" class="w-4 h-4 text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]" style="color: #2347C4; border-color: #E2E4DF;">
                                    <span class="text-sm font-medium text-[#15191F]" style="color: #15191F;">Terbit</span>
                                </label>
                            </div>
                        </div>

                        <!-- Terbit ke kelas Panel -->
                        @if($status === 'published')
                            <div class="bg-white border border-[#E2E4DF] rounded-xl overflow-hidden" style="border-color: #E2E4DF;">
                                <div class="px-4 py-3 border-b border-[#E2E4DF] bg-[#F9FAFB]" style="border-color: #E2E4DF; background-color: #F9FAFB;">
                                    <h4 class="text-sm font-bold text-[#15191F]" style="color: #15191F;">Terbit ke kelas</h4>
                                </div>
                                <div class="p-0">
                                    @if($myClassrooms->isEmpty())
                                        <div class="p-4 text-[#DC2626] text-sm" style="color: #DC2626;">Anda belum ditugaskan ke kelas manapun.</div>
                                    @else
                                        <div class="flex flex-col divide-y divide-[#E2E4DF]" style="border-color: #E2E4DF;">
                                            @foreach($myClassrooms as $classroom)
                                                <div class="p-4">
                                                    <label class="flex items-center gap-3 cursor-pointer mb-2">
                                                        <input type="checkbox" wire:model="selectedClassrooms" value="{{ $classroom->id }}" class="w-4 h-4 rounded text-[#2347C4] border-[#E2E4DF] focus:ring-[#2347C4]" style="color: #2347C4; border-color: #E2E4DF;">
                                                        <span class="text-sm font-bold text-[#15191F]" style="color: #15191F;">{{ $classroom->name }}</span>
                                                    </label>
                                                    <div class="pl-7">
                                                        <label class="text-[11px] text-[#4A515C] block mb-1" style="color: #4A515C;">Tanggal mulai dibuka (opsional)</label>
                                                        <input type="datetime-local" wire:model="availableFrom.{{ $classroom->id }}" class="w-full text-xs rounded-lg border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4]" style="border-color: #E2E4DF; color: #15191F;" />
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <button type="submit" class="w-full py-2.5 bg-[#2347C4] text-white rounded-lg font-bold text-sm hover:bg-[#1A3596] transition-colors" style="background-color: #2347C4; color: #ffffff;">Simpan penerbitan</button>
                    </form>
                </div>
            @endif
        </div>
    @endif

    <!-- ==================== MODAL LAMPIRAN (Tetap sebagai modal overlay) ==================== -->
    <div x-data="{ show: @entangle('isAttachmentModalOpen').live }" 
         x-show="show" 
         x-cloak 
         style="display: none;"
         class="relative z-50" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="show" 
                 @click.outside="$wire.closeAttachmentModal()"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative z-50 bg-white p-6 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto mx-auto border border-[#E2E4DF]" style="border-color: #E2E4DF;">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-[#15191F]" style="color: #15191F;">Kelola Lampiran</h2>
                        @if($activeLessonForAttachment)
                            <p class="text-sm text-[#4A515C] mt-1" style="color: #4A515C;">{{ $activeLessonForAttachment->title }}</p>
                        @endif
                    </div>
                    <button type="button" wire:click="closeAttachmentModal" class="text-[#4A515C] hover:text-[#15191F] transition-colors" style="color: #4A515C;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                @if (session()->has('attachment_message'))
                    <div class="mb-4 p-3 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] rounded-xl text-sm font-medium" style="background-color: #E8F5E9; color: #2E7D32; border-color: #A5D6A7;">
                        {{ session('attachment_message') }}
                    </div>
                @endif
                
                <form wire:submit="uploadAttachment" class="bg-[#F4F5F2] p-4 rounded-xl border border-[#E2E4DF] mb-6" style="background-color: #F4F5F2; border-color: #E2E4DF;">
                    <h3 class="font-bold text-[#15191F] mb-3 text-sm" style="color: #15191F;">Unggah Dokumen Baru</h3>
                    <div class="flex flex-col md:flex-row gap-4 items-start md:items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-[#4A515C] mb-1" style="color: #4A515C;">Nama / Label</label>
                            <input type="text" wire:model="attachmentLabel" class="w-full rounded-xl border-[#E2E4DF] bg-white text-[#15191F] text-sm focus:border-[#2347C4] focus:ring-[#2347C4]" style="border-color: #E2E4DF; color: #15191F;">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-[#4A515C] mb-1" style="color: #4A515C;">Pilih File</label>
                            <input type="file" wire:model="newAttachment" class="w-full text-sm text-[#4A515C] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-white file:text-[#2347C4] hover:file:bg-[#FDF3E9] file:border file:border-[#E2E4DF] file:cursor-pointer" style="color: #4A515C;">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl font-bold text-sm hover:bg-[#1A3596] transition-colors" style="background-color: #2347C4; color: #ffffff;">Unggah</button>
                    </div>
                    <div wire:loading wire:target="newAttachment" class="text-xs text-[#E0892A] font-bold mt-2" style="color: #E0892A;">Mengunggah file...</div>
                </form>

                <div>
                    <h3 class="font-bold text-[#15191F] mb-3" style="color: #15191F;">Daftar Lampiran</h3>
                    @if($activeLessonForAttachment && $activeLessonForAttachment->attachments->count() > 0)
                        <div class="flex flex-col gap-3">
                            @foreach($activeLessonForAttachment->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 border border-[#E2E4DF] rounded-xl" style="border-color: #E2E4DF;">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="truncate">
                                            <p class="font-bold text-[#15191F] text-sm truncate" style="color: #15191F;">{{ $attachment->label }}</p>
                                        </div>
                                    </div>
                                    <button type="button" wire:confirm="Hapus lampiran ini?" wire:click="deleteAttachment({{ $attachment->id }})" class="p-2 text-[#4A515C] hover:text-[#DC2626]" style="color: #4A515C;">
                                        Hapus
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-[#F4F5F2] rounded-xl border border-[#E2E4DF] border-dashed" style="background-color: #F4F5F2; border-color: #E2E4DF;">
                            <p class="text-[#4A515C] text-sm" style="color: #4A515C;">Belum ada lampiran.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JS untuk event dispatcher -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-video', (event) => {
                if (event.url) {
                    window.open(event.url, '_blank');
                }
            });
        });
    </script>
</div>
