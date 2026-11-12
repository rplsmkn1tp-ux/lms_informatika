<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <a href="{{ route('guru.tugas.index') }}" wire:navigate class="hover:text-[#2347C4] flex items-center gap-1 font-medium transition-colors">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Daftar Tugas
            </a>
            <span>/</span>
            <span class="text-zinc-800 font-semibold truncate max-w-xs">{{ $assessment->title }}</span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Unduh ZIP -->
            <a href="{{ route('guru.tugas.download-zip', $assessment->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 !text-white font-semibold text-xs rounded-xl shadow-sm transition-all" style="color: #ffffff !important;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white" style="color: #ffffff !important;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span class="text-white" style="color: #ffffff !important;">Unduh Semua Jawaban (ZIP)</span>
            </a>

            <!-- Ubah Tugas -->
            <a href="{{ route('guru.tugas.edit', $assessment->id) }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-zinc-50 text-zinc-700 border border-zinc-200 font-semibold text-xs rounded-xl transition-all">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Ubah Tugas
            </a>
        </div>
    </div>

    <!-- Alert Flash -->
    @if(session()->has('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-lg leading-none">&times;</button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-lg leading-none">&times;</button>
        </div>
    @endif

    <!-- Card Info Tugas & Instruksi -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6 sm:p-7 mb-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 border-b border-zinc-100 pb-5">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    @if($assessment->status === 'draft')
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200">DRAFT</span>
                    @elseif($dueAtCarbon && $dueAtCarbon->lt(now()))
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">LEWAT TENGGAT</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">AKTIF</span>
                    @endif
                    <span class="text-xs text-zinc-400">&bull;</span>
                    @if($assessment->isGroup())
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                            TUGAS KELOMPOK (Maks. {{ $assessment->max_group_members ?? 4 }} Siswa)
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200">
                            INDIVIDU
                        </span>
                    @endif
                    <span class="text-xs text-zinc-400">&bull;</span>
                    <span class="text-xs font-semibold text-zinc-600">Nilai Maks: {{ $assessment->max_score }}</span>
                </div>
                <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">{{ $assessment->title }}</h1>
                @if($assessment->lesson || $assessment->effectiveMeetingNo())
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        @if($assessment->lesson)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-[#2347C4] border border-blue-200/60">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                Materi: {{ $assessment->lesson->title }}
                            </span>
                        @endif
                        @if($assessment->effectiveMeetingNo())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Pertemuan ke-{{ $assessment->effectiveMeetingNo() }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Detail Tenggat & Kebijakan -->
            <div class="flex flex-wrap md:flex-col items-start md:items-end gap-1.5 text-xs text-zinc-500">
                @if($dueAtCarbon)
                    <div>Tenggat: <strong class="text-zinc-800">{{ $dueAtCarbon->translatedFormat('l, d F Y, H:i') }} WIB</strong></div>
                @else
                    <div>Tenggat: <strong class="text-zinc-800">Tanpa batas waktu</strong></div>
                @endif
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center gap-1 text-[11px] {{ $assessment->allow_late ? 'text-emerald-700' : 'text-zinc-400' }}">
                        {{ $assessment->allow_late ? '✓ Terlambat Diizinkan' : '✗ Tidak Boleh Terlambat' }}
                    </span>
                    <span>&bull;</span>
                    <span class="inline-flex items-center gap-1 text-[11px] {{ $assessment->allow_resubmit ? 'text-emerald-700' : 'text-zinc-400' }}">
                        {{ $assessment->allow_resubmit ? '✓ Kumpul Ulang Diizinkan' : '✗ Tidak Boleh Kumpul Ulang' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Instruksi -->
        @if($assessment->instructions)
            <div class="pt-4 text-sm text-zinc-700 leading-relaxed whitespace-pre-line">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider block mb-1">Instruksi:</span>
                {{ $assessment->instructions }}
            </div>
        @endif

        <!-- Lampiran Guru -->
        @if(!empty($assessment->teacher_attachments))
            <div class="pt-4 border-t border-zinc-100 mt-4">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider block mb-2">Lampiran dari Guru:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($assessment->teacher_attachments as $idx => $att)
                        @if(($att['type'] ?? '') === 'file')
                            <a href="{{ route('guru.tugas.download-attachment', [$assessment->id, $idx]) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 text-xs font-semibold text-zinc-700 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#2347C4]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                <span>{{ $att['name'] }}</span>
                            </a>
                        @else
                            <a href="{{ $att['url'] }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-xs font-semibold text-amber-800 transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                <span>{{ $att['name'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Statistik Bar -->
    @if($assessment->isGroup())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Total Kelompok</span>
                <span class="text-2xl font-extrabold text-zinc-900 mt-1 block">{{ $statTotalGroups }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Mengumpulkan</span>
                <span class="text-2xl font-extrabold text-blue-600 mt-1 block">{{ $statSubmittedGroups }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Belum Kumpul</span>
                <span class="text-2xl font-extrabold text-zinc-500 mt-1 block">{{ $statTotalGroups - $statSubmittedGroups }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Sudah Dinilai</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">{{ $statGradedGroups }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Belum Punya Kelompok</span>
                <span class="text-2xl font-extrabold {{ $unassignedStudents->count() > 0 ? 'text-amber-600' : 'text-zinc-500' }} mt-1 block">{{ $unassignedStudents->count() }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Rata-rata Nilai</span>
                <span class="text-2xl font-extrabold text-amber-600 mt-1 block">{{ $averageScore }}</span>
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Total Siswa</span>
                <span class="text-2xl font-extrabold text-zinc-900 mt-1 block">{{ $statTotal }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Mengumpulkan</span>
                <span class="text-2xl font-extrabold text-blue-600 mt-1 block">{{ $statSubmitted }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Belum Kumpul</span>
                <span class="text-2xl font-extrabold text-zinc-500 mt-1 block">{{ $statTotal - $statSubmitted }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Terlambat</span>
                <span class="text-2xl font-extrabold text-rose-600 mt-1 block">{{ $statLate }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Sudah Dinilai</span>
                <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">{{ $statGraded }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-zinc-200 shadow-sm text-center">
                <span class="text-xs text-zinc-500 font-medium block">Rata-rata Nilai</span>
                <span class="text-2xl font-extrabold text-amber-600 mt-1 block">{{ $averageScore }}</span>
            </div>
        </div>
    @endif

    <!-- Tab Switcher untuk Tugas Kelompok -->
    @if($assessment->isGroup())
        <div class="flex items-center gap-2 mb-4 border-b border-zinc-200 pb-3 flex-wrap">
            <button 
                type="button" 
                wire:click="switchTab('kelompok')" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'kelompok' ? 'shadow-sm' : 'hover:bg-zinc-100 border border-zinc-200' }}"
                style="{{ $activeTab === 'kelompok' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #FFFFFF; color: #52525B;' }}"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Daftar Kelompok ({{ $groups->count() }})</span>
            </button>
            <button 
                type="button" 
                wire:click="switchTab('belum_kelompok')" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'belum_kelompok' ? 'shadow-sm' : 'hover:bg-zinc-100 border border-zinc-200' }}"
                style="{{ $activeTab === 'belum_kelompok' ? 'background: #D97706; color: #FFFFFF;' : 'background: #FFFFFF; color: #52525B;' }}"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Siswa Belum Ada Kelompok ({{ $unassignedStudents->count() }})</span>
            </button>
            <button 
                type="button" 
                wire:click="switchTab('siswa')" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'siswa' ? 'shadow-sm' : 'hover:bg-zinc-100 border border-zinc-200' }}"
                style="{{ $activeTab === 'siswa' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #FFFFFF; color: #52525B;' }}"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Semua Siswa (Perorangan)</span>
            </button>
        </div>
    @endif

    @if($assessment->isGroup() && $activeTab === 'kelompok')
        <!-- Daftar Kelompok Container -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden mb-8">
            <div class="p-4 border-b border-zinc-200 bg-zinc-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900">Daftar Kelompok Belajar</h3>
                    <span class="text-xs text-zinc-500">Kelompok dibentuk oleh siswa dengan ketua sebagai pengunggah tugas.</span>
                </div>
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedClassroomId" class="px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-xl text-zinc-700 font-medium">
                        <option value="">Semua Kelas ({{ $availableClassrooms->count() }} Kelas)</option>
                        @foreach($availableClassrooms as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600">
                    <thead class="bg-zinc-50 text-[11px] uppercase font-semibold text-zinc-500 tracking-wider border-b border-zinc-200">
                        <tr>
                            <th class="py-3 px-5">Nama Kelompok & Kelas</th>
                            <th class="py-3 px-4">Ketua & Anggota</th>
                            <th class="py-3 px-4">Laporan Peran / Kontribusi</th>
                            <th class="py-3 px-4">Status Pengumpulan</th>
                            <th class="py-3 px-4 text-center">Nilai Rata-rata</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($groups as $grp)
                            @php
                                $sub = $grp->submissions->first();
                                $isSubmitted = $sub && $sub->submitted_at && in_array($sub->status, [\App\Models\Submission::STATUS_SUBMITTED, \App\Models\Submission::STATUS_GRADED, \App\Models\Submission::STATUS_REVISION]);
                                $isLate = $sub && ($sub->is_late || ($dueAtCarbon && $sub->submitted_at && \Carbon\Carbon::parse($sub->submitted_at)->setTimezone('Asia/Jakarta')->gt($dueAtCarbon)));
                                $isGraded = $sub && $sub->status === \App\Models\Submission::STATUS_GRADED;
                                $isRevision = $sub && $sub->status === \App\Models\Submission::STATUS_REVISION;

                                $groupScores = $grp->submissions->where('status', \App\Models\Submission::STATUS_GRADED)->pluck('score')->filter(fn($s) => $s !== null);
                                $avgScore = $groupScores->count() > 0 ? round($groupScores->avg(), 1) : null;
                            @endphp
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-zinc-900 text-sm flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                        <span>{{ $grp->name }}</span>
                                    </div>
                                    <div class="text-xs text-zinc-500 mt-0.5">
                                        Kelas: <strong class="text-zinc-700">{{ $grp->classroom?->name ?? '-' }}</strong>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 font-bold text-purple-800 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 text-[11px]">
                                            👑 {{ $grp->leader?->name ?? 'Belum ditentukan' }} (Ketua)
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-1 text-[11px] text-zinc-600">
                                        @foreach($grp->members as $mem)
                                            @if((int)$mem->id !== (int)$grp->leader_id)
                                                <span class="px-1.5 py-0.5 bg-zinc-100 rounded text-zinc-600">{{ $mem->name }}</span>
                                            @endif
                                        @endforeach
                                        <span class="text-zinc-400">({{ $grp->members->count() }}/{{ $assessment->max_group_members ?? 4 }} siswa)</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if(!empty($grp->contribution_notes))
                                        <div class="max-w-xs">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                                                ✓ Ada Catatan
                                            </span>
                                            <p class="text-[11px] text-zinc-600 line-clamp-2 leading-relaxed" title="{{ $grp->contribution_notes }}">
                                                {{ $grp->contribution_notes }}
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">Tidak ada catatan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if(!$sub || !$sub->submitted_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                                            Belum mengumpulkan
                                        </span>
                                    @elseif($isGraded)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ Sudah dinilai
                                        </span>
                                    @elseif($isRevision)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            ⚠ Perlu revisi
                                        </span>
                                    @elseif($isLate)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Sudah mengumpulkan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($avgScore !== null)
                                        <span class="font-extrabold text-sm text-zinc-900">{{ $avgScore }}</span>
                                        <span class="text-[10px] text-zinc-400">/{{ $assessment->max_score }}</span>
                                    @else
                                        <span class="text-zinc-400 font-mono">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        wire:click="openGroupGradingModal({{ $grp->id }})" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm hover:opacity-95"
                                        style="{{ $isGraded ? 'background: #F4F5F2; color: #374151; border: 1px solid #D1D5DB;' : 'background: #6B21A8; color: #FFFFFF; border: 1px solid #581C87;' }}"
                                    >
                                        @if($isGraded)
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                            <span>Edit Nilai Kelompok</span>
                                        @elseif($isSubmitted)
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                            <span>Periksa & Nilai Kelompok</span>
                                        @else
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                            <span>Beri Nilai Kelompok</span>
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-zinc-400 text-xs italic">
                                    Belum ada kelompok yang dibentuk untuk tugas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($assessment->isGroup() && $activeTab === 'belum_kelompok')
        <!-- Siswa Belum Ada Kelompok Container -->
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden mb-8">
            <div class="p-5 border-b border-zinc-200 bg-amber-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-950">Siswa Belum Memiliki Kelompok ({{ $unassignedStudents->count() }} Siswa)</h3>
                        <p class="text-xs text-amber-800 mt-0.5">
                            Gunakan menu ini untuk memasukkan siswa yang belum memiliki kelompok (misalnya siswa yang baru hadir/absen) ke kelompok yang sudah ada.
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-600">
                    <thead class="bg-zinc-50 text-[11px] uppercase font-semibold text-zinc-500 tracking-wider border-b border-zinc-200">
                        <tr>
                            <th class="py-3 px-5">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">NISN</th>
                            <th class="py-3 px-5 text-right">Pilih Kelompok Tujuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($unassignedStudents as $unassigned)
                            <tr class="hover:bg-zinc-50/60 transition-colors" x-data="{ targetGroup: '' }">
                                <td class="py-3.5 px-5 font-bold text-zinc-900 text-sm">
                                    {{ $unassigned->name }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-medium text-zinc-700">
                                    {{ $unassigned->classrooms->first()?->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-zinc-500">
                                    {{ $unassigned->nisn ?? '-' }}
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2 justify-end">
                                        <select x-model="targetGroup" class="px-2.5 py-1.5 text-xs bg-white border border-zinc-300 rounded-lg text-zinc-700">
                                            <option value="">-- Pilih Kelompok Tujuan --</option>
                                            @foreach($groups as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->members->count() }}/{{ $assessment->max_group_members ?? 4 }} anggota)</option>
                                            @endforeach
                                        </select>
                                        <button 
                                            type="button" 
                                            @click="if(targetGroup) { $wire.assignStudentToGroup({{ $unassigned->id }}, targetGroup) }" 
                                            :disabled="!targetGroup"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold transition-all cursor-pointer shadow-sm"
                                            style="background: #059669; color: #FFFFFF; border: 1px solid #047857;"
                                        >
                                            + Masukkan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-emerald-600 font-semibold text-xs">
                                    ✓ Semua siswa telah terdaftar dalam kelompok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!$assessment->isGroup() || $activeTab === 'siswa')
    <!-- Filter & Tabel Siswa -->
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden mb-8">
        <!-- Filter Controls -->
        <div class="p-4 border-b border-zinc-200 bg-zinc-50/50 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex-1 w-full relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari siswa berdasarkan nama atau NISN..." 
                    class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20"
                >
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <!-- Dropdown Filter Kelas -->
                <select wire:model.live="selectedClassroomId" class="px-3 py-2 text-xs bg-white border border-zinc-200 rounded-xl text-zinc-700 font-medium">
                    <option value="">Semua Kelas ({{ $availableClassrooms->count() }} Kelas)</option>
                    @foreach($availableClassrooms as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                    @endforeach
                </select>

                <!-- Filter Status Siswa -->
                <select wire:model.live="statusFilter" class="px-3 py-2 text-xs bg-white border border-zinc-200 rounded-xl text-zinc-700">
                    <option value="">Semua Status Siswa</option>
                    <option value="belum">Belum Mengumpulkan</option>
                    <option value="dikumpulkan">Sudah Mengumpulkan</option>
                    <option value="terlambat">Terlambat</option>
                    <option value="dinilai">Sudah Dinilai</option>
                    <option value="revisi">Perlu Revisi</option>
                </select>
            </div>
        </div>

        <!-- Tabel Siswa -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-600">
                <thead class="bg-zinc-50 text-[11px] uppercase font-semibold text-zinc-500 tracking-wider border-b border-zinc-200">
                    <tr>
                        <th class="py-3 px-5">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Status Pengumpulan</th>
                        <th class="py-3 px-4">Waktu Kumpul</th>
                        <th class="py-3 px-4 text-center">Nilai</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($students as $st)
                        @php
                            $sub = $submissions->get($st->id);
                            $isSubmitted = $sub && $sub->submitted_at && in_array($sub->status, [\App\Models\Submission::STATUS_SUBMITTED, \App\Models\Submission::STATUS_GRADED, \App\Models\Submission::STATUS_REVISION]);
                            $isLate = $sub && ($sub->is_late || ($dueAtCarbon && $sub->submitted_at && \Carbon\Carbon::parse($sub->submitted_at)->setTimezone('Asia/Jakarta')->gt($dueAtCarbon)));
                            $isGraded = $sub && $sub->status === \App\Models\Submission::STATUS_GRADED;
                            $isRevision = $sub && $sub->status === \App\Models\Submission::STATUS_REVISION;
                        @endphp
                        <tr class="hover:bg-zinc-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-600 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ substr($st->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-zinc-900 text-sm">{{ $st->name }}</div>
                                        <div class="text-[11px] text-zinc-400 font-mono">NISN: {{ $st->nisn ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium text-zinc-700">
                                {{ $st->classrooms->first()?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if(!$sub || !$sub->submitted_at || $sub->status === 'dikerjakan')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                                        Belum mengumpulkan
                                    </span>
                                @elseif($isGraded)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Sudah dinilai
                                    </span>
                                @elseif($isRevision)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        ⚠ Perlu revisi
                                    </span>
                                @elseif($isLate)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        Terlambat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        Sudah mengumpulkan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                                @if($sub && $sub->submitted_at)
                                    <span class="text-zinc-800 font-medium">
                                        {{ \Carbon\Carbon::parse($sub->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    </span>
                                @else
                                    <span class="text-zinc-400 italic">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($isGraded && $sub->score !== null)
                                    <span class="font-extrabold text-sm text-zinc-900">{{ round($sub->score, 1) }}</span>
                                    <span class="text-[10px] text-zinc-400">/{{ $assessment->max_score }}</span>
                                @else
                                    <span class="text-zinc-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <button 
                                    type="button" 
                                    wire:click="openGradingModal({{ $st->id }})" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer shadow-sm"
                                    style="{{ $isGraded ? 'background: #F4F5F2; color: #374151; border: 1px solid #D1D5DB;' : 'background: #2347C4; color: #FFFFFF; border: 1px solid #1A3596;' }}"
                                >
                                    @if($isGraded)
                                        Edit Nilai
                                    @elseif($isSubmitted)
                                        Periksa & Nilai
                                    @else
                                        Beri Nilai
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-zinc-400 text-xs italic">
                                Tidak ada siswa yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-zinc-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>
    @endif

    <!-- Modal Penilaian -->
    @if($showGradingModal && $selectedStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white rounded-2xl shadow-2xl border border-zinc-200 max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200 my-auto">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 border-b border-zinc-100 flex items-center justify-between bg-zinc-50/70 flex-shrink-0">
                    <div>
                        <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider block">Penilaian Tugas</span>
                        <h3 class="text-lg font-bold text-zinc-900">{{ $selectedStudent->name }}</h3>
                        <span class="text-xs text-zinc-400 font-mono">NISN: {{ $selectedStudent->nisn ?? '-' }}</span>
                    </div>
                    <button type="button" wire:click="closeGradingModal" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 transition-colors">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 sm:p-6 space-y-6 overflow-y-auto flex-1">
                    <!-- Status & Riwayat Pengumpulan Siswa -->
                    <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-zinc-600">Waktu Pengumpulan:</span>
                                @if($selectedSubmission && $selectedSubmission->submitted_at)
                                    <span class="font-medium text-zinc-800">
                                        {{ \Carbon\Carbon::parse($selectedSubmission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y, H:i') }} WIB
                                    </span>
                                @else
                                    <span class="text-zinc-500 italic">Belum dikumpulkan</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5">
                                @if($selectedSubmission && $selectedSubmission->submitted_at)
                                    @if($selectedSubmission->is_late)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            Tepat Waktu
                                        </span>
                                    @endif

                                    @if($selectedSubmission->attempt > 1)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                            Kumpul Ulang (Pengumpulan ke-{{ $selectedSubmission->attempt }})
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                            Pengumpulan ke-1
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Riwayat Kumpul Ulang Jika Ada Lebih Dari 1 Pengumpulan -->
                        @if(isset($submissionHistory) && count($submissionHistory) > 1)
                            <div class="pt-2 border-t border-zinc-200/60 text-xs">
                                <span class="font-semibold text-zinc-600 block mb-1.5">Riwayat Kumpul Ulang:</span>
                                <div class="space-y-1">
                                    @foreach($submissionHistory as $hist)
                                        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $hist->id === $selectedSubmission->id ? 'bg-blue-50/70 border border-blue-200' : 'bg-white border border-zinc-200/60' }} text-[11px]">
                                            <span class="font-medium text-zinc-700">
                                                Pengumpulan ke-{{ $hist->attempt }}
                                                @if($hist->id === $selectedSubmission->id)
                                                    <strong class="text-[#2347C4]">(Versi Terakhir)</strong>
                                                @endif
                                            </span>
                                            <span class="text-zinc-500">
                                                {{ $hist->submitted_at ? \Carbon\Carbon::parse($hist->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-' }} WIB
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Jawaban Siswa -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Jawaban Siswa</h4>
                            <span class="text-[11px] text-zinc-400">
                                Tipe yang diminta: 
                                {{ collect($assessment->submission_types ?? ['file'])->map(fn($t) => match($t){ 'file'=>'Berkas', 'text'=>'Teks', 'link'=>'Tautan', default=>$t })->join(', ') }}
                            </span>
                        </div>

                        <!-- 1. Berkas / File -->
                        <div class="p-3.5 rounded-xl border border-zinc-200 bg-white space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-700 flex items-center gap-1.5">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    Berkas File
                                </span>
                                @if($selectedSubmission && $selectedSubmission->file_path)
                                    <span class="text-[11px] text-zinc-500 font-mono">{{ $selectedSubmission->file_size_formatted ?? '' }}</span>
                                @endif
                            </div>

                            @if($selectedSubmission && $selectedSubmission->file_path)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-zinc-100">
                                    <div class="truncate max-w-sm">
                                        <span class="text-xs font-semibold text-zinc-900 block truncate" title="{{ $selectedSubmission->file_name }}">
                                            {{ $selectedSubmission->file_name }}
                                        </span>
                                        <span class="text-[11px] text-zinc-400">Tersimpan aman di penyimpanan sistem</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if($selectedSubmission->isPreviewable())
                                            <a 
                                                href="{{ route('guru.tugas.submission.preview', $selectedSubmission->id) }}" 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold transition-colors"
                                            >
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                Lihat (Preview)
                                            </a>
                                        @endif
                                        <a 
                                            href="{{ route('guru.tugas.submission.file', $selectedSubmission->id) }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#2347C4] text-xs font-semibold transition-colors"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                            Unduh
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="pt-2 border-t border-zinc-100 text-xs text-zinc-400 italic">
                                    Tidak ada
                                </div>
                            @endif
                        </div>

                        <!-- 2. Teks Jawaban -->
                        <div class="p-3.5 rounded-xl border border-zinc-200 bg-white space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-700 flex items-center gap-1.5">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600"><line x1="17" y1="10" x2="3" y2="10"></line><line x1="21" y1="6" x2="3" y2="6"></line><line x1="21" y1="14" x2="3" y2="14"></line><line x1="17" y1="18" x2="3" y2="18"></line></svg>
                                    Teks Jawaban
                                </span>
                            </div>

                            @if($selectedSubmission && filled(trim($selectedSubmission->text_content ?? '')))
                                <div class="pt-2 border-t border-zinc-100">
                                    <div class="p-3 rounded-lg bg-zinc-50 border border-zinc-200/80 text-xs text-zinc-800 leading-relaxed whitespace-pre-line break-words max-h-48 overflow-y-auto">
                                        {!! nl2br(e($selectedSubmission->text_content)) !!}
                                    </div>
                                </div>
                            @else
                                <div class="pt-2 border-t border-zinc-100 text-xs text-zinc-400 italic">
                                    Tidak ada
                                </div>
                            @endif
                        </div>

                        <!-- 3. Tautan URL -->
                        <div class="p-3.5 rounded-xl border border-zinc-200 bg-white space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-700 flex items-center gap-1.5">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-600"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                    Tautan (URL)
                                </span>
                            </div>

                            @if($selectedSubmission && filled(trim($selectedSubmission->link_url ?? '')))
                                @php
                                    $rawUrl = trim($selectedSubmission->link_url);
                                    $isHttp = preg_match('#^https?://#i', $rawUrl);
                                @endphp
                                <div class="pt-2 border-t border-zinc-100 flex items-center justify-between gap-2">
                                    @if($isHttp)
                                        <a 
                                            href="{{ $rawUrl }}" 
                                            target="_blank" 
                                            rel="noopener noreferrer" 
                                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 hover:underline max-w-md truncate font-medium"
                                        >
                                            <span class="truncate">{{ $rawUrl }}</span>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    @else
                                        <span class="text-xs text-zinc-600 font-mono break-all">{{ e($rawUrl) }} <span class="text-rose-500 font-sans text-[11px]">(Skema tidak aman / bukan http/https)</span></span>
                                    @endif
                                </div>
                            @else
                                <div class="pt-2 border-t border-zinc-100 text-xs text-zinc-400 italic">
                                    Tidak ada
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Form Input Nilai & Umpan Balik -->
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="grade_score" class="text-sm font-semibold text-zinc-800">Nilai Siswa (0 - {{ $assessment->max_score }}) <span class="text-rose-500">*</span></label>
                                <span class="text-xs text-zinc-400">Skala maksimal: {{ $assessment->max_score }}</span>
                            </div>
                            <input 
                                type="number" 
                                id="grade_score"
                                wire:model="score" 
                                min="0" 
                                max="{{ $assessment->max_score }}" 
                                step="0.5" 
                                placeholder="Contoh: 85" 
                                class="w-full px-4 py-2 text-base font-bold text-zinc-900 bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4]"
                            >
                            @error('score') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="grade_feedback" class="block text-sm font-semibold text-zinc-800 mb-1">Komentar & Umpan Balik Guru (Opsional)</label>
                            <textarea 
                                id="grade_feedback"
                                wire:model="feedback" 
                                rows="3" 
                                placeholder="Tulis catatan, apresiasi, atau saran perbaikan untuk siswa..."
                                class="w-full px-4 py-2.5 text-xs bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#2347C4]/20 focus:border-[#2347C4]"
                            ></textarea>
                            @error('feedback') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-zinc-100 bg-zinc-50 flex items-center justify-between gap-3 flex-shrink-0">
                    <div>
                        @if($selectedSubmission && $selectedSubmission->submitted_at && in_array($selectedSubmission->status, [\App\Models\Submission::STATUS_SUBMITTED, \App\Models\Submission::STATUS_GRADED]))
                            <button 
                                type="button" 
                                wire:click="requestRevision" 
                                wire:confirm="Kirim permintaan revisi ke siswa ini agar dapat mengumpulkan ulang jawabannya?"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold transition-colors"
                            >
                                ⚠ Minta Revisi
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="closeGradingModal" 
                            class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-100 border border-zinc-200 text-zinc-700 text-xs font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            wire:click="saveGrade" 
                            class="px-5 py-2 rounded-xl bg-[#2347C4] hover:bg-[#1A3596] text-white text-xs font-bold shadow-sm transition-colors cursor-pointer"
                        >
                            Simpan Nilai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Penilaian Tugas Kelompok -->
    @if($showGroupGradingModal && $selectedGroup)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="bg-white rounded-2xl shadow-2xl border border-zinc-200 max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200 my-auto">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 border-b border-zinc-100 flex items-center justify-between bg-purple-50/80 flex-shrink-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-purple-700 uppercase tracking-wider">Penilaian Tugas Kelompok</span>
                            <span class="text-xs text-zinc-400">&bull;</span>
                            <span class="text-xs text-zinc-600 font-semibold">Kelas: {{ $selectedGroup->classroom?->name ?? '-' }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900">{{ $selectedGroup->name }}</h3>
                        <span class="text-xs text-zinc-500">Ketua Kelompok: <strong>{{ $selectedGroup->leader?->name ?? '-' }}</strong> ({{ $selectedGroup->members->count() }} Anggota)</span>
                    </div>
                    <button type="button" wire:click="closeGroupGradingModal" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 transition-colors" title="Tutup Modal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-6 space-y-5 overflow-y-auto flex-1">
                    @if($errors->any())
                        <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium flex items-start gap-2.5">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-500 flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>
                                <span class="font-bold block">Terdapat kesalahan pengisian nilai:</span>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Status Pengumpulan -->
                    <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-zinc-600">Waktu Pengumpulan:</span>
                            @if($groupLeaderSubmission && $groupLeaderSubmission->submitted_at)
                                <span class="font-medium text-zinc-800">
                                    {{ \Carbon\Carbon::parse($groupLeaderSubmission->submitted_at)->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y, H:i') }} WIB
                                </span>
                            @else
                                <span class="text-zinc-500 italic">Belum dikumpulkan oleh ketua</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5">
                            @if($groupLeaderSubmission && $groupLeaderSubmission->submitted_at)
                                @if($groupLeaderSubmission->is_late)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        Terlambat
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        Tepat Waktu
                                    </span>
                                @endif

                                @if($groupLeaderSubmission->attempt > 1)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                        Pengumpulan ke-{{ $groupLeaderSubmission->attempt }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                        Pengumpulan ke-1
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Laporan Kontribusi & Peran Anggota dari Ketua -->
                    <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                Laporan Peran & Kontribusi Anggota (dari Ketua):
                            </span>
                            @if(!empty($selectedGroup->contribution_notes))
                                <span class="text-[11px] font-semibold text-purple-700 bg-purple-100 px-2 py-0.5 rounded">Tersedia</span>
                            @endif
                        </div>

                        @if(!empty($selectedGroup->contribution_notes))
                            <div class="p-3 bg-white rounded-lg border border-purple-200/80 text-xs text-purple-950 whitespace-pre-line leading-relaxed">
                                {!! nl2br(e($selectedGroup->contribution_notes)) !!}
                            </div>
                        @else
                            <div class="text-xs text-purple-700/80 italic">
                                Ketua kelompok tidak mencantumkan catatan pembagian tugas / peran anggota.
                            </div>
                        @endif
                    </div>

                    <!-- Berkas & Jawaban yang Dikumpulkan Kelompok -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Berkas / Jawaban Kelompok</h4>

                        <!-- 1. File Upload -->
                        <div class="p-3.5 rounded-xl border border-zinc-200 bg-white space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-700 flex items-center gap-1.5">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    Berkas File Kelompok
                                </span>
                                @if($groupLeaderSubmission && $groupLeaderSubmission->file_path)
                                    <span class="text-[11px] text-zinc-500 font-mono">{{ $groupLeaderSubmission->file_size_formatted ?? '' }}</span>
                                @endif
                            </div>

                            @if($groupLeaderSubmission && $groupLeaderSubmission->file_path)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-zinc-100">
                                    <div class="truncate max-w-sm">
                                        <span class="text-xs font-semibold text-zinc-900 block truncate" title="{{ $groupLeaderSubmission->file_name }}">
                                            {{ $groupLeaderSubmission->file_name }}
                                        </span>
                                        <span class="text-[11px] text-zinc-400">Diunggah oleh Ketua Kelompok</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if($groupLeaderSubmission->isPreviewable())
                                            <a 
                                                href="{{ route('guru.tugas.submission.preview', $groupLeaderSubmission->id) }}" 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-semibold transition-colors"
                                            >
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                Lihat (Preview)
                                            </a>
                                        @endif
                                        <a 
                                            href="{{ route('guru.tugas.submission.file', $groupLeaderSubmission->id) }}" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#2347C4] text-xs font-semibold transition-colors"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                            Unduh
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="pt-2 border-t border-zinc-100 text-xs text-zinc-400 italic">
                                    Tidak ada file yang diunggah
                                </div>
                            @endif
                        </div>

                        <!-- 2. Teks & Tautan -->
                        @if($groupLeaderSubmission && (filled(trim($groupLeaderSubmission->text_content ?? '')) || filled(trim($groupLeaderSubmission->link_url ?? ''))))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @if(filled(trim($groupLeaderSubmission->text_content ?? '')))
                                    <div class="p-3.5 rounded-xl border border-zinc-200 bg-white space-y-1.5">
                                        <span class="text-xs font-bold text-zinc-700 block">Teks Jawaban:</span>
                                        <div class="p-2.5 rounded bg-zinc-50 text-xs text-zinc-800 leading-relaxed whitespace-pre-line max-h-32 overflow-y-auto">
                                            {!! nl2br(e($groupLeaderSubmission->text_content)) !!}
                                        </div>
                                    </div>
                                @endif
                                @if(filled(trim($groupLeaderSubmission->link_url ?? '')))
                                    <div class="p-3.5 rounded-xl border border-zinc-200 bg-white space-y-1.5">
                                        <span class="text-xs font-bold text-zinc-700 block">Tautan (URL):</span>
                                        <a href="{{ $groupLeaderSubmission->link_url }}" target="_blank" class="text-xs text-blue-600 hover:underline block truncate font-medium">
                                            {{ $groupLeaderSubmission->link_url }} &nearr;
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Pengaturan Nilai Standar Kelompok -->
                    <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <label for="default_group_score" class="text-xs font-bold text-zinc-800 block">Nilai Standar Kelompok (0 - {{ $assessment->max_score }})</label>
                                <span class="text-[11px] text-zinc-500">Nilai dasar untuk kelompok ini, dapat disesuaikan per individu di bawah.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="number" 
                                    id="default_group_score"
                                    wire:model.live="groupDefaultScore" 
                                    min="0" 
                                    max="{{ $assessment->max_score }}" 
                                    step="0.5" 
                                    placeholder="85" 
                                    class="w-32 px-3 py-1.5 text-sm font-bold bg-white border border-zinc-300 rounded-xl text-center focus:outline-none focus:ring-2 focus:ring-purple-500/20"
                                >
                                <button 
                                    type="button" 
                                    wire:click="applyDefaultScoreToAll" 
                                    class="px-3.5 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-800 rounded-xl text-xs font-bold transition-colors cursor-pointer flex-shrink-0"
                                >
                                    Terapkan ke Semua
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Rincian Nilai Per Anggota (Fair Grading Override) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-zinc-700 uppercase tracking-wider">
                                Rincian Nilai Setiap Anggota Kelompok
                            </h4>
                            <span class="text-[11px] text-zinc-400">
                                Anda dapat membedakan nilai jika ada anggota yang kurang aktif
                            </span>
                        </div>

                        <div class="border border-zinc-200 rounded-xl overflow-hidden divide-y divide-zinc-200">
                            @foreach($selectedGroup->members as $member)
                                @php
                                    $isThisLeader = ((int)$member->id === (int)$selectedGroup->leader_id);
                                @endphp
                                <div class="p-3.5 bg-white flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-zinc-50/50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full {{ $isThisLeader ? 'bg-purple-100 text-purple-700' : 'bg-zinc-100 text-zinc-600' }} font-bold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ substr($member->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-zinc-900 flex items-center gap-1.5">
                                                <span>{{ $member->name }}</span>
                                                @if($isThisLeader)
                                                    <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">Ketua</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-zinc-400 font-mono">NISN: {{ $member->nisn ?? '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1 items-end sm:items-start w-full sm:w-auto">
                                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full sm:w-auto">
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-semibold text-zinc-600">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    wire:model="memberScores.{{ $member->id }}" 
                                                    min="0" 
                                                    max="{{ $assessment->max_score }}" 
                                                    step="0.5" 
                                                    placeholder="0 - {{ $assessment->max_score }}"
                                                    class="w-20 px-2 py-1 text-xs font-bold text-center bg-zinc-50 border @error('memberScores.' . $member->id) border-rose-400 bg-rose-50/50 @else border-zinc-300 @enderror rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-purple-500"
                                                >
                                            </div>

                                            <input 
                                                type="text" 
                                                wire:model="memberFeedbacks.{{ $member->id }}" 
                                                placeholder="Catatan individu (opsional)..." 
                                                class="flex-1 sm:w-56 px-2.5 py-1 text-xs bg-zinc-50 border border-zinc-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-purple-500"
                                            >

                                            @if(!$isThisLeader)
                                                <button 
                                                    type="button" 
                                                    wire:click="removeStudentFromGroupTeacher({{ $member->id }}, {{ $selectedGroup->id }})" 
                                                    wire:confirm="Keluarkan {{ $member->name }} dari kelompok ini?"
                                                    title="Keluarkan dari kelompok"
                                                    class="text-rose-500 hover:text-rose-700 p-1.5 rounded hover:bg-rose-50 transition-colors flex-shrink-0"
                                                >
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            @endif
                                        </div>
                                        @error('memberScores.' . $member->id)
                                            <span class="text-[11px] text-rose-500 font-semibold mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Catatan / Umpan Balik Guru untuk Seluruh Kelompok -->
                    <div>
                        <label for="group_general_feedback" class="block text-sm font-semibold text-zinc-800 mb-1">
                            Catatan / Evaluasi Umum untuk Seluruh Kelompok (Opsional)
                        </label>
                        <textarea 
                            id="group_general_feedback"
                            wire:model="groupFeedback" 
                            rows="2" 
                            placeholder="Catatan ini akan otomatis dibagikan ke semua anggota kelompok yang belum memiliki catatan khusus..."
                            class="w-full px-4 py-2.5 text-xs bg-zinc-50 border border-zinc-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"
                        ></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-zinc-100 bg-zinc-50 flex items-center justify-between gap-3 flex-shrink-0">
                    <div>
                        @if($groupLeaderSubmission && $groupLeaderSubmission->submitted_at)
                            <button 
                                type="button" 
                                wire:click="requestGroupRevision" 
                                wire:confirm="Kirim permintaan revisi ke kelompok ini agar dapat mengunggah ulang tugas?"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-semibold transition-colors"
                            >
                                ⚠ Minta Revisi Kelompok
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="closeGroupGradingModal" 
                            class="px-4 py-2 rounded-xl bg-white hover:bg-zinc-100 border border-zinc-200 text-zinc-700 text-xs font-semibold transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            wire:click="saveGroupGrade" 
                            class="px-5 py-2 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold shadow-sm transition-colors cursor-pointer"
                        >
                            Simpan Nilai Kelompok
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
