<div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 32px">
    <section style="display: flex; flex-wrap: wrap; gap: 24px; align-items: stretch">
        <div style="flex: 999 1 600px; min-width: 0; display: flex; flex-direction: column; gap: 20px">
            <div style="display: flex; flex-direction: column; gap: 6px">
                <p style="margin: 0; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; color: #4A515C">
                    {{ now()->translatedFormat('l, d F Y') }}@if(!empty($siswaSubtitle)) &bull; {{ $siswaSubtitle }}@endif
                </p>
                <h1 style="margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2">{{ $greetingTitle }}</h1>
            </div>

            <!-- "Lanjutkan belajar" statik untuk sekarang (nanti akan diambil dari database) -->
            <article style="background: #15191F; color: #FFFFFF; border-radius: 20px; padding: 28px; display: flex; flex-wrap: wrap; gap: 28px; align-items: center">
                <div style="flex: 1 1 300px; min-width: 0; display: flex; flex-direction: column; gap: 14px">
                    <p style="margin: 0; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #F2A93B">Lanjutkan belajar</p>
                    <div style="display: flex; flex-direction: column; gap: 4px">
                        <h2 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.01em; line-height: 1.25">Materi Belajar</h2>
                        <p style="margin: 0; font-size: 14px; color: #C5CBD6">Pilih elemen di bawah untuk mulai belajar</p>
                    </div>
                </div>
            </article>
        </div>

        <aside aria-labelledby="target-title" style="flex: 1 1 300px; min-width: 0; background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 20px">
            <div style="display: flex; justify-content: space-between; align-items: center">
                <h2 id="target-title" style="margin: 0; font-size: 17px; font-weight: 700">Target minggu ini</h2>
                @if($streak > 0)
                <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; color: #F2A93B; font-weight: bold;">🔥 {{ $streak }} hari beruntun</span>
                @else
                <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; color: #4A515C">Senin - Minggu</span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 20px">
                <div style="position: relative; width: 112px; height: 112px; flex: none">
                    <svg width="112" height="112" viewBox="0 0 112 112" aria-hidden="true">
                        <circle cx="56" cy="56" r="46" fill="none" stroke="#E8EDFC" stroke-width="12"></circle>
                        @php
                            $circumference = 2 * pi() * 46;
                            $dasharray = ($weeklyPercentage / 100) * $circumference;
                        @endphp
                        <circle cx="56" cy="56" r="46" fill="none" stroke="#2347C4" stroke-width="12" stroke-linecap="round" stroke-dasharray="{{ $dasharray }} {{ $circumference }}" transform="rotate(-90 56 56)"></circle>
                    </svg>
                    <div style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1">
                        <span style="font-size: 26px; font-weight: 800">{{ $completedThisWeek }}</span>
                        <span style="font-size: 12px; color: #4A515C">dari {{ $weeklyTarget }}</span>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px">
                    @if($completedThisWeek >= $weeklyTarget)
                        <p style="margin: 0; font-size: 15px; font-weight: 700; color: #1D6B45;">Target Tercapai!</p>
                        <p style="margin: 0; font-size: 14px; color: #4A515C">Kerja bagus! Pertahankan semangat belajarmu.</p>
                    @elseif($completedThisWeek > 0)
                        <p style="margin: 0; font-size: 15px; font-weight: 700">Progres Berjalan</p>
                        <p style="margin: 0; font-size: 14px; color: #4A515C">Lanjutkan belajarmu untuk mencapai target.</p>
                    @else
                        <p style="margin: 0; font-size: 15px; font-weight: 700">Belum ada progres</p>
                        <p style="margin: 0; font-size: 14px; color: #4A515C">Mulai belajar untuk menyelesaikan target mingguanmu.</p>
                    @endif
                </div>
            </div>
        </aside>
    </section>

    <section style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start">
        <div style="flex: 999 1 640px; min-width: 0; display: flex; flex-direction: column; gap: 24px">
            
            <!-- Lanjutkan belajar -->
            <article style="background: #15191F; color: #FFFFFF; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 20px">
                <div style="display: flex; flex-direction: column; gap: 4px">
                    <span style="font-size: 14px; color: #A0AEC0; font-weight: 600">Terakhir Dipelajari</span>
                    <h2 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.01em; line-height: 1.2">
                        @if($lastProgress && $lastProgress->lesson)
                            {{ $lastProgress->lesson->title }}
                        @else
                            Mari Mulai Belajar!
                        @endif
                    </h2>
                    @if($lastProgress && $lastProgress->lesson && $lastProgress->lesson->learningObjective)
                    <p style="margin: 0; font-size: 15px; color: #CBD5E1; opacity: 0.9">{{ $lastProgress->lesson->learningObjective->name }}</p>
                    @else
                    <p style="margin: 0; font-size: 15px; color: #CBD5E1; opacity: 0.9">Pilih elemen untuk memulai perjalanan belajarmu.</p>
                    @endif
                </div>
                
                @if($lastProgress && $lastProgress->lesson && $lastProgress->lesson->learningObjective)
                <a href="{{ route('siswa.belajar.materi', ['element' => $lastProgress->lesson->learningObjective->element->code, 'tp' => $lastProgress->lesson->learningObjective->code, 'lesson' => $lastProgress->lesson->code ?: $lastProgress->lesson->id]) }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 18px; border-radius: 12px; border: 1px solid #2347C4; background: #2347C4; color: #FFFFFF; font-weight: 700; font-size: 15px; text-decoration: none; width: fit-content">Lanjutkan Belajar</a>
                @else
                <a href="{{ route('siswa.belajar') }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 18px; border-radius: 12px; border: 1px solid #2347C4; background: #2347C4; color: #FFFFFF; font-weight: 700; font-size: 15px; text-decoration: none; width: fit-content">Lihat Daftar Elemen</a>
                @endif
            </article>

            <!-- Elemen Belajar -->
            <div style="display: flex; flex-direction: column; gap: 18px; margin-top: 12px">
                <div style="display: flex; flex-direction: column; gap: 2px">
                    <h2 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.01em">Elemen Informatika</h2>
                    <p style="margin: 0; font-size: 14px; color: #4A515C">Pilih elemen untuk melihat materi dan progres belajar Anda.</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px">
                    @foreach($elements as $element)
                    <article style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 16px">
                        <div style="display: flex; align-items: center; gap: 10px">
                            <span style="padding: 4px 10px; border-radius: 8px; background: #2347C4; color: #FFFFFF; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 13px; font-weight: 600">{{ $element->code }}</span>
                        </div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800; line-height: 1.2">{{ $element->name }}</h3>
                        <a href="{{ route('siswa.belajar.tp', $element->code) }}" style="display: inline-block; text-align: center; width: 100%; padding: 10px 0; border-radius: 8px; background: #F4F5F2; color: #15191F; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s">Mulai Belajar &rarr;</a>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>

        <aside style="flex: 1 1 320px; min-width: 0; display: flex; flex-direction: column; gap: 20px">
            {{-- Tugas & Penilaian Highlight --}}
            @if($urgentTasks->isNotEmpty() || $gradedTasks->isNotEmpty())
            <section style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; gap: 14px">
                <div style="display: flex; justify-content: space-between; align-items: center">
                    <h2 style="margin: 0; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2347C4" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        Tugas Anda
                    </h2>
                    <a href="{{ route('siswa.tugas') }}" style="font-size: 13px; font-weight: 600; color: #2347C4; text-decoration: none">Lihat Semua &rarr;</a>
                </div>

                {{-- Urgent tasks --}}
                @foreach($urgentTasks as $ut)
                    @php
                        $sub = $ut->submissions->first();
                        $isRevision = $sub && $sub->status === 'perlu_revisi';
                        $dueCarbon = $ut->due_at ? \Illuminate\Support\Carbon::parse($ut->due_at)->setTimezone('Asia/Jakarta') : null;
                        $isPastDue = $dueCarbon && $dueCarbon->isPast();
                    @endphp
                    <a href="{{ route('siswa.tugas.kerjakan', $ut->id) }}" style="display: block; padding: 12px 14px; border-radius: 12px; background: {{ $isRevision ? '#FEF3C7' : ($isPastDue ? '#FEE2E2' : '#FFFBEB') }}; border: 1px solid {{ $isRevision ? '#F59E0B' : ($isPastDue ? '#FCA5A5' : '#FDE68A') }}; text-decoration: none; color: inherit; transition: transform 0.15s">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 4px">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <span style="font-size: 13px; font-weight: 700; color: #15191F; line-height: 1.3">{{ $ut->title }}</span>
                                @if($ut->isGroup())
                                    <span style="font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">Kelompok</span>
                                @endif
                            </div>
                            @if($isRevision)
                                <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; background: #D97706; color: #FFFFFF; white-space: nowrap">Revisi</span>
                            @elseif($isPastDue)
                                <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; background: #DC2626; color: #FFFFFF; white-space: nowrap">Lewat</span>
                            @else
                                <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; background: #D97706; color: #FFFFFF; white-space: nowrap">Tenggat Dekat</span>
                            @endif
                        </div>
                        <div style="font-size: 11px; color: #4A515C; display: flex; align-items: center; gap: 6px">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            @if($dueCarbon)
                                {{ $isPastDue ? 'Terlewat: ' : 'Tenggat: ' }} {{ $dueCarbon->translatedFormat('d M H:i') }} WIB ({{ $dueCarbon->diffForHumans() }})
                            @else
                                Tanpa tenggat
                            @endif
                        </div>
                    </a>
                @endforeach

                {{-- Graded tasks --}}
                @foreach($gradedTasks as $gt)
                    @if(!$urgentTasks->contains('id', $gt->assessment_id))
                        <a href="{{ route('siswa.tugas.kerjakan', $gt->assessment_id) }}" style="display: block; padding: 12px 14px; border-radius: 12px; background: #F0FDF4; border: 1px solid #BBF7D0; text-decoration: none; color: inherit">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 4px">
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-size: 13px; font-weight: 700; color: #15191F; line-height: 1.3">{{ $gt->assessment->title ?? 'Tugas' }}</span>
                                    @if($gt->assessment?->isGroup())
                                        <span style="font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;">Kelompok</span>
                                    @endif
                                </div>
                                <span style="font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; background: #16A34A; color: #FFFFFF">
                                    Nilai: {{ $gt->score ?? 0 }}/{{ $gt->assessment->max_score ?? 100 }}
                                </span>
                            </div>
                            @if($gt->feedback)
                                <p style="margin: 0; font-size: 11px; color: #166534; overflow: hidden; text-overflow: ellipsis; white-space: nowrap">
                                    Catatan: "{{ \Illuminate\Support\Str::limit($gt->feedback, 50) }}"
                                </p>
                            @else
                                <span style="font-size: 11px; color: #166534">Sudah dinilai guru</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </section>
            @endif

            <!-- Pengumuman -->
            <section aria-labelledby="info-title" style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; gap: 14px">
                <h2 id="info-title" style="margin: 0; font-size: 17px; font-weight: 700">Pengumuman</h2>
                @if($announcements->isEmpty())
                    <div style="padding: 12px 0; color: #4A515C; font-size: 14px">Belum ada pengumuman.</div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($announcements as $announcement)
                            <div style="padding-bottom: 12px; border-bottom: 1px solid #E2E4DF;">
                                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700">{{ $announcement->title }}</h3>
                                <p style="margin: 0 0 6px 0; font-size: 13px; color: #4A515C">{{ $announcement->body }}</p>
                                <span style="font-size: 11px; color: #888;">{{ $announcement->published_at->diffForHumans() }} &bull; {{ $announcement->author->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </section>
</div>
