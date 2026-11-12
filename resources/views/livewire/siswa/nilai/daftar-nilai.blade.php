<div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 32px">
    <div style="display: flex; flex-direction: column; gap: 6px">
        <h1 style="margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2">Nilai & Progres</h1>
        <p style="margin: 0; font-size: 15px; color: #4A515C">Pencapaian Tujuan Pembelajaran (TP) untuk kelasmu.</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 24px">
        @foreach($elements as $element)
            @if($element->learningObjectives->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 16px">
                    <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #15191F; border-bottom: 2px solid #EDEEEA; padding-bottom: 8px;">Elemen: {{ $element->name }}</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px">
                        @foreach($element->learningObjectives as $tp)
                            @php
                                $mastery = $masteries->get($tp->id);
                                $kktp = $tp->kktp ?? $defaultKktp;
                                
                                $status = $mastery ? $mastery->status : 'belum';
                                $score = $mastery ? $mastery->best_score : null;
                                
                                $statusColors = [
                                    'belum' => ['bg' => '#F4F5F2', 'text' => '#4A515C'],
                                    'remedial' => ['bg' => '#FBEDE5', 'text' => '#D32F2F'],
                                    'tercapai' => ['bg' => '#E7F3EC', 'text' => '#1D6B45'],
                                    'pengayaan' => ['bg' => '#E8EDFC', 'text' => '#2347C4'],
                                ];
                                
                                $color = $statusColors[$status];
                            @endphp
                            <div style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 16px; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 24px">
                                <div style="display: flex; flex-direction: column; gap: 4px; flex: 1">
                                    <div style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 13px; font-weight: 700; color: #4A515C">{{ $tp->code }}</div>
                                    <div style="font-size: 15px; font-weight: 600; color: #15191F; line-height: 1.4">{{ $tp->description }}</div>
                                </div>
                                <div style="display: flex; gap: 24px; align-items: center">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px">
                                        <span style="font-size: 11px; color: #6D747D; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em">KKTP</span>
                                        <span style="font-size: 16px; font-weight: 800; color: #15191F">{{ $kktp }}</span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px">
                                        <span style="font-size: 11px; color: #6D747D; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em">Nilai</span>
                                        <span style="font-size: 16px; font-weight: 800; color: {{ $score !== null && $score < $kktp ? '#D32F2F' : '#15191F' }}">{{ $score !== null ? round($score, 1) : '-' }}</span>
                                    </div>
                                    <div style="padding: 6px 12px; border-radius: 8px; background: {{ $color['bg'] }}; color: {{ $color['text'] }}; font-size: 13px; font-weight: 700; text-transform: capitalize; min-width: 80px; text-align: center">
                                        {{ $status }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
