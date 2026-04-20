<div class="topbar">
    <div class="topbar-brand" aria-label="Brand {{ $branding['app_name'] }}">
        @include('layouts.partials.brand-media', [
            'imageUrl' => $branding['logo_url'] ?? null,
            'appName' => $branding['app_name'] ?? null,
            'initials' => null,
            'imgClass' => 'topbar-logo',
            'fallbackTextClass' => '',
        ])
        <div>
            <strong>{{ $branding['app_name'] }}</strong>
            <div style="font-size:12px;color:#64748b;">{{ $branding['app_subtitle'] }}</div>
        </div>
    </div>

    <div>{{ $user?->name }} ({{ $user?->role?->name }})</div>
</div>
