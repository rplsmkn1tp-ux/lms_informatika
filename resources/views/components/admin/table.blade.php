@props(['headers' => []])

<div style="overflow-x: auto; overflow-y: visible; margin-top: -8px; margin-bottom: -14px; margin-left: -22px; margin-right: -22px">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px" {{ $attributes }}>
        @if(isset($head))
            <thead>
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @elseif(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" style="text-align: left; padding: 12px 22px; font-size: 12px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #4A515C; background: #F9FAF7; border-bottom: 1px solid #E2E4DF; white-space: nowrap">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
