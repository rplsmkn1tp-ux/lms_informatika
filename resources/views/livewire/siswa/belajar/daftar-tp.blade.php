<div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 32px">

    <div style="display: flex; flex-direction: column; gap: 12px">
        <nav aria-label="Breadcrumb" style="font-size: 13px; color: #4A515C; display: flex; flex-wrap: wrap; gap: 6px; align-items: center">
            <a href="{{ route('siswa.beranda') }}" style="color: #4A515C; text-decoration: none;">Beranda</a><span aria-hidden="true">/</span>
            <span style="color: #15191F; font-weight: 600">{{ $element->name }}</span>
        </nav>

        <div style="display: flex; flex-direction: column; gap: 2px">
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600; color: #2347C4">ELEMEN {{ $element->code }}</span>
            <h1 style="margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2">{{ $element->name }}</h1>
            @if($element->description)
                <p style="margin: 0; font-size: 15px; color: #4A515C; max-width: 800px; margin-top: 8px;">{{ $element->description }}</p>
            @endif
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 24px">
        @forelse($tps as $index => $tp)
            <article style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px">
                <div style="display: flex; flex-direction: column; gap: 4px">
                    <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; font-weight: 600; color: #2347C4">{{ $tp->code }}</span>
                    <h2 style="margin: 0; font-size: 20px; font-weight: 800; line-height: 1.3">{{ $tp->title }}</h2>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px">
                    @forelse($tp->lessons as $lesson)
                        @php
                            $isDone = \App\Models\LessonProgress::where('student_id', auth()->id())
                                ->where('lesson_id', $lesson->id)->exists();
                            
                            $classroomPivot = $lesson->classrooms->first();
                            $isLocked = $classroomPivot && $classroomPivot->pivot->available_from && \Illuminate\Support\Carbon::parse($classroomPivot->pivot->available_from)->isFuture();
                        @endphp
                        
                        @if($isLocked)
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border-radius: 12px; background: #F4F5F2; color: #8F95A0; border: 1px solid transparent; cursor: not-allowed;">
                                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                    <span style="width: 36px; height: 36px; flex: none; border-radius: 99px; background: #E2E4DF; color: #8F95A0; display: flex; align-items: center; justify-content: center">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                                    </span>
                                    <span style="font-size: 15px; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                        {{ $lesson->code }} {{ $lesson->title }}
                                    </span>
                                </div>
                                <span style="font-size: 13px; color: #8F95A0; flex: none;">
                                    Terkunci
                                </span>
                            </div>
                        @else
                            <a href="{{ route('siswa.belajar.materi', ['element' => $element->code, 'tp' => $tp->code, 'lesson' => $lesson->code]) }}" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border-radius: 12px; background: {{ $isDone ? '#E8EDFC' : '#F6F7F4' }}; text-decoration: none; color: #15191F; border: 1px solid transparent;">
                                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                    <span style="width: 36px; height: 36px; flex: none; border-radius: 99px; background: {{ $isDone ? '#2347C4' : '#C9CDC4' }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center">
                                        @if($isDone)
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                                        @else
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
                                        @endif
                                    </span>
                                    <span style="font-size: 15px; font-weight: 600; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                        {{ $lesson->code }} {{ $lesson->title }}
                                    </span>
                                </div>
                                <span style="font-size: 13px; color: #4A515C; flex: none;">
                                    {{ $lesson->estimated_minutes ? '±' . $lesson->estimated_minutes . ' menit' : 'Materi' }}
                                </span>
                            </a>
                        @endif
                    @empty
                        <div style="padding: 12px 16px; border-radius: 12px; background: #F4F5F2; color: #4A515C; font-size: 14px;">
                            Belum ada materi yang diterbitkan pada TP ini.
                        </div>
                    @endforelse
                </div>
            </article>
        @empty
            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 40px 24px; text-align: center; display: flex; flex-direction: column; gap: 12px; align-items: center">
                <span style="width: 64px; height: 64px; border-radius: 99px; background: #F4F5F2; color: #4A515C; display: flex; align-items: center; justify-content: center">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                </span>
                <div style="display: flex; flex-direction: column; gap: 4px">
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700">Belum Ada Materi</h3>
                    <p style="margin: 0; font-size: 15px; color: #4A515C">Guru pengampu kelasmu belum menerbitkan materi untuk elemen ini.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
