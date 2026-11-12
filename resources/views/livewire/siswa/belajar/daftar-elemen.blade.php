<div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 32px">
    <div style="display: flex; flex-direction: column; gap: 2px">
        <h1 style="margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.01em">Elemen Informatika</h1>
        <p style="margin: 0; font-size: 15px; color: #4A515C">Pilih elemen untuk mulai mempelajari materi dan mengerjakan tugas.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; align-items: start">
        @foreach($elements as $element)
            <article style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 20px">
                <div style="display: flex; flex-direction: column; gap: 12px">
                    <div style="display: flex; align-items: center; gap: 10px">
                        <span style="padding: 4px 10px; border-radius: 8px; background: #2347C4; color: #FFFFFF; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 13px; font-weight: 600">{{ $element->code }}</span>
                    </div>
                    <h2 style="margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.01em; line-height: 1.2">{{ $element->name }}</h2>
                    @if($element->description)
                    <p style="margin: 0; font-size: 14px; color: #4A515C; line-height: 1.5">{{ $element->description }}</p>
                    @endif
                </div>
                <a href="{{ route('siswa.belajar.tp', $element->code) }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 18px; border-radius: 12px; border: 1px solid #2347C4; background: #2347C4; color: #FFFFFF; font-weight: 700; font-size: 15px; text-decoration: none; width: 100%;">Buka elemen {{ $element->code }}</a>
            </article>
        @endforeach
    </div>
</div>
