<div>
    @if($isPreviewMode)
        <!-- Banner Mode Pratinjau Guru -->
        <div class="bg-[#EEF2FF] border-b border-[#C7D2FE] px-6 py-3 flex flex-wrap items-center justify-between gap-3 sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#2347C4] animate-pulse"></span>
                <span class="text-xs font-bold text-[#1E3A8A]">Mode Pratinjau Tampilan Siswa:</span>
                <span class="text-xs text-[#3B82F6]">Anda sedang melihat materi <strong class="text-[#1E3A8A]">{{ $lesson->title }}</strong> persis seperti yang tampil pada akun siswa.</span>
            </div>
            <a href="{{ route('guru.lessons') }}" class="px-3 py-1.5 bg-[#2347C4] text-white text-xs font-bold rounded-lg hover:bg-[#1A3596] transition-colors shadow-sm flex items-center gap-1.5" style="color: #FFFFFF !important;">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #FFFFFF !important; stroke: #FFFFFF !important;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span style="color: #FFFFFF !important;">Kembali ke Kelola Materi</span>
            </a>
        </div>
    @endif

    @if($isNotFound || $isLocked)
        <div class="max-w-4xl mx-auto px-6 py-16 flex flex-col items-center justify-center min-h-[50vh] text-center">
            <div class="bg-white border border-[#E2E4DF] rounded-2xl p-8 max-w-lg w-full shadow-sm">
                <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#FFFBEB] text-[#B45309] mb-4">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </span>
                <h2 class="text-lg font-bold text-[#15191F] mb-2">{{ $isLocked ? 'Materi Terkunci' : 'Materi Tidak Tersedia' }}</h2>
                <p class="text-sm text-[#4A515C] mb-6 leading-relaxed">{{ $errorMessage }}</p>
                <a href="{{ route('siswa.belajar') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#2347C4] text-white font-bold text-sm hover:bg-[#1A3596] transition-colors shadow-sm" style="color: #FFFFFF !important;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #FFFFFF !important; stroke: #FFFFFF !important;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span style="color: #FFFFFF !important;">Kembali ke Halaman Belajar</span>
                </a>
            </div>
        </div>
    @else
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col lg:flex-row gap-8 items-start">

            <!-- SIDEBAR KIRI: DAFTAR SUBBAB MATERI -->
            <aside aria-label="Daftar Subbab" class="w-full lg:w-80 shrink-0 bg-white border border-[#E2E4DF] rounded-2xl p-5 flex flex-col gap-5 shadow-sm">
                <div class="flex flex-col gap-1">
                    <span class="font-mono text-xs font-bold text-[#2347C4] tracking-wider uppercase">
                        {{ $tp ? $tp->code : 'ATP' }} • {{ $element ? $element->name : 'ELEMEN' }}
                    </span>
                    <h2 class="text-base font-bold text-[#15191F] leading-snug">
                        {{ $tp ? $tp->title : 'Tujuan Pembelajaran' }}
                    </h2>
                </div>

                <!-- Progress Bar Sidebar -->
                @if($totalLessonsCount > 0)
                    <div class="flex flex-col gap-1.5 p-3 bg-[#F8F9FA] rounded-xl border border-[#E2E4DF]">
                        <div class="flex items-center justify-between text-xs font-semibold text-[#4A515C]">
                            <span>Progres Bab:</span>
                            <span class="font-bold text-[#15191F]">{{ $completedLessonsCount }} / {{ $totalLessonsCount }} subbab</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                            <div class="h-2 rounded-full bg-[#2347C4] transition-all duration-300" 
                                 style="width: {{ $totalLessonsCount > 0 ? round(($completedLessonsCount / $totalLessonsCount) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- Navigasi List Subbab -->
                <nav class="flex flex-col gap-1.5">
                    @foreach($lessons as $idx => $lsn)
                        @php
                            $isActive = $lsn->id === $lesson->id;
                            $isLessonDone = false;
                            if (auth()->check() && auth()->user()->role === 'siswa') {
                                $isLessonDone = \App\Models\LessonProgress::where('student_id', auth()->id())
                                    ->where('lesson_id', $lsn->id)
                                    ->whereNotNull('completed_at')
                                    ->exists();
                            }
                        @endphp

                        <a href="{{ $isPreviewMode ? route('guru.lessons.preview', $lsn->id) : route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $lsn->code ?: $lsn->id]) }}" 
                           class="flex items-center gap-3 p-2.5 rounded-xl text-xs transition-colors {{ $isActive ? 'bg-[#EEF2FF] font-bold text-[#2347C4] border border-[#C7D2FE]' : 'text-[#4A515C] hover:bg-[#F8F9FA] hover:text-[#15191F]' }}">
                            @if($isLessonDone)
                                <span class="w-6 h-6 rounded-full bg-[#2E7D32] text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                            @elseif($isActive)
                                <span class="w-6 h-6 rounded-full bg-[#2347C4] text-white flex items-center justify-center shrink-0 font-bold">
                                    {{ $idx + 1 }}
                                </span>
                            @else
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-[#4A515C] flex items-center justify-center shrink-0 font-semibold border border-gray-200">
                                    {{ $idx + 1 }}
                                </span>
                            @endif
                            <span class="truncate {{ $isActive ? 'text-[#15191F]' : '' }}">{{ $lsn->code }} {{ $lsn->title }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <!-- KONTEN UTAMA: BACA > TONTON > KERJAKAN -->
            <main class="flex-1 min-w-0 flex flex-col gap-8">
                
                <!-- Breadcrumbs & Header Materi -->
                <div class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm flex flex-col gap-4">
                    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs text-[#4A515C]">
                        <a href="{{ route('siswa.beranda') }}" class="hover:underline">Beranda</a>
                        <span>/</span>
                        <a href="{{ route('siswa.belajar') }}" class="hover:underline">Belajar</a>
                        <span>/</span>
                        <span class="text-[#15191F] font-semibold">{{ $element ? $element->name : '' }}</span>
                    </nav>

                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 bg-[#EEF2FF] text-[#2347C4] font-mono text-xs font-bold rounded">
                                {{ $lesson->code }}
                            </span>
                            @if($lesson->estimated_minutes)
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded flex items-center gap-1">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>±{{ $lesson->estimated_minutes }} menit</span>
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#15191F] tracking-tight leading-tight">
                            {{ $lesson->title }}
                        </h1>
                    </div>

                    <!-- Tujuan Pembelajaran Materi -->
                    @if($lesson->summary)
                        <div class="p-4 bg-[#F8F9FA] rounded-xl border border-[#E2E4DF]">
                            <h2 class="text-xs font-bold text-[#4A515C] uppercase tracking-wider mb-1">Tujuan Pembelajaran:</h2>
                            <p class="text-sm text-[#15191F] leading-relaxed font-medium">{{ $lesson->summary }}</p>
                        </div>
                    @endif

                    <!-- Rekap Progres Belajar Siswa untuk Materi Ini -->
                    @if(!$isPreviewMode)
                        @php
                            $prog = $this->progressData;
                        @endphp
                        <div class="p-4 bg-[#EEF2FF] rounded-xl border border-[#C7D2FE] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#2347C4] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ $prog['percent'] }}%
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-[#1E3A8A]">Progres Aktivitas Belajar Kamu:</h3>
                                    <p class="text-xs text-[#3B82F6]">
                                        {{ $prog['completed'] }} dari {{ $prog['total'] }} aktivitas (literasi & video) telah diselesaikan
                                    </p>
                                </div>
                            </div>
                            
                            @if($this->isDone)
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#E8F5E9] text-[#2E7D32] text-xs font-bold rounded-xl border border-[#A5D6A7] shrink-0">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Materi Selesai</span>
                                </span>
                            @else
                                <button type="button" wire:click="toggleOverallDone" class="px-4 py-1.5 bg-[#2347C4] text-white text-xs font-bold rounded-xl hover:bg-[#1A3596] transition-colors shrink-0">
                                    Tandai Materi Selesai
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Tabs: Materi Belajar, Diskusi, Catatan -->
                <div class="flex items-center gap-4 border-b border-[#E2E4DF] bg-white px-6 rounded-t-2xl pt-3">
                    <button type="button" wire:click="setTab('materi')" class="pb-3 text-sm font-bold {{ $tab === 'materi' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors">
                        Alur Belajar Siswa
                    </button>
                    <button type="button" wire:click="setTab('diskusi')" class="pb-3 text-sm font-bold {{ $tab === 'diskusi' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors flex items-center gap-1.5">
                        <span>Diskusi Kelas</span>
                        @if($discussionCount > 0)
                            <span class="px-2 py-0.5 bg-gray-100 text-[#4A515C] text-xs rounded-full font-bold">{{ $discussionCount }}</span>
                        @endif
                    </button>
                    <button type="button" wire:click="setTab('catatan')" class="pb-3 text-sm font-bold {{ $tab === 'catatan' ? 'text-[#2347C4] border-b-2 border-[#2347C4]' : 'text-[#4A515C] hover:text-[#15191F]' }} transition-colors">
                        Catatan Pribadi
                    </button>
                </div>

                @if($tab === 'materi')
                    <div class="flex flex-col gap-8">
                        
                        <!-- PENGANTAR / URAIAN MATERI (JIKA ADA) -->
                        @if($lesson->content)
                            <section class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm">
                                <h2 class="text-base font-bold text-[#15191F] mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    <span>Pengantar & Uraian Konsep</span>
                                </h2>
                                <div class="prose max-w-none text-[#2F353E] text-sm leading-relaxed whitespace-pre-line">
                                    {{ $lesson->content }}
                                </div>
                            </section>
                        @endif

                        <!-- ==================== LANGKAH 1: BACA BAHAN BACAAN (LITERASI PDF) ==================== -->
                        @if($readings->isNotEmpty())
                            <section class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm flex flex-col gap-5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E2E4DF] pb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-bold text-sm shrink-0">
                                            1
                                        </div>
                                        <div>
                                            <h2 class="text-base font-bold text-[#15191F]">Langkah 1: Baca Bahan Bacaan (Literasi PDF)</h2>
                                            <p class="text-xs text-[#4A515C] mt-0.5">Pelajari dokumen berikut langsung di layar atau unduh untuk dibaca secara offline.</p>
                                        </div>
                                    </div>

                                    @if($activePdf)
                                        @php
                                            $isPdfDone = $this->isItemDone('pdf_' . $activePdf->id);
                                        @endphp
                                        <button type="button" wire:click="toggleItemProgress('pdf_{{ $activePdf->id }}')" 
                                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 self-start sm:self-auto shadow-sm {{ $isPdfDone ? 'bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7]' : 'bg-[#2347C4] text-white hover:bg-[#1A3596]' }}">
                                            @if($isPdfDone)
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>Sudah Dibaca</span>
                                            @else
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                                                <span>Tandai Sudah Dibaca</span>
                                            @endif
                                        </button>
                                    @endif
                                </div>

                                <!-- Tab Pemilihan PDF jika lebih dari 1 file -->
                                @if($readings->count() > 1)
                                    <div class="flex flex-wrap items-center gap-2">
                                        @foreach($readings as $idx => $pdf)
                                            @php
                                                $isSelectedPdf = $activePdf && $activePdf->id === $pdf->id;
                                                $isThisPdfDone = $this->isItemDone('pdf_' . $pdf->id);
                                            @endphp
                                            <button type="button" wire:click="setActivePdf({{ $pdf->id }})" 
                                                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 border {{ $isSelectedPdf ? 'bg-[#2347C4] text-white border-[#2347C4] shadow-sm' : 'bg-[#F8F9FA] text-[#4A515C] border-[#E2E4DF] hover:bg-gray-100' }}">
                                                @if($isThisPdfDone)
                                                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                                @endif
                                                <span>{{ $pdf->label }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- PDF Embedded Viewer Container -->
                                @if($activePdf)
                                    <div id="pdf-viewer-card-{{ $activePdf->id }}" class="bg-[#15191F] rounded-2xl overflow-hidden border border-[#E2E4DF] shadow-md flex flex-col">
                                        <!-- Viewer Toolbar -->
                                        <div class="px-4 py-3 bg-[#1F2937] text-white flex flex-wrap items-center justify-between gap-3 border-b border-gray-700">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 bg-red-600 text-white font-mono text-[10px] font-bold rounded">PDF</span>
                                                <span class="text-xs font-bold text-gray-200 truncate max-w-xs sm:max-w-md">{{ $activePdf->label }}</span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <!-- Tombol Fullscreen -->
                                                <button type="button" onclick="toggleFullscreen('pdf-viewer-card-{{ $activePdf->id }}')" 
                                                        class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5" 
                                                        title="Buka Layar Penuh">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                                                    <span class="hidden sm:inline">Layar Penuh</span>
                                                </button>

                                                <!-- Tombol Unduh PDF -->
                                                <a href="{{ route('materi.attachment.download', [$lesson->id, $activePdf->id]) }}" 
                                                   class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5" 
                                                   style="color: #FFFFFF !important;"
                                                   title="Unduh File PDF">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #FFFFFF !important; stroke: #FFFFFF !important;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                    <span class="hidden sm:inline" style="color: #FFFFFF !important;">Unduh PDF</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Iframe / Object Native PDF Viewer -->
                                        <div class="relative w-full bg-gray-800" style="min-height: 620px;">
                                            <iframe src="{{ route('materi.attachment.stream', [$lesson->id, $activePdf->id]) }}" 
                                                    class="w-full h-[620px] border-0" 
                                                    title="{{ $activePdf->label }}">
                                                <!-- Fallback jika iframe tidak render di browser jadul -->
                                                <div class="p-8 text-center text-white flex flex-col items-center justify-center h-full">
                                                    <p class="text-sm mb-4">Browser Anda tidak dapat menampilkan pratinjau dokumen PDF secara langsung.</p>
                                                    <a href="{{ route('materi.attachment.download', [$lesson->id, $activePdf->id]) }}" class="px-4 py-2 bg-[#2347C4] text-white rounded-xl text-xs font-bold" style="color: #FFFFFF !important;">
                                                        <span style="color: #FFFFFF !important;">Unduh Dokumen PDF</span>
                                                    </a>
                                                </div>
                                            </iframe>
                                        </div>
                                    </div>
                                @endif
                            </section>
                        @endif

                        <!-- ==================== LANGKAH 2: TONTON VIDEO PEMBELAJARAN ==================== -->
                        @if($videos->isNotEmpty())
                            <section class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm flex flex-col gap-6">
                                <div class="flex items-center gap-3 border-b border-[#E2E4DF] pb-4">
                                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ $readings->isNotEmpty() ? '2' : '1' }}
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-[#15191F]">Langkah 2: Tonton Video Pembelajaran</h2>
                                        <p class="text-xs text-[#4A515C] mt-0.5">Simak penjelasan video edukatif berikut untuk memperdalam pemahaman materi.</p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-8">
                                    @foreach($videos as $vIndex => $vid)
                                        @php
                                            $isVideoDone = $this->isItemDone('video_' . $vid->id);
                                        @endphp
                                        <div class="bg-[#F8F9FA] p-5 rounded-2xl border border-[#E2E4DF] flex flex-col gap-4">
                                            <!-- Video Embed 16:9 Responsive via youtube-nocookie.com -->
                                            <div class="relative w-full aspect-video rounded-xl overflow-hidden bg-black shadow-md">
                                                <iframe src="https://www.youtube-nocookie.com/embed/{{ $vid->file_path }}?rel=0&modestbranding=1" 
                                                        title="{{ $vid->label }}" 
                                                        class="absolute inset-0 w-full h-full border-0" 
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                        allowfullscreen></iframe>
                                            </div>

                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                                                <div>
                                                    <h3 class="text-sm font-bold text-[#15191F]">{{ $vid->label }}</h3>
                                                    <!-- Link Cadangan jika video dibatasi/dimatikan -->
                                                    <p class="text-xs text-[#4A515C] mt-1">
                                                        Video tidak bisa diputar? 
                                                        <a href="https://www.youtube.com/watch?v={{ $vid->file_path }}" target="_blank" rel="noopener noreferrer" class="text-[#2347C4] hover:underline font-bold inline-flex items-center gap-1">
                                                            <span>Buka langsung di YouTube</span>
                                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                        </a>
                                                    </p>
                                                </div>

                                                <!-- Tombol Tandai Sudah Ditonton -->
                                                <button type="button" wire:click="toggleItemProgress('video_{{ $vid->id }}')" 
                                                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 self-start sm:self-auto shrink-0 shadow-sm {{ $isVideoDone ? 'bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7]' : 'bg-[#2347C4] text-white hover:bg-[#1A3596]' }}">
                                                    @if($isVideoDone)
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                        <span>Sudah Ditonton</span>
                                                    @else
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                                        <span>Tandai Sudah Ditonton</span>
                                                    @endif
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <!-- ==================== LANGKAH 3: KERJAKAN TUGAS ==================== -->
                        @if(!empty($tasksData))
                            <section class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm flex flex-col gap-5">
                                <div class="flex items-center gap-3 border-b border-[#E2E4DF] pb-4">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-[#2347C4] flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ ($readings->isNotEmpty() && $videos->isNotEmpty()) ? '3' : (($readings->isNotEmpty() || $videos->isNotEmpty()) ? '2' : '1') }}
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-[#15191F]">Langkah 3: Kerjakan Tugas</h2>
                                        <p class="text-xs text-[#4A515C] mt-0.5">Selesaikan tugas terkait materi ini dan kumpulkan file (PPT/PPTX, PDF, Word, dsb).</p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-4">
                                    @foreach($tasksData as $tItem)
                                        @php
                                            $ass = $tItem['assessment'];
                                            $isSub = $tItem['is_submitted'];
                                            $isGrd = $tItem['is_graded'];
                                        @endphp
                                        <div class="p-5 rounded-2xl border border-[#E2E4DF] bg-[#F8F9FA] hover:bg-white transition-colors shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div class="flex items-start gap-3.5">
                                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-[#2347C4] flex items-center justify-center font-bold shrink-0">
                                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-sm font-bold text-[#15191F]">{{ $ass->title }}</h3>
                                                    <div class="flex flex-wrap items-center gap-3 text-xs text-[#4A515C] mt-1">
                                                        <span>Tenggat: <strong class="{{ $tItem['is_past_due'] ? 'text-red-600 font-bold' : 'text-[#15191F]' }}">{{ $tItem['due_formatted'] }}</strong></span>
                                                        <span>•</span>
                                                        <span>Bobot: <strong>{{ $ass->max_score ?? 100 }} Poin</strong></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 self-end sm:self-center shrink-0">
                                                <!-- Status Badge Siswa -->
                                                @if($isGrd)
                                                    <span class="px-3 py-1 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] font-bold text-xs rounded-xl">
                                                        Nilai: {{ $tItem['score'] }} / {{ $ass->max_score ?? 100 }}
                                                    </span>
                                                @elseif($isSub)
                                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs rounded-xl">
                                                        Sudah Dikumpulkan
                                                    </span>
                                                @else
                                                    <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 font-bold text-xs rounded-xl">
                                                        Belum Mengumpulkan
                                                    </span>
                                                @endif

                                                <!-- Tombol Kerjakan -->
                                                @if(!$isPreviewMode)
                                                    <a href="{{ route('siswa.tugas.kerjakan', $ass->id) }}" 
                                                       class="px-4 py-2 bg-[#2347C4] text-white rounded-xl text-xs font-bold hover:bg-[#1A3596] shadow-sm transition-colors flex items-center gap-1.5"
                                                       style="color: #FFFFFF !important; text-decoration: none;">
                                                        <span style="color: #FFFFFF !important;">{{ $isSub ? 'Lihat Tugas' : 'Kerjakan Sekarang' }}</span>
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #FFFFFF !important; stroke: #FFFFFF !important;"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <!-- Navigasi Subbab Selanjutnya / Sebelumnya -->
                        <nav aria-label="Navigasi Subbab" class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-[#E2E4DF]">
                            @if($prevLesson)
                                <a href="{{ $isPreviewMode ? route('guru.lessons.preview', $prevLesson->id) : route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $prevLesson->code ?: $prevLesson->id]) }}" 
                                   class="flex items-center gap-3 p-3 rounded-xl border border-[#E2E4DF] bg-white hover:bg-[#F8F9FA] transition-colors text-xs font-bold text-[#15191F]">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                    <div>
                                        <span class="block text-[10px] font-normal text-[#4A515C] uppercase">Sebelumnya</span>
                                        <span>{{ $prevLesson->code }} {{ $prevLesson->title }}</span>
                                    </div>
                                </a>
                            @else
                                <div></div>
                            @endif

                            @if($nextLesson)
                                <a href="{{ $isPreviewMode ? route('guru.lessons.preview', $nextLesson->id) : route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $nextLesson->code ?: $nextLesson->id]) }}" 
                                   class="flex items-center gap-3 p-3 rounded-xl border border-[#E2E4DF] bg-white hover:bg-[#F8F9FA] transition-colors text-xs font-bold text-[#15191F] text-right">
                                    <div>
                                        <span class="block text-[10px] font-normal text-[#4A515C] uppercase">Berikutnya</span>
                                        <span>{{ $nextLesson->code }} {{ $nextLesson->title }}</span>
                                    </div>
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

                @if($tab === 'diskusi')
                    <section class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm flex flex-col gap-6">
                        
                        <!-- Header & Classroom Filter for Teacher/Admin -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#E2E4DF]">
                            <div>
                                <h2 class="text-base font-bold text-[#15191F] flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[#2347C4]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    <span>Ruang Diskusi & Tanya Jawab Materi</span>
                                </h2>
                                <p class="text-xs text-[#4A515C] mt-0.5">Diskusikan materi ini bersama guru dan teman-teman sekelas.</p>
                            </div>

                            @if(auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']) && $lesson->classrooms->count() > 0)
                                <div class="flex items-center gap-2 self-start sm:self-auto">
                                    <label class="text-xs font-bold text-[#4A515C] shrink-0">Filter Kelas:</label>
                                    <select wire:model.live="selectedClassroomId" class="text-xs font-semibold rounded-xl border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] py-1.5 px-3 focus:border-[#2347C4] focus:ring-[#2347C4]">
                                        <option value="">Semua Kelas ({{ $lesson->classrooms->pluck('name')->join(', ') }})</option>
                                        @foreach($lesson->classrooms as $cls)
                                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <!-- Flash Messages -->
                        @if(session()->has('discussion_success'))
                            <div class="p-3 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] rounded-xl text-xs font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ session('discussion_success') }}</span>
                            </div>
                        @endif

                        @if(session()->has('discussion_error'))
                            <div class="p-3 bg-[#FEF2F2] text-[#B91C1C] border border-[#FECACA] rounded-xl text-xs font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>{{ session('discussion_error') }}</span>
                            </div>
                        @endif

                        <!-- Form Tulis Pertanyaan / Topik Baru -->
                        <form wire:submit="sendDiscussion" class="flex flex-col gap-3 p-4 bg-[#F8F9FA] rounded-2xl border border-[#E2E4DF]">
                            <label class="text-xs font-bold text-[#15191F] flex items-center justify-between">
                                <span>{{ auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']) ? 'Tulis Pertanyaan Pemantik, Catatan, atau Pengumuman Diskusi:' : 'Tulis Pertanyaan atau Diskusi Baru:' }}</span>
                                <span class="text-[11px] font-normal text-[#4A515C]">Maks. 1000 karakter</span>
                            </label>
                            <textarea wire:model="discussionBody" rows="3" 
                                      placeholder="{{ auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']) ? 'Tulis pesan panduan belajar, pertanyaan untuk dijawab siswa, atau klarifikasi materi...' : 'Tanyakan hal yang belum kamu pahami dari bahan bacaan atau video...' }}" 
                                      class="w-full text-xs sm:text-sm rounded-xl border-[#E2E4DF] bg-white text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] p-3"></textarea>
                            <div class="flex items-center justify-between">
                                @if(auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']) && $lesson->classrooms->count() > 1)
                                    <span class="text-[11px] text-[#4A515C]">
                                        Tujuan: <strong class="text-[#15191F]">{{ $selectedClassroomId ? $lesson->classrooms->firstWhere('id', $selectedClassroomId)?->name : 'Semua Kelas' }}</strong>
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <button type="submit" class="px-5 py-2 bg-[#2347C4] text-white font-bold text-xs rounded-xl hover:bg-[#1A3596] shadow-sm flex items-center gap-1.5 transition-colors" style="color: #FFFFFF !important;">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #FFFFFF !important; stroke: #FFFFFF !important;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                    <span style="color: #FFFFFF !important;">{{ auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']) ? 'Kirim ke Forum' : 'Kirim Pertanyaan' }}</span>
                                </button>
                            </div>
                        </form>

                        <!-- Daftar Thread Diskusi -->
                        <div class="flex flex-col gap-4 divide-y divide-[#E2E4DF]">
                            @forelse($discussions as $disc)
                                <div class="pt-5 flex flex-col gap-3 {{ $disc->is_hidden ? 'opacity-60 bg-gray-50 p-3 rounded-xl border border-dashed border-gray-300' : '' }}">
                                    <!-- Main Post Header -->
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <span class="w-9 h-9 rounded-full {{ $disc->user?->role === 'guru' ? 'bg-[#2347C4] text-white' : 'bg-[#EEF2FF] text-[#2347C4]' }} font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                                                {{ $disc->user ? $disc->user->initials() : 'U' }}
                                            </span>
                                            <div class="flex flex-col">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-bold text-[#15191F]">{{ $disc->user ? $disc->user->name : 'Pengguna' }}</span>
                                                    @if($disc->user?->role === 'guru')
                                                        <span class="px-2 py-0.5 bg-[#EEF2FF] text-[#2347C4] border border-[#C7D2FE] text-[10px] font-bold rounded-full">
                                                            Guru Pengampu
                                                        </span>
                                                    @endif
                                                    @if($disc->classroom)
                                                        <span class="px-2 py-0.5 bg-[#F8F9FA] text-[#4A515C] border border-[#E2E4DF] text-[10px] font-semibold rounded-full">
                                                            {{ $disc->classroom->name }}
                                                        </span>
                                                    @endif
                                                    <span class="text-[11px] text-[#4A515C]">• {{ $disc->created_at->diffForHumans() }}</span>
                                                </div>

                                                <!-- Body Post -->
                                                <p class="text-xs sm:text-sm text-[#2F353E] leading-relaxed whitespace-pre-line mt-1.5">{{ $disc->body }}</p>

                                                @if($disc->is_hidden)
                                                    <span class="mt-1 text-[11px] font-bold text-red-600 flex items-center gap-1">
                                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                                        <span>(Pesan ini disembunyikan dari siswa oleh guru)</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Post Moderation / Actions -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            @if(auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']))
                                                <button type="button" 
                                                        wire:click="toggleHideDiscussion({{ $disc->id }})" 
                                                        class="p-1.5 rounded-lg text-xs font-semibold {{ $disc->is_hidden ? 'text-green-700 bg-green-50 hover:bg-green-100' : 'text-gray-500 hover:text-amber-700 hover:bg-amber-50' }} transition-colors" 
                                                        title="{{ $disc->is_hidden ? 'Tampilkan kembali' : 'Sembunyikan dari siswa' }}">
                                                    {{ $disc->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}
                                                </button>
                                            @endif

                                            @if(auth()->check() && (in_array(auth()->user()->role, ['guru', 'admin']) || auth()->id() === $disc->user_id))
                                                <button type="button" 
                                                        wire:click="deleteDiscussion({{ $disc->id }})" 
                                                        wire:confirm="Yakin ingin menghapus pesan diskusi ini?" 
                                                        class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" 
                                                        title="Hapus Pesan">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Bottom Action: Tombol Balas -->
                                    <div class="ml-12 flex items-center gap-4">
                                        <button type="button" 
                                                wire:click="startReply({{ $disc->id }})" 
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2347C4] hover:text-[#1A3596] transition-colors">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 17 4 12 9 7"></polyline><path d="M20 18v-2a4 4 0 0 0-4-4H4"></path></svg>
                                            <span>Tanggapi / Balas</span>
                                            @if($disc->replies->count() > 0)
                                                <span class="px-1.5 py-0.2 bg-[#EEF2FF] text-[#2347C4] text-[10px] rounded-full font-bold">({{ $disc->replies->count() }})</span>
                                            @endif
                                        </button>
                                    </div>

                                    <!-- Inline Form Balas (Jika sedang membalas post ini) -->
                                    @if($replyingToId === $disc->id)
                                        <div class="ml-8 sm:ml-12 p-3.5 bg-[#EEF2FF] rounded-xl border border-[#C7D2FE] flex flex-col gap-2.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-[#1E3A8A]">Tulis Tanggapan untuk {{ $disc->user?->name }}:</span>
                                                <button type="button" wire:click="cancelReply" class="text-xs text-gray-500 hover:text-gray-800 font-semibold">Tutup</button>
                                            </div>
                                            <textarea wire:model="replyBody" rows="2" 
                                                      placeholder="Tulis balasan penjelasan, panduan, atau apresiasi..." 
                                                      class="w-full text-xs rounded-xl border-[#C7D2FE] bg-white text-[#15191F] p-3 focus:border-[#2347C4] focus:ring-[#2347C4]"></textarea>
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" wire:click="cancelReply" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-gray-800">
                                                    Batal
                                                </button>
                                                <button type="button" wire:click="sendReply({{ $disc->id }})" class="px-4 py-1.5 bg-[#2347C4] text-white text-xs font-bold rounded-lg hover:bg-[#1A3596] shadow-sm flex items-center gap-1.5 transition-colors" style="color: #FFFFFF !important;">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #FFFFFF !important; stroke: #FFFFFF !important;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                                    <span style="color: #FFFFFF !important;">Kirim Balasan</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- List Tanggapan / Replies -->
                                    @if($disc->replies->isNotEmpty())
                                        <div class="ml-6 sm:ml-12 flex flex-col gap-2.5 pl-3 border-l-2 border-[#C7D2FE] pt-1">
                                            @foreach($disc->replies as $reply)
                                                <div class="flex items-start justify-between gap-2.5 p-3 rounded-xl border {{ $reply->user?->role === 'guru' ? 'bg-[#F0F4FF] border-[#C7D2FE]' : 'bg-[#F8F9FA] border-[#E2E4DF]' }}">
                                                    <div class="flex items-start gap-2.5">
                                                        <span class="w-7 h-7 rounded-full {{ $reply->user?->role === 'guru' ? 'bg-[#2347C4] text-white' : 'bg-gray-200 text-gray-700' }} font-bold text-[11px] flex items-center justify-center shrink-0">
                                                            {{ $reply->user ? $reply->user->initials() : 'U' }}
                                                        </span>
                                                        <div>
                                                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                                <span class="text-xs font-bold text-[#15191F]">{{ $reply->user ? $reply->user->name : 'Pengguna' }}</span>
                                                                @if($reply->user?->role === 'guru')
                                                                    <span class="px-1.5 py-0.2 bg-[#2347C4] text-white text-[9px] font-bold rounded-full">
                                                                        Guru Pengampu
                                                                    </span>
                                                                @endif
                                                                <span class="text-[10px] text-[#4A515C]">• {{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>
                                                            <p class="text-xs text-[#2F353E] leading-relaxed whitespace-pre-line">{{ $reply->body }}</p>
                                                        </div>
                                                    </div>

                                                    @if(auth()->check() && (in_array(auth()->user()->role, ['guru', 'admin']) || auth()->id() === $reply->user_id))
                                                        <button type="button" 
                                                                wire:click="deleteDiscussion({{ $reply->id }})" 
                                                                wire:confirm="Hapus tanggapan ini?" 
                                                                class="p-1 text-gray-400 hover:text-red-600 transition-colors" 
                                                                title="Hapus Tanggapan">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                </div>
                            @empty
                                <div class="text-center py-10 flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-full bg-[#EEF2FF] text-[#2347C4] flex items-center justify-center">
                                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-[#15191F]">Belum ada diskusi untuk materi ini</p>
                                    <p class="text-xs text-[#4A515C] max-w-sm">Jadilah yang pertama menuliskan pertanyaan atau pemantik diskusi di atas!</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if($tab === 'catatan')
                    <section class="bg-white border border-[#E2E4DF] rounded-2xl p-6 shadow-sm flex flex-col gap-4">
                        <div>
                            <h2 class="text-sm font-bold text-[#15191F]">Catatan Pribadi</h2>
                            <p class="text-xs text-[#4A515C] mt-0.5">
                                @if(auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']))
                                    Catatan pengajaran atau rangkuman pribadi guru untuk materi ini (tersimpan aman dan hanya bisa dibaca oleh Anda sendiri).
                                @else
                                    Catatan ini tersimpan aman dan hanya bisa dibaca oleh kamu sendiri.
                                @endif
                            </p>
                        </div>

                        @if(session()->has('success'))
                            <div class="p-3 bg-[#E8F5E9] text-[#2E7D32] border border-[#A5D6A7] rounded-xl text-xs font-bold">
                                {{ session('success') }}
                            </div>
                        @endif

                        <textarea wire:model="noteBody" rows="10" 
                                  placeholder="{{ auth()->check() && in_array(auth()->user()->role, ['guru', 'admin']) ? 'Tulis catatan rencana pengajaran, poin penting diskusi kelas, atau rangkuman materi...' : 'Tulis ringkasan rumus, algoritma, atau konsep kunci yang kamu pelajari dari materi ini...' }}" 
                                  class="w-full text-sm rounded-xl border-[#E2E4DF] bg-[#F8F9FA] text-[#15191F] focus:border-[#2347C4] focus:ring-[#2347C4] p-3.5"></textarea>

                        <div class="flex justify-end">
                            <button type="button" wire:click="saveNote" class="px-5 py-2 bg-[#2347C4] text-white font-bold text-xs rounded-xl hover:bg-[#1A3596] shadow-sm">
                                Simpan Catatan
                            </button>
                        </div>
                    </section>
                @endif
            </main>
        </div>
    @endif

    <!-- Script Fullscreen Native JS -->
    <script>
        function toggleFullscreen(elementId) {
            const elem = document.getElementById(elementId);
            if (!elem) return;
            if (!document.fullscreenElement) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</div>
