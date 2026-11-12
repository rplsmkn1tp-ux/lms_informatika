@props(['title', 'description' => null, 'breadcrumbs' => null])

<header data-testid="app-header" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px">
    <div style="display: flex; flex-direction: column; gap: 6px; min-width: 0; flex: 1 1 420px">
        @if($breadcrumbs)
            <span style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; letter-spacing: 0.06em; color: #4A515C">{{ mb_strtoupper($breadcrumbs) }}</span>
        @endif
        <h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2">{{ $title }}</h1>
        @if($description)
            <p style="margin: 0; font-size: 15px; color: #4A515C; max-width: 680px">{{ $description }}</p>
        @endif
    </div>
    
    @if(trim($slot))
        <div style="display: flex; flex-wrap: wrap; gap: 10px">
            {{ $slot }}
        </div>
    @endif
</header>
