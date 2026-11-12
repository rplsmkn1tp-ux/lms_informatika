@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-zinc-200/80 p-6 shadow-sm']) }} style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 22px; display: flex; flex-direction: column; gap: 16px">
    @if($title)
        <div style="display: flex; flex-direction: column; gap: 4px">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #15191F">{{ $title }}</h2>
            @if($description)
                <span style="font-size: 13px; color: #4A515C">{{ $description }}</span>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
