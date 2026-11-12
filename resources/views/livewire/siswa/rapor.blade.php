<div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 28px">
    <!-- Page Header -->
    <x-siswa.page-header 
        title="Nilai & Rapor Belajar" 
        subtitle="Pantau capaian nilai tugas, asesmen, dan ketercapaian kompetensi pembelajaranmu." 
    >
        @if($selectedClass && $selectedYear && $reportData && ($reportData['is_published'] ?? false))
            <a 
                href="{{ route('siswa.rapor.cetak', ['classroom' => $selectedClass, 'year' => $selectedYear]) }}" 
                target="_blank" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm text-white shadow-sm transition-all hover:opacity-90"
                style="background: #2347C4; text-decoration: none;"
            >
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Rapor PDF
            </a>
        @endif
    </x-siswa.page-header>

    <!-- Filter Tahun Ajaran & Kelas -->
    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 18px 22px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 14px; flex: 1 1 360px">
            <div style="min-width: 200px; flex: 1">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #4A515C; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em">Tahun Ajaran & Semester</label>
                <select 
                    wire:model.live="selectedYear" 
                    style="width: 100%; border: 1px solid #E2E4DF; border-radius: 10px; padding: 9px 14px; font-size: 14px; font-weight: 600; color: #15191F; background: #F4F5F2; outline: none; cursor: pointer"
                >
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} - Semester {{ ucfirst($year->semester) }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 200px; flex: 1">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #4A515C; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em">Kelas Siswa</label>
                <select 
                    wire:model.live="selectedClass" 
                    style="width: 100%; border: 1px solid #E2E4DF; border-radius: 10px; padding: 9px 14px; font-size: 14px; font-weight: 600; color: #15191F; background: #F4F5F2; outline: none; cursor: pointer"
                >
                    @if($classrooms->isEmpty())
                        <option value="">Belum terdaftar di kelas</option>
                    @else
                        @foreach($classrooms as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        @if($classroom)
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #4A515C; background: #F4F5F2; padding: 8px 14px; border-radius: 10px; border: 1px solid #E2E4DF">
                <span style="font-weight: 700; color: #15191F">Status Kelas:</span>
                <span>{{ $classroom->name }}</span>
                <span style="color: #A1A1AA">•</span>
                <span>KKTP Standar: <strong style="color: #2347C4">{{ $defaultKktp }}</strong></span>
            </div>
        @endif
    </div>

    @if(!$classroom)
        <!-- Empty Classroom State -->
        <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 48px 24px; text-align: center; color: #4A515C">
            <svg style="margin: 0 auto 16px; width: 48px; height: 48px; color: #A1A1AA" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h3 style="margin: 0 0 6px; font-size: 18px; font-weight: 800; color: #15191F">Belum Ada Data Kelas</h3>
            <p style="margin: 0; font-size: 14px; max-width: 440px; margin-inline: auto">Kamu belum terdaftar di kelas manapun pada tahun ajaran yang dipilih.</p>
        </div>
    @else
        <!-- Ringkasan Nilai di Atas (Summary Cards) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px">
            <!-- Rata-rata Nilai -->
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 8px">
                <div style="display: flex; justify-content: space-between; align-items: center">
                    <span style="font-size: 12px; font-weight: 700; color: #4A515C; text-transform: uppercase; letter-spacing: 0.05em">Rata-rata Nilai</span>
                    <span style="width: 28px; height: 28px; border-radius: 8px; background: #E8EDFC; color: #2347C4; display: flex; align-items: center; justify-content: center">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #15191F; font-family: 'JetBrains Mono', monospace; line-height: 1">
                    {{ $summary['average_score'] !== null ? number_format($summary['average_score'], 1) : '-' }}
                </div>
                <span style="font-size: 12px; color: #4A515C">Dari {{ $summary['graded_count'] }} tugas yang dinilai</span>
            </div>

            <!-- Tugas Sudah Dinilai -->
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 8px">
                <div style="display: flex; justify-content: space-between; align-items: center">
                    <span style="font-size: 12px; font-weight: 700; color: #4A515C; text-transform: uppercase; letter-spacing: 0.05em">Tugas Dinilai</span>
                    <span style="width: 28px; height: 28px; border-radius: 8px; background: #E7F3EC; color: #1D6B45; display: flex; align-items: center; justify-content: center">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #15191F; font-family: 'JetBrains Mono', monospace; line-height: 1">
                    {{ $summary['graded_count'] }} <span style="font-size: 16px; color: #4A515C; font-weight: 600">/ {{ $summary['total_assessments'] }}</span>
                </div>
                <span style="font-size: 12px; color: #4A515C">{{ $summary['ungraded_count'] }} tugas belum dinilai</span>
            </div>

            <!-- Tercapai (>= KKTP) -->
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 8px">
                <div style="display: flex; justify-content: space-between; align-items: center">
                    <span style="font-size: 12px; font-weight: 700; color: #1D6B45; text-transform: uppercase; letter-spacing: 0.05em">Tercapai (≥ KKTP)</span>
                    <span style="width: 28px; height: 28px; border-radius: 8px; background: #E7F3EC; color: #1D6B45; display: flex; align-items: center; justify-content: center">
                        ✓
                    </span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #1D6B45; font-family: 'JetBrains Mono', monospace; line-height: 1">
                    {{ $summary['achieved_count'] }}
                </div>
                <span style="font-size: 12px; color: #4A515C">Memenuhi standar ketuntasan</span>
            </div>

            <!-- Belum Tercapai (< KKTP) -->
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 8px">
                <div style="display: flex; justify-content: space-between; align-items: center">
                    <span style="font-size: 12px; font-weight: 700; color: #D32F2F; text-transform: uppercase; letter-spacing: 0.05em">Perlu Remedial</span>
                    <span style="width: 28px; height: 28px; border-radius: 8px; background: #FBEDE5; color: #D32F2F; display: flex; align-items: center; justify-content: center">
                        !
                    </span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: {{ $summary['unachieved_count'] > 0 ? '#D32F2F' : '#15191F' }}; font-family: 'JetBrains Mono', monospace; line-height: 1">
                    {{ $summary['unachieved_count'] }}
                </div>
                <span style="font-size: 12px; color: #4A515C">{{ $summary['unachieved_count'] > 0 ? 'Perlu perbaikan belajar' : 'Semua telah tuntas' }}</span>
            </div>

            <!-- Nilai Akhir / Rapor -->
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 8px">
                <div style="display: flex; justify-content: space-between; align-items: center">
                    <span style="font-size: 12px; font-weight: 700; color: #2347C4; text-transform: uppercase; letter-spacing: 0.05em">Nilai Akhir Rapor</span>
                    <span style="font-size: 12px; font-weight: 800; color: #2347C4; padding: 2px 6px; border-radius: 6px; background: #E8EDFC">
                        {{ $reportData['predicate'] ?? '-' }}
                    </span>
                </div>
                <div style="font-size: 32px; font-weight: 800; color: #2347C4; font-family: 'JetBrains Mono', monospace; line-height: 1">
                    {{ $reportData && $reportData['final_score'] !== null ? number_format($reportData['final_score'], 0) : '-' }}
                </div>
                <span style="font-size: 12px; color: #4A515C">Kehadiran: {{ $summary['attendance_percentage'] }}%</span>
            </div>
        </div>

        <!-- Tab Navigasi -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; background: #FFFFFF; padding: 10px 14px; border-radius: 16px; border: 1px solid #E2E4DF">
            <div style="display: flex; gap: 6px; overflow-x: auto">
                <button 
                    type="button" 
                    wire:click="setTab('semua')" 
                    style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $activeTab === 'semua' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
                >
                    Semua Penilaian ({{ $allAssessments->count() }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('tugas')" 
                    style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $activeTab === 'tugas' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
                >
                    Tugas Mandiri ({{ $allAssessments->where('filter_category', 'tugas')->count() }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('ujian')" 
                    style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $activeTab === 'ujian' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
                >
                    UH & Ujian ({{ $allAssessments->where('filter_category', 'ujian')->count() }})
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('tp')" 
                    style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $activeTab === 'tp' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
                >
                    Tujuan Pembelajaran (TP)
                </button>
                <button 
                    type="button" 
                    wire:click="setTab('buku_nilai')" 
                    style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $activeTab === 'buku_nilai' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
                >
                    Rapor & Buku Nilai
                </button>
            </div>
        </div>

        @if(in_array($activeTab, ['semua', 'tugas', 'ujian']))
            <!-- DETAIL PENILAIAN TUGAS & ASESMEN -->
            <div style="display: flex; flex-direction: column; gap: 16px">
                @forelse($assessments as $item)
                    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 22px; display: flex; flex-direction: column; gap: 16px; transition: transform 0.15s, box-shadow 0.15s"
                         onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.04)';"
                         onmouseout="this.style.boxShadow='none';">
                        
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 14px">
                            <!-- Info Tugas -->
                            <div style="display: flex; flex-direction: column; gap: 6px; flex: 1 1 320px">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px">
                                    <!-- Badge Kategori -->
                                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; background: #E8EDFC; color: #2347C4; font-family: 'JetBrains Mono', monospace">
                                        {{ strtoupper($item->grade_group) }}
                                    </span>

                                    <!-- Badge Status Pengumpulan / Penilaian -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $item->badge_class }}">
                                        {{ $item->status_label }}
                                    </span>

                                    @if($item->is_graded)
                                        @if($item->is_achieved)
                                            <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; background: #E7F3EC; color: #1D6B45; border: 1px solid #C4E3D2; display: inline-flex; align-items: center; gap: 4px">
                                                <span>✓</span> Tercapai
                                            </span>
                                        @else
                                            <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; background: #FBEDE5; color: #D32F2F; border: 1px solid #F5C6CB; display: inline-flex; align-items: center; gap: 4px">
                                                <span>⚠</span> Belum Tercapai
                                            </span>
                                        @endif
                                    @else
                                        <span style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 99px; background: #F4F5F2; color: #71717A; border: 1px solid #E2E4DF; display: inline-flex; align-items: center; gap: 4px">
                                            Belum dinilai
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($item->assessment_id))
                                    <a href="{{ route('siswa.tugas.kerjakan', $item->assessment_id) }}" wire:navigate style="text-decoration: none; color: #15191F">
                                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; line-height: 1.3">
                                            {{ $item->title }}
                                        </h3>
                                    </a>
                                @else
                                    <h3 style="margin: 0; font-size: 18px; font-weight: 800; line-height: 1.3; color: #15191F">
                                        {{ $item->title }}
                                    </h3>
                                @endif

                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; font-size: 13px; color: #4A515C">
                                    @if($item->tp_code)
                                        <span>TP: <strong style="color: #15191F">{{ $item->tp_code }}</strong> ({{ $item->tp_desc ?? '' }})</span>
                                        <span>•</span>
                                    @endif
                                    @if($item->element_name)
                                        <span>Elemen: {{ $item->element_name }}</span>
                                        <span>•</span>
                                    @endif
                                    @if($item->due_at)
                                        <span>Tenggat: {{ \Carbon\Carbon::parse($item->due_at)->translatedFormat('d M Y, H:i') . ' WIB' }}</span>
                                    @elseif($item->date)
                                        <span>Tanggal: {{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M Y') }}</span>
                                    @else
                                        <span>Penilaian langsung di Buku Nilai</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Nilai Siswa -->
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; min-width: 140px; background: #F4F5F2; padding: 12px 18px; border-radius: 14px; border: 1px solid #E2E4DF">
                                <span style="font-size: 11px; font-weight: 700; color: #4A515C; text-transform: uppercase; letter-spacing: 0.05em">Nilai Diperoleh</span>
                                <div style="display: flex; align-items: baseline; gap: 4px">
                                    @if($item->is_graded)
                                        <span style="font-size: 26px; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: {{ $item->is_achieved ? '#1D6B45' : '#D32F2F' }}">
                                            {{ round($item->score, 1) }}
                                        </span>
                                        <span style="font-size: 14px; font-weight: 600; color: #4A515C">/ {{ round($item->max_score, 0) }}</span>
                                    @else
                                        <span style="font-size: 14px; font-weight: 700; color: #71717A; padding: 4px 0">
                                            Belum dinilai
                                        </span>
                                    @endif
                                </div>
                                <span style="font-size: 11px; color: #71717A">Target KKTP: <strong>{{ round($item->kktp, 0) }}</strong></span>
                            </div>
                        </div>

                        <!-- Feedback Guru jika ada -->
                        @if($item->feedback)
                            <div style="background: #FFFDF5; border: 1px solid #F5E6B8; border-radius: 12px; padding: 12px 16px; display: flex; gap: 10px; align-items: flex-start">
                                <span style="font-size: 16px; line-height: 1">💬</span>
                                <div style="display: flex; flex-direction: column; gap: 2px">
                                    <span style="font-size: 12px; font-weight: 700; color: #8A6D1A">Umpan Balik Guru:</span>
                                    <p style="margin: 0; font-size: 13px; color: #5C4813; font-style: italic; line-height: 1.45">
                                        "{{ $item->feedback }}"
                                    </p>
                                </div>
                            </div>
                        @endif

                        <!-- Action Link jika ada asesmen LMS -->
                        @if(!empty($item->assessment_id))
                            <div style="display: flex; justify-content: flex-end; align-items: center; padding-top: 10px; border-top: 1px solid #EDEEEA">
                                <a href="{{ route('siswa.tugas.kerjakan', $item->assessment_id) }}" wire:navigate style="font-size: 13px; font-weight: 700; color: #2347C4; text-decoration: none; display: inline-flex; align-items: center; gap: 4px">
                                    Lihat Instruksi & Jawaban Tugas
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 48px 24px; text-align: center; color: #4A515C">
                        <svg style="margin: 0 auto 12px; width: 40px; height: 40px; color: #A1A1AA" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h4 style="margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #15191F">Belum Ada Penilaian</h4>
                        <p style="margin: 0; font-size: 14px">Tidak ada data penilaian pada kategori ini.</p>
                    </div>
                @endforelse
            </div>
        @elseif($activeTab === 'tp')
            <!-- KETERCAPAIAN TUJUAN PEMBELAJARAN (TP) -->
            <div style="display: flex; flex-direction: column; gap: 24px">
                @forelse($elements as $element)
                    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 22px; display: flex; flex-direction: column; gap: 16px">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #F4F5F2; padding-bottom: 12px">
                            <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: #15191F">
                                Elemen: {{ $element->name }}
                            </h3>
                            <span style="font-size: 12px; font-weight: 700; color: #4A515C">
                                {{ $element->learningObjectives->count() }} Tujuan Pembelajaran
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 12px">
                            @foreach($element->learningObjectives as $tp)
                                <div x-data="{ open: false }" class="border border-[#E2E4DF] rounded-2xl bg-[#FFFFFF] overflow-hidden transition-all duration-200 hover:border-[#CAD7FB]">
                                    <!-- Baris Utama TP (Klik untuk membuka rincian) -->
                                    <div 
                                        @click="open = !open" 
                                        class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer select-none hover:bg-[#F8F9F7] transition-colors"
                                    >
                                        <!-- Kiri: Kode TP, Deskripsi, dan Badge Jumlah Penilaian -->
                                        <div class="flex flex-col gap-2 flex-1 min-w-[240px]">
                                            <div class="flex items-center gap-2.5 flex-wrap">
                                                <span class="font-mono text-xs font-bold text-[#2347C4] bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-100">
                                                    {{ $tp->code }}
                                                </span>

                                                <!-- Badge Jumlah Penilaian yang Dihitung -->
                                                @if(($tp->graded_count ?? 0) > 0)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#2347C4] border border-blue-200">
                                                        <svg class="w-3.5 h-3.5 text-[#2347C4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                        </svg>
                                                        <span>{{ $tp->count_label ?? ($tp->graded_count . ' penilaian') }}</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                                                        <svg class="w-3 h-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>Belum ada penilaian</span>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="text-sm sm:text-base font-semibold text-[#15191F] leading-snug">
                                                {{ $tp->description }}
                                            </div>
                                        </div>

                                        <!-- Kanan: KKTP, Nilai Rata-rata, Status Badge, dan Icon Panah -->
                                        <div class="flex items-center justify-between md:justify-end gap-3 sm:gap-6 pt-2 md:pt-0 border-t md:border-t-0 border-zinc-100">
                                            <!-- KKTP -->
                                            <div class="flex flex-col items-center">
                                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">KKTP</span>
                                                <span class="font-mono text-sm sm:text-base font-extrabold text-[#15191F]">{{ $tp->effective_kktp ?? $defaultKktp }}</span>
                                            </div>

                                            <!-- Nilai Rata-rata Dinormalkan -->
                                            <div class="flex flex-col items-center">
                                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Nilai</span>
                                                <span class="font-mono text-base sm:text-lg font-extrabold" style="color: {{ ($tp->calculated_score ?? null) !== null ? (($tp->calculated_score ?? 0) >= ($tp->effective_kktp ?? $defaultKktp) ? '#1D6B45' : '#D32F2F') : '#71717A' }}">
                                                    {{ ($tp->calculated_score ?? null) !== null ? round($tp->calculated_score, 1) : '-' }}
                                                </span>
                                            </div>

                                            <!-- Status Badge -->
                                            <div style="padding: 6px 14px; border-radius: 99px; background: {{ $tp->badge_bg ?? '#F4F5F2' }}; color: {{ $tp->badge_text ?? '#4A515C' }}; border: 1px solid {{ $tp->badge_border ?? '#E2E4DF' }}; font-size: 12px; font-weight: 700; min-width: 96px; text-align: center">
                                                {{ $tp->calculated_status ?? 'Belum Diuji' }}
                                            </div>

                                            <!-- Icon Toggle Accordion -->
                                            <div class="w-8 h-8 rounded-full bg-zinc-100 flex items-center justify-center text-zinc-500 hover:text-zinc-700 hover:bg-zinc-200 transition-colors">
                                                <svg class="w-4 h-4 transform transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bagian Rincian Penilaian (Collapsible) -->
                                    <div x-show="open" x-cloak class="border-t border-[#EDEEEA] bg-[#FAFBF9] p-4 sm:p-6 space-y-3">
                                        <div class="flex items-center justify-between pb-2">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-600 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-[#2347C4]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Rincian Penilaian Tersambung untuk {{ $tp->code }}
                                            </h4>
                                            <span class="text-xs text-zinc-500 font-medium">
                                                Nilai TP = rata-rata nilai terhitung (skala 0–100)
                                            </span>
                                        </div>

                                        @if(empty($tp->connected_items) || $tp->connected_items->isEmpty())
                                            <div class="p-4 rounded-xl bg-white border border-zinc-200 text-center text-xs text-zinc-500">
                                                Belum ada tugas atau kolom nilai guru yang dikaitkan ke Tujuan Pembelajaran ini.
                                            </div>
                                        @else
                                            <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white">
                                                <table class="w-full text-left text-xs border-collapse">
                                                    <thead>
                                                        <tr class="bg-zinc-50 text-zinc-600 font-bold border-b border-zinc-200 uppercase tracking-wider text-[11px]">
                                                            <th class="py-2.5 px-3">Jenis</th>
                                                            <th class="py-2.5 px-3">Nama Penilaian</th>
                                                            <th class="py-2.5 px-3 text-center">Nilai Asli</th>
                                                            <th class="py-2.5 px-3 text-center">Nilai (0–100)</th>
                                                            <th class="py-2.5 px-3 text-center">KKTP</th>
                                                            <th class="py-2.5 px-3 text-center">Status</th>
                                                            <th class="py-2.5 px-3 text-right">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-zinc-100">
                                                        @foreach($tp->connected_items as $cItem)
                                                            <tr class="hover:bg-zinc-50/70 transition-colors">
                                                                <td class="py-2.5 px-3 whitespace-nowrap">
                                                                    <span class="font-mono uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-zinc-100 text-zinc-700">
                                                                        {{ strtoupper($cItem->grade_group ?? $cItem->jenis) }}
                                                                    </span>
                                                                </td>
                                                                <td class="py-2.5 px-3 font-semibold text-zinc-800">
                                                                    {{ $cItem->title }}
                                                                    @if(!empty($cItem->due_at))
                                                                        <div class="text-[11px] text-zinc-400 font-normal">
                                                                            {{ \Carbon\Carbon::parse($cItem->due_at)->isoFormat('D MMMM Y') }}
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-zinc-800">
                                                                    @if($cItem->is_graded && $cItem->score !== null)
                                                                        {{ $cItem->score }} <span class="text-zinc-400 font-normal">/ {{ $cItem->max_score }}</span>
                                                                    @else
                                                                        <span class="text-zinc-400 font-normal">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-[#2347C4]">
                                                                    {{ $cItem->normalized_score !== null ? round($cItem->normalized_score, 1) : '-' }}
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center font-mono text-zinc-600">
                                                                    {{ $tp->effective_kktp ?? $defaultKktp }}
                                                                </td>
                                                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                                                    @if($cItem->is_graded)
                                                                        @if($cItem->is_achieved)
                                                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                                ✓ Tercapai
                                                                            </span>
                                                                        @else
                                                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                                                ⚠ Belum Tercapai
                                                                            </span>
                                                                        @endif
                                                                    @else
                                                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">
                                                                            {{ $cItem->status_label ?? 'Belum dinilai' }}
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                                                    @if(!empty($cItem->assessment_id))
                                                                        <a href="{{ route('siswa.tugas.kerjakan', $cItem->assessment_id) }}" wire:navigate class="inline-flex items-center gap-1 text-[11px] font-bold text-[#2347C4] hover:underline">
                                                                            Lihat Tugas &rarr;
                                                                        </a>
                                                                    @else
                                                                        <span class="text-zinc-400 text-[11px]">Buku Nilai</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 48px 24px; text-align: center; color: #4A515C">
                        <p style="margin: 0; font-size: 14px">Belum ada Tujuan Pembelajaran yang dikonfigurasi untuk kelas ini.</p>
                    </div>
                @endforelse
            </div>
        @elseif($activeTab === 'buku_nilai')
            <!-- REKAP BUKU NILAI & RAPOR -->
            <div style="display: flex; flex-direction: column; gap: 24px">
                <!-- Rapor Akademik & Absensi -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px">
                    <!-- Nilai Rapor -->
                    <x-siswa.card>
                        <h3 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #15191F">Rangkuman Rapor Semester</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px">
                            <div style="background: #F4F5F2; padding: 14px; border-radius: 12px; border: 1px solid #E2E4DF">
                                <span style="font-size: 12px; color: #4A515C">Rata-rata UH</span>
                                <div style="font-size: 22px; font-weight: 800; color: #15191F; font-family: 'JetBrains Mono', monospace">
                                    {{ $reportData['avg_uh'] ?? '-' }}
                                </div>
                            </div>
                            <div style="background: #F4F5F2; padding: 14px; border-radius: 12px; border: 1px solid #E2E4DF">
                                <span style="font-size: 12px; color: #4A515C">Rata-rata Tugas</span>
                                <div style="font-size: 22px; font-weight: 800; color: #15191F; font-family: 'JetBrains Mono', monospace">
                                    {{ $reportData['avg_tugas'] ?? '-' }}
                                </div>
                            </div>
                            <div style="background: #F4F5F2; padding: 14px; border-radius: 12px; border: 1px solid #E2E4DF">
                                <span style="font-size: 12px; color: #4A515C">Rata-rata UAS/MID</span>
                                <div style="font-size: 22px; font-weight: 800; color: #15191F; font-family: 'JetBrains Mono', monospace">
                                    {{ $reportData['avg_uas_mid'] ?? '-' }}
                                </div>
                            </div>
                            <div style="background: #E8EDFC; padding: 14px; border-radius: 12px; border: 1px solid #CAD7FB">
                                <span style="font-size: 12px; color: #2347C4; font-weight: 700">Nilai Akhir Rapor</span>
                                <div style="font-size: 24px; font-weight: 800; color: #2347C4; font-family: 'JetBrains Mono', monospace">
                                    {{ $reportData['final_score'] ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #EDEEEA; font-size: 13px; color: #4A515C">
                            @if($reportData && ($reportData['is_published'] ?? false))
                                <div style="display: flex; align-items: center; gap: 6px; color: #1D6B45; font-weight: 700">
                                    <span>✓</span> Rapor semester ini telah diterbitkan resmi oleh guru.
                                </div>
                            @else
                                <div style="display: flex; align-items: center; gap: 6px; color: #71717A">
                                    <span>ℹ</span> Nilai bersifat berjalan (*live*) dan terus diperbarui saat guru menilai tugas.
                                </div>
                            @endif
                        </div>
                    </x-siswa.card>

                    <!-- Kehadiran -->
                    <x-siswa.card>
                        <h3 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #15191F">Rekap Presensi & Kehadiran</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px">
                            <div style="background: #E7F3EC; padding: 12px 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center">
                                <span style="font-size: 13px; font-weight: 600; color: #1D6B45">Hadir</span>
                                <span style="font-size: 18px; font-weight: 800; color: #1D6B45; font-family: 'JetBrains Mono', monospace">{{ $reportData['attendance']['Hadir'] ?? 0 }}</span>
                            </div>
                            <div style="background: #E8EDFC; padding: 12px 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center">
                                <span style="font-size: 13px; font-weight: 600; color: #2347C4">Izin</span>
                                <span style="font-size: 18px; font-weight: 800; color: #2347C4; font-family: 'JetBrains Mono', monospace">{{ $reportData['attendance']['Izin'] ?? 0 }}</span>
                            </div>
                            <div style="background: #FFFDF5; padding: 12px 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center">
                                <span style="font-size: 13px; font-weight: 600; color: #8A6D1A">Sakit</span>
                                <span style="font-size: 18px; font-weight: 800; color: #8A6D1A; font-family: 'JetBrains Mono', monospace">{{ $reportData['attendance']['Sakit'] ?? 0 }}</span>
                            </div>
                            <div style="background: #FBEDE5; padding: 12px 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center">
                                <span style="font-size: 13px; font-weight: 600; color: #D32F2F">Alpa</span>
                                <span style="font-size: 18px; font-weight: 800; color: #D32F2F; font-family: 'JetBrains Mono', monospace">{{ $reportData['attendance']['Alpa'] ?? 0 }}</span>
                            </div>
                        </div>

                        <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #EDEEEA; display: flex; justify-content: space-between; align-items: center">
                            <span style="font-size: 13px; color: #4A515C; font-weight: 600">Persentase Kehadiran</span>
                            <span style="font-size: 18px; font-weight: 800; color: #15191F; font-family: 'JetBrains Mono', monospace">{{ $summary['attendance_percentage'] }}%</span>
                        </div>
                    </x-siswa.card>
                </div>

                <!-- Rincian Buku Nilai per Kolom Penilaian -->
                <x-siswa.card>
                    <h3 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #15191F">Rincian Kolom Penilaian Guru</h3>
                    <div style="overflow-x: auto">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left">
                            <thead>
                                <tr style="border-bottom: 2px solid #E2E4DF; color: #4A515C; font-size: 12px; text-transform: uppercase">
                                    <th style="padding: 10px 14px">Jenis</th>
                                    <th style="padding: 10px 14px">Kolom Nilai</th>
                                    <th style="padding: 10px 14px; text-align: right">Nilai Maks</th>
                                    <th style="padding: 10px 14px; text-align: right">Nilai Diperoleh</th>
                                    <th style="padding: 10px 14px; text-align: center">Status KKTP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allAssessments as $col)
                                    <tr style="border-bottom: 1px solid #F4F5F2">
                                        <td style="padding: 12px 14px; font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 700; color: #2347C4">
                                            {{ strtoupper($col->grade_group) }}
                                        </td>
                                        <td style="padding: 12px 14px; font-weight: 600; color: #15191F">
                                            {{ $col->nama_kolom }}
                                        </td>
                                        <td style="padding: 12px 14px; text-align: right; color: #4A515C; font-family: 'JetBrains Mono', monospace">
                                            {{ round($col->max_score, 0) }}
                                        </td>
                                        <td style="padding: 12px 14px; text-align: right; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: {{ $col->is_graded ? ($col->is_achieved ? '#1D6B45' : '#D32F2F') : '#71717A' }}">
                                            {{ $col->is_graded ? round($col->score, 1) : 'Belum dinilai' }}
                                        </td>
                                        <td style="padding: 12px 14px; text-align: center">
                                            @if($col->is_graded)
                                                @if($col->is_achieved)
                                                    <span style="font-size: 11px; font-weight: 700; color: #1D6B45; background: #E7F3EC; padding: 2px 8px; border-radius: 99px">
                                                        Tercapai
                                                    </span>
                                                @else
                                                    <span style="font-size: 11px; font-weight: 700; color: #D32F2F; background: #FBEDE5; padding: 2px 8px; border-radius: 99px">
                                                        Belum Tercapai
                                                    </span>
                                                @endif
                                            @else
                                                <span style="font-size: 11px; font-weight: 600; color: #71717A; background: #F4F5F2; padding: 2px 8px; border-radius: 99px">
                                                    Belum dinilai
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 24px; text-align: center; color: #71717A">
                                            Belum ada kolom penilaian yang dibuat guru di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-siswa.card>
            </div>
        @endif
    @endif
</div>
