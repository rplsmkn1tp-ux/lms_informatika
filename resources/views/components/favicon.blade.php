@php
    $customLogo = \App\Models\Setting::getValue('app_logo');
    $logoVer = \App\Models\Setting::getValue('logo_version', '1');
    $appName = \App\Models\Setting::getValue('app_name', 'Ruang Informatika');
    $iconUrl = $customLogo ? asset('storage/' . $customLogo) : asset('favicon.svg');
    $appleIconUrl = $customLogo ? asset('storage/' . $customLogo) : asset('apple-touch-icon.png');
    $isSvg = $customLogo ? str_ends_with(strtolower($customLogo), '.svg') : true;
@endphp

@if($customLogo)
    <link rel="icon" href="{{ $iconUrl }}?v={{ $logoVer }}" @if($isSvg) type="image/svg+xml" @endif>
    <link rel="apple-touch-icon" href="{{ $appleIconUrl }}?v={{ $logoVer }}">
@else
    <link rel="icon" href="{{ asset('favicon.svg') }}?v={{ $logoVer }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v={{ $logoVer }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ $logoVer }}">
@endif
<link rel="manifest" href="{{ route('webmanifest') }}">
<meta name="apple-mobile-web-app-title" content="{{ $appName }}">
<meta name="application-name" content="{{ $appName }}">
<meta name="theme-color" content="#2347C4">

