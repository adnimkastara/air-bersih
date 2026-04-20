@php
    $mainLogoSvgPath = public_path('assets/logo/logo-main.svg');
    $mainLogoUrl = asset('assets/logo/logo-main.svg');
    $hasMainLogo = file_exists($mainLogoSvgPath);
@endphp

<div class="topbar">
    <div class="topbar-brand" aria-label="Brand Tirta Sejahtera">
        @if($hasMainLogo)
            <img src="{{ $mainLogoUrl }}" alt="Logo Tirta Sejahtera" class="topbar-logo" loading="lazy">
        @else
            <strong>Tirta Sejahtera</strong>
        @endif
    </div>

    <div>{{ $user?->name }} ({{ $user?->role?->name }})</div>
</div>
