<div class="topbar">
    <div class="topbar-brand" aria-label="Brand {{ $branding['app_name'] }}">
        @if(!empty($branding['logo_url']))
            <img src="{{ $branding['logo_url'] }}" alt="Logo {{ $branding['app_name'] }}" class="topbar-logo" loading="lazy">
        @else
            <strong>{{ $branding['app_name'] }}</strong>
        @endif
        <div>
            <strong>{{ $branding['app_name'] }}</strong>
            <div style="font-size:12px;color:#64748b;">{{ $branding['app_subtitle'] }}</div>
        </div>
    </div>

    <div>{{ $user?->name }} ({{ $user?->role?->name }})</div>
</div>
