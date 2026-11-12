@props(['title' => null, 'description' => null])

<section style="background: #FFFFFF; border: 1px solid #E2E4DF; border-radius: 18px; padding: 20px 22px; display: flex; flex-direction: column; gap: 16px" {{ $attributes }}>
    @if($title)
        <div style="display: flex; flex-direction: column; gap: 4px">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800">{{ $title }}</h2>
            @if($description)
                <span style="font-size: 13px; color: #4A515C">{{ $description }}</span>
            @endif
        </div>
    @endif
    {{ $slot }}
</section>
