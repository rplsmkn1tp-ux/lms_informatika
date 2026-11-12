@if($isNotFound || $isLocked)
<div style="max-width: 1360px; margin: 0 auto; padding: 28px 24px 64px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 50vh; text-align: center">
    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 40px; max-width: 480px; width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.05)">
        <span style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background: #FDF1DC; color: #F2A93B; margin-bottom: 24px">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </span>
        <h2 style="margin: 0 0 12px; font-size: 20px; font-weight: 800; color: #15191F">Oops!</h2>
        <p style="margin: 0 0 24px; font-size: 15px; color: #4A515C; line-height: 1.5">{{ $errorMessage }}</p>
        <a href="{{ route('siswa.belajar.tp', $element->code ?? 'BK') }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 20px; border-radius: 12px; background: #2347C4; color: #FFFFFF; font-weight: 700; text-decoration: none; font-size: 14px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali ke Daftar Materi
        </a>
    </div>
</div>
@else
<div style="max-width: 1360px; margin: 0 auto; padding: 28px 24px 64px; display: flex; flex-wrap: wrap; gap: 32px; align-items: flex-start">

    <!-- Sidebar Daftar Subbab -->
    <aside aria-label="Daftar subbab" style="flex: 1 1 240px; min-width: 0; background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; gap: 16px">
        <div style="display: flex; flex-direction: column; gap: 4px">
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.06em; color: #4A515C">{{ $tp->code }} · {{ strtoupper($element->name) }}</span>
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; line-height: 1.3">{{ $tp->title }}</h2>
        </div>
        
        @if($totalLessonsCount > 0)
            <div style="display: flex; flex-direction: column; gap: 6px">
                <div style="height: 6px; border-radius: 99px; background: #EDEEEA; overflow: hidden">
                    <div style="width: {{ ($completedLessonsCount / $totalLessonsCount) * 100 }}%; height: 100%; border-radius: 99px; background: #2347C4"></div>
                </div>
                <span style="font-size: 13px; color: #4A515C">{{ $completedLessonsCount }} dari {{ $totalLessonsCount }} subbab selesai</span>
            </div>
        @endif

        <ol style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 2px">
            @foreach($lessons as $idx => $lsn)
                @php
                    $isActive = $lsn->id === $lesson->id;
                    $isCompleted = \App\Models\LessonProgress::where('student_id', auth()->id())->where('lesson_id', $lsn->id)->exists();
                    $classroomPivot = $lsn->classrooms->first();
                    $isLockedItem = $classroomPivot && $classroomPivot->pivot->available_from && \Illuminate\Support\Carbon::parse($classroomPivot->pivot->available_from)->isFuture();
                @endphp
                <li>
                    @if($isLockedItem)
                        <div style="display: flex; align-items: center; justify-content: space-between; min-height: 44px; padding: 6px 10px; border-radius: 10px; color: #8F95A0; font-size: 14px; cursor: not-allowed; opacity: 0.7;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                <span style="width: 24px; height: 24px; flex: none; box-sizing: border-box; border-radius: 99px; border: 2px solid #E2E4DF; display: flex; align-items: center; justify-content: center">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                                </span>
                                <span>{{ $lsn->code }} {{ $lsn->title }}</span>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $lsn->code]) }}" style="display: flex; align-items: center; gap: 12px; min-height: 44px; padding: 6px 10px; border-radius: 10px; {{ $isActive ? 'background: #E8EDFC; font-weight: 700;' : 'color: #4A515C;' }} text-decoration: none; font-size: 14px">
                            @if($isCompleted)
                                <span style="width: 24px; height: 24px; flex: none; border-radius: 99px; background: #2347C4; color: #FFFFFF; display: flex; align-items: center; justify-content: center">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                                </span>
                            @elseif($isActive)
                                <span style="width: 24px; height: 24px; flex: none; box-sizing: border-box; border-radius: 99px; border: 2px solid #2347C4; background: #FFFFFF; display: flex; align-items: center; justify-content: center">
                                    <span style="width: 8px; height: 8px; border-radius: 99px; background: #2347C4"></span>
                                </span>
                            @else
                                <span style="width: 24px; height: 24px; flex: none; box-sizing: border-box; border-radius: 99px; border: 2px solid #C9CDC4; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; display: flex; align-items: center; justify-content: center">{{ $idx + 1 }}</span>
                            @endif
                            <span style="{{ $isActive ? 'color: #15191F;' : '' }}">{{ $lsn->code }} {{ $lsn->title }}</span>
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </aside>

    <!-- Konten Utama -->
    <article style="flex: 999 1 560px; min-width: 0; max-width: 780px; display: flex; flex-direction: column; gap: 28px">
        <nav aria-label="Breadcrumb" style="font-size: 13px; color: #4A515C; display: flex; flex-wrap: wrap; gap: 6px; align-items: center">
            <a href="{{ route('siswa.beranda') }}" style="color: #4A515C; text-decoration: none;">Beranda</a><span aria-hidden="true">/</span>
            <a href="{{ route('siswa.belajar.tp', $element->code) }}" style="color: #4A515C; text-decoration: none;">{{ $element->name }}</a><span aria-hidden="true">/</span>
            <span style="color: #4A515C">{{ $tp->title }}</span><span aria-hidden="true">/</span>
            <span style="color: #15191F; font-weight: 600">{{ $lesson->code }} {{ $lesson->title }}</span>
        </nav>

        <div style="display: flex; flex-direction: column; gap: 12px">
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.06em; color: #2347C4; font-weight: 600">{{ $lesson->code }} · {{ strtoupper($tp->title) }}</span>
            <h1 style="margin: 0; font-size: 40px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.15">{{ $lesson->title }}</h1>
            @if($lesson->summary)
                <p style="margin: 0; font-size: 17px; color: #2F353E; max-width: 660px">{{ $lesson->summary }}</p>
            @endif
            
            <div style="display: flex; flex-wrap: wrap; gap: 8px 20px; font-size: 13px; color: #4A515C">
                @if($lesson->estimated_minutes)
                    <span style="display: inline-flex; align-items: center; gap: 6px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                        ±{{ $lesson->estimated_minutes }} menit
                    </span>
                @endif
                @if($lesson->video_url)
                    <span style="display: inline-flex; align-items: center; gap: 6px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M10 9l5 3-5 3z"></path></svg>
                        Video
                    </span>
                @endif
                @if($lesson->interactive_component)
                    <span style="display: inline-flex; align-items: center; gap: 6px">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="6" width="12" height="12" rx="2"></rect><rect x="9.5" y="9.5" width="5" height="5"></rect><path d="M9 3v3M15 3v3M9 18v3M15 18v3M3 9h3M3 15h3M18 9h3M18 15h3"></path></svg>
                        Simulasi
                    </span>
                @endif
            </div>
        </div>

        <!-- TABS -->
        <div role="tablist" aria-label="Bagian subbab" style="display: flex; gap: 4px; border-bottom: 1px solid #E2E4DF; overflow-x: auto">
            <button wire:click="setTab('materi')" type="button" role="tab" aria-selected="{{ $tab === 'materi' ? 'true' : 'false' }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 0 16px; border: 0; border-bottom: 3px solid {{ $tab === 'materi' ? '#2347C4' : 'transparent' }}; margin-bottom: -1px; background: transparent; font: inherit; font-size: 15px; font-weight: {{ $tab === 'materi' ? '700' : '600' }}; color: {{ $tab === 'materi' ? '#15191F' : '#4A515C' }}; cursor: pointer; white-space: nowrap">
                Materi
            </button>
            <button wire:click="setTab('diskusi')" type="button" role="tab" aria-selected="{{ $tab === 'diskusi' ? 'true' : 'false' }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 0 16px; border: 0; border-bottom: 3px solid {{ $tab === 'diskusi' ? '#2347C4' : 'transparent' }}; margin-bottom: -1px; background: transparent; font: inherit; font-size: 15px; font-weight: {{ $tab === 'diskusi' ? '700' : '600' }}; color: {{ $tab === 'diskusi' ? '#15191F' : '#4A515C' }}; cursor: pointer; white-space: nowrap">
                Diskusi 
                @if($discussionCount > 0)
                    <span style="padding: 1px 8px; border-radius: 99px; background: #EFF0EC; color: #4A515C; font-size: 12px; font-weight: 700">{{ $discussionCount }}</span>
                @endif
            </button>
            <button wire:click="setTab('catatan')" type="button" role="tab" aria-selected="{{ $tab === 'catatan' ? 'true' : 'false' }}" style="display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 0 16px; border: 0; border-bottom: 3px solid {{ $tab === 'catatan' ? '#2347C4' : 'transparent' }}; margin-bottom: -1px; background: transparent; font: inherit; font-size: 15px; font-weight: {{ $tab === 'catatan' ? '700' : '600' }}; color: {{ $tab === 'catatan' ? '#15191F' : '#4A515C' }}; cursor: pointer; white-space: nowrap">
                Catatanku
            </button>
        </div>

        @if($tab === 'materi')
            <div style="display: flex; flex-direction: column; gap: 32px">
                @if($lesson->video_url)
                    <div style="position: relative; aspect-ratio: 16 / 9; border-radius: 18px; background: #15191F; overflow: hidden; display: flex; align-items: center; justify-content: center">
                        <span style="position: absolute; top: 16px; left: 18px; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.06em; color: #9AA3B2">[ VIDEO PEMBELAJARAN ]</span>
                        <iframe width="100%" height="100%" src="{{ str_replace('watch?v=', 'embed/', $lesson->video_url) }}" title="Video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                @endif
                
                @if($lesson->content)
                    <div style="line-height: 1.6; font-size: 16px; color: #2F353E;">
                        {!! str_replace('[simulasi:von-neumann]', view('components.siswa.belajar.simulasi-von-neumann')->render(), clean($lesson->content)) !!}
                    </div>
                @endif

                @if($lesson->interactive_component === 'von-neumann' && !str_contains($lesson->content ?? '', '[simulasi:von-neumann]'))
                    <section style="display: flex; flex-direction: column; gap: 14px">
                        <h2 style="margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.01em">Simulasi Input–Proses–Output</h2>
                        <p style="margin: 0; font-size: 16px; color: #2F353E">Perhatikan komponen yang menyala saat kamu menghitung <strong>12 + 30</strong>.</p>
                        <x-siswa.belajar.simulasi-von-neumann />
                    </section>
                @endif
                
                <nav aria-label="Navigasi subbab" style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: center; padding-top: 24px; border-top: 1px solid #E2E4DF">
                    @if($prevLesson)
                        <a href="{{ route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $prevLesson->code]) }}" style="flex: 1 1 200px; display: flex; align-items: center; gap: 12px; min-height: 56px; padding: 10px 14px; border-radius: 14px; border: 1px solid #E2E4DF; background: #FFFFFF; text-decoration: none; color: #15191F">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"></path></svg>
                            <span style="display: flex; flex-direction: column; line-height: 1.3"><span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; color: #4A515C">SEBELUMNYA</span><span style="font-size: 14px; font-weight: 700">{{ $prevLesson->code }} {{ $prevLesson->title }}</span></span>
                        </a>
                    @else
                        <div style="flex: 1 1 200px;"></div>
                    @endif
                    
                    @if(!$this->isDone)
                        <button wire:click="toggleDone" type="button" style="flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px; min-height: 56px; padding: 0 22px; border-radius: 14px; border: 0; background: #2347C4; color: #FFFFFF; font: inherit; font-size: 15px; font-weight: 700; cursor: pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>Tandai selesai
                        </button>
                    @else
                        <button wire:click="toggleDone" type="button" aria-pressed="true" style="flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px; min-height: 56px; padding: 0 22px; border-radius: 14px; border: 2px solid #2347C4; background: #E8EDFC; color: #1A3596; font: inherit; font-size: 15px; font-weight: 700; cursor: pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M8 12.5l2.5 2.5L16 9.5"></path></svg>Subbab selesai
                        </button>
                    @endif

                    @if($nextLesson)
                        <a href="{{ route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $nextLesson->code]) }}" style="flex: 1 1 200px; display: flex; align-items: center; justify-content: flex-end; gap: 12px; min-height: 56px; padding: 10px 14px; border-radius: 14px; border: 1px solid #E2E4DF; background: #FFFFFF; text-decoration: none; color: #15191F; text-align: right">
                            <span style="display: flex; flex-direction: column; line-height: 1.3"><span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; color: #4A515C">BERIKUTNYA</span><span style="font-size: 14px; font-weight: 700">{{ $nextLesson->code }} {{ $nextLesson->title }}</span></span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                        </a>
                    @else
                        <div style="flex: 1 1 200px;"></div>
                    @endif
                </nav>
            </div>
        @endif

        @if($tab === 'diskusi')
            <section aria-label="Diskusi kelas" style="display: flex; flex-direction: column; gap: 16px">
                <form wire:submit.prevent="sendDiscussion" style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px; display: flex; flex-direction: column; gap: 10px">
                    <label for="tanya" style="font-size: 14px; font-weight: 700">Tulis pertanyaan atau tanggapan</label>
                    <textarea wire:model="discussionBody" id="tanya" rows="3" placeholder="Contoh: apa bedanya RAM dan SSD?" style="font: inherit; font-size: 15px; padding: 12px 14px; border-radius: 12px; border: 1px solid #C9CDC4; resize: vertical; color: #15191F" required></textarea>
                    <div style="display: flex; justify-content: flex-end">
                        <button type="submit" style="min-height: 44px; padding: 0 20px; border-radius: 12px; border: 0; background: #2347C4; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer">Kirim</button>
                    </div>
                </form>

                @forelse($discussions as $disc)
                    <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px; display: flex; gap: 14px">
                        <span style="width: 40px; height: 40px; flex: none; border-radius: 99px; background: #FDF1DC; color: #8A4B00; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px">{{ $disc->user->initials() }}</span>
                        <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 1;">
                            <p style="margin: 0; font-size: 14px"><strong>{{ $disc->user->name }}</strong> <span style="color: #4A515C">· {{ $disc->created_at->diffForHumans() }}</span></p>
                            <p style="margin: 0; font-size: 15px; color: #2F353E; white-space: pre-line;">{{ $disc->body }}</p>
                            
                            @foreach($disc->replies as $reply)
                                @if(!$reply->is_hidden)
                                    <div style="margin-top: 10px; display: flex; gap: 12px; padding: 14px; border-radius: 14px; background: #F4F5F2">
                                        <span style="width: 36px; height: 36px; flex: none; border-radius: 99px; background: #E8EDFC; color: #2347C4; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px">{{ $reply->user->initials() }}</span>
                                        <div style="display: flex; flex-direction: column; gap: 4px; min-width: 0">
                                            <p style="margin: 0; font-size: 14px">
                                                <strong>{{ $reply->user->name }}</strong> 
                                                @if($reply->user->role === 'guru')
                                                    <span style="padding: 1px 7px; border-radius: 99px; background: #2347C4; color: #FFFFFF; font-size: 11px; font-weight: 700">Guru</span> 
                                                @endif
                                                <span style="color: #4A515C">· {{ $reply->created_at->diffForHumans() }}</span>
                                            </p>
                                            <p style="margin: 0; font-size: 15px; color: #2F353E; white-space: pre-line;">{{ $reply->body }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div style="padding: 24px; text-align: center; color: #4A515C; background: #FFFFFF; border-radius: 18px; border: 1px solid #E2E4DF;">
                        Belum ada diskusi untuk materi ini.
                    </div>
                @endforelse
            </section>
        @endif

        @if($tab === 'catatan')
            <form wire:submit.prevent="saveNote" aria-label="Catatanku" style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px; display: flex; flex-direction: column; gap: 10px">
                <label for="catatan" style="font-size: 14px; font-weight: 700">Catatan pribadi <span style="font-weight: 500; color: #4A515C">(hanya terlihat olehmu)</span></label>
                @if(session()->has('success'))
                    <span style="color: #1D6B45; font-size: 14px; margin-bottom: 8px;">{{ session('success') }}</span>
                @endif
                <textarea wire:model.defer="noteBody" id="catatan" rows="8" placeholder="Tulis ringkasan dengan kata-katamu sendiri…" style="font: inherit; font-size: 15px; padding: 12px 14px; border-radius: 12px; border: 1px solid #C9CDC4; resize: vertical; color: #15191F"></textarea>
                <div style="display: flex; justify-content: flex-end">
                    <button type="submit" style="min-height: 44px; padding: 0 20px; border-radius: 12px; border: 0; background: #2347C4; color: #FFFFFF; font: inherit; font-weight: 700; cursor: pointer">Simpan catatan</button>
                </div>
            </form>
        @endif
    </article>

    <!-- Panel Pendukung (Kanan) -->
    <aside aria-label="Panel pendukung" style="flex: 1 1 260px; min-width: 0; display: flex; flex-direction: column; gap: 20px">
        @if($lesson->attachments && $lesson->attachments->count() > 0)
            <section aria-labelledby="unduh-title" style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; gap: 10px">
                <h2 id="unduh-title" style="margin: 0 0 4px; font-size: 16px; font-weight: 700">Materi pendukung</h2>
                @foreach($lesson->attachments as $attachment)
                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" style="display: flex; align-items: center; gap: 12px; min-height: 52px; padding: 8px 12px; border-radius: 12px; background: #F4F5F2; text-decoration: none; color: #15191F">
                        <span style="color: #2347C4; display: flex">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"></path><path d="M14 3v5h5"></path></svg>
                        </span>
                        <span style="flex: 1; display: flex; flex-direction: column; line-height: 1.3">
                            <span style="font-size: 14px; font-weight: 700">{{ $attachment->label ?? 'Lampiran' }}</span>
                            <span style="font-size: 12px; color: #4A515C">Lampiran</span>
                        </span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"></path></svg>
                    </a>
                @endforeach
            </section>
        @endif

        <section aria-labelledby="tanya-title" style="background: #15191F; color: #FFFFFF; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; gap: 10px">
            <h2 id="tanya-title" style="margin: 0; font-size: 16px; font-weight: 700">Ada yang belum jelas?</h2>
            <p style="margin: 0; font-size: 14px; color: #C5CBD6">Tanyakan di diskusi kelas. Guru dan teman sekelas bisa membantu menjawab.</p>
            <button wire:click="setTab('diskusi')" type="button" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; margin-top: 4px; border-radius: 12px; border: 0; background: #F2A93B; color: #15191F; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v11H9l-5 4z"></path></svg>
                Tanya di diskusi
            </button>
        </section>
    </aside>

</div>
@endif
