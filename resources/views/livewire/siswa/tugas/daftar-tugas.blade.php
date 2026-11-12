<div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 28px">
    <!-- Header -->
    <div style="display: flex; flex-direction: column; gap: 6px">
        <h1 style="margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2">Tugas Siswa</h1>
        <p style="margin: 0; font-size: 15px; color: #4A515C">Daftar tugas mandiri dan praktik untuk kelas {{ $classroom?->name ?? '' }}.</p>
    </div>

    <!-- Filter Tabs & Search -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; background: #FFFFFF; padding: 12px 16px; border-radius: 16px; border: 1px solid #E2E4DF">
        <!-- Tabs -->
        <div style="display: flex; gap: 6px; overflow-x: auto;">
            <button 
                type="button" 
                wire:click="setTab('semua')" 
                style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $tab === 'semua' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
            >
                Semua
            </button>
            <button 
                type="button" 
                wire:click="setTab('perlu_dikerjakan')" 
                style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $tab === 'perlu_dikerjakan' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
            >
                Perlu Dikerjakan
            </button>
            <button 
                type="button" 
                wire:click="setTab('selesai')" 
                style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $tab === 'selesai' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
            >
                Selesai / Dinilai
            </button>
            <button 
                type="button" 
                wire:click="setTab('terlewat')" 
                style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; {{ $tab === 'terlewat' ? 'background: #2347C4; color: #FFFFFF;' : 'background: #F4F5F2; color: #4A515C;' }}"
            >
                Terlewat
            </button>
        </div>

        <!-- Search Input -->
        <div style="flex: 1 1 240px; max-width: 320px;">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari tugas..." 
                style="width: 100%; box-sizing: border-box; padding: 8px 14px; font-size: 13px; border-radius: 10px; border: 1px solid #E2E4DF; background: #F4F5F2; outline: none;"
            >
        </div>
    </div>

    <!-- Grid Kartu Tugas -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 18px">
        @forelse($assessments as $assessment)
            <a 
                href="{{ route('siswa.tugas.kerjakan', $assessment->id) }}" 
                wire:navigate
                style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px; text-decoration: none; color: #15191F; transition: transform 0.15s, box-shadow 0.15s;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px -6px rgba(0,0,0,0.08)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';"
            >
                <!-- Header Card: Status & Max Score -->
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $assessment->userBadgeClass }}">
                        {{ $assessment->userStatusLabel }}
                    </span>
                    <span style="font-size: 12px; font-weight: 700; color: #4A515C; font-family: 'JetBrains Mono', monospace;">
                        Max: {{ $assessment->max_score }}
                    </span>
                </div>

                <!-- Judul & Keterangan -->
                <div style="display: flex; flex-direction: column; gap: 6px">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 800; line-height: 1.35; color: #15191F">
                        {{ $assessment->title }}
                    </h2>
                    @if($assessment->lesson || $assessment->effectiveMeetingNo())
                        <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11.5px; margin-top: 2px;">
                            @if($assessment->lesson)
                                <span style="background: #E8EDFC; color: #2347C4; padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                                    Materi: {{ $assessment->lesson->title }}
                                </span>
                            @endif
                            @if($assessment->effectiveMeetingNo())
                                <span style="background: #F1EFFD; color: #5B32E8; padding: 2px 8px; border-radius: 6px; font-weight: 600;">
                                    Pertemuan {{ $assessment->effectiveMeetingNo() }}
                                </span>
                            @endif
                        </div>
                    @endif
                    @if($assessment->instructions)
                        <p style="margin: 0; font-size: 13px; color: #4A515C; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.45;">
                            {{ $assessment->instructions }}
                        </p>
                    @endif
                </div>

                <!-- Footer Card: Deadline & Info -->
                <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid #EDEEEA; display: flex; flex-direction: column; gap: 6px">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px">
                        <span style="color: #4A515C; font-weight: 500">
                            {{ $assessment->userTimeText }}
                        </span>
                        @if($assessment->userSubmission && $assessment->userSubmission->score !== null)
                            <span style="font-weight: 800; color: #1D6B45; font-size: 14px">
                                Skor: {{ round($assessment->userSubmission->score, 1) }}
                            </span>
                        @endif
                    </div>

                    @if($assessment->userSubmission && $assessment->userSubmission->status === \App\Models\Submission::STATUS_REVISION)
                        <div style="padding: 6px 10px; border-radius: 8px; background: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 600">
                            ⚠ Guru meminta perbaikan. Klik untuk mengumpulkan ulang.
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div style="grid-column: 1 / -1; padding: 56px 24px; background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; text-align: center; color: #4A515C; display: flex; flex-direction: column; align-items: center; gap: 10px">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #F4F5F2; display: flex; align-items: center; justify-content: center; color: #8A92A0; margin-bottom: 4px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #15191F">Belum ada tugas</h3>
                <p style="margin: 0; font-size: 13px; max-width: 320px; line-height: 1.4">Tidak ada tugas dalam kategori ini untuk kelasmu saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
