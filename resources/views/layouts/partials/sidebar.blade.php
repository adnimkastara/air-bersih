<aside class="sidebar">
    <div class="brand">
        @include('layouts.partials.brand-media', [
            'imageUrl' => $branding['logo_icon_url'] ?? null,
            'appName' => $branding['app_name'] ?? null,
            'initials' => $branding['initials'] ?? null,
            'imgClass' => 'brand-logo',
            'fallbackClass' => 'brand-fallback-mark',
        ])
        <span class="brand-text">{{ $branding['app_name'] }}</span>
    </div>

    <nav class="menu">
        <div class="menu-group-title">Main Menu</div>
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>

        @if($user?->isKecamatanLevel() || $user?->isAdminDesa())
            <a class="{{ request()->routeIs('desa.*') ? 'active' : '' }}" href="{{ route('desa.index') }}"><i class="bi bi-houses"></i> Desa</a>
        @endif

        @if($user?->isKecamatanLevel())
            <a class="{{ request()->routeIs('kecamatan.*') ? 'active' : '' }}" href="{{ route('kecamatan.index') }}"><i class="bi bi-map"></i> Kecamatan</a>
        @endif

        @if($user?->isKecamatanLevel() || $user?->isAdminDesa() || $user?->isPetugasLapangan())
            @if(! $user?->isKecamatanLevel())
                <a class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }}" href="{{ route('pelanggan.index') }}"><i class="bi bi-people"></i> Pelanggan</a>
                <a class="{{ request()->routeIs('tagihan.*') ? 'active' : '' }}" href="{{ route('tagihan.index') }}"><i class="bi bi-receipt"></i> Tagihan</a>
                <a class="{{ request()->routeIs('meter_records.*') ? 'active' : '' }}" href="{{ route('meter_records.index') }}"><i class="bi bi-speedometer"></i> Meter Record</a>
                <a class="{{ request()->routeIs('pembayaran.*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}"><i class="bi bi-cash-stack"></i> Pembayaran</a>
            @endif
            @if($user?->isKecamatanLevel())
                <a class="{{ request()->routeIs('district-billings.index') ? 'active' : '' }}" href="{{ route('district-billings.index') }}"><i class="bi bi-building"></i> Tagihan Kecamatan</a>
                <a class="{{ request()->routeIs('district-billings.payments') ? 'active' : '' }}" href="{{ route('district-billings.payments') }}"><i class="bi bi-bank"></i> Pembayaran Kecamatan</a>
            @endif
            <a class="{{ request()->routeIs('monitoring.*') ? 'active' : '' }}" href="{{ route('monitoring.index') }}"><i class="bi bi-geo-alt"></i> Monitoring</a>
            <a class="{{ request()->routeIs('keluhan.*') ? 'active' : '' }}" href="{{ route('keluhan.index') }}"><i class="bi bi-exclamation-triangle"></i> Gangguan & Keluhan</a>
        @endif

        @if($user?->isKecamatanLevel() || $user?->isAdminDesa())
            <a class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}" href="{{ route('laporan.index') }}"><i class="bi bi-bar-chart"></i> Laporan</a>
            <div class="menu-group">
                <div class="menu-group-title">Pengaturan</div>
                <div class="submenu">
                    <a class="{{ request()->routeIs('settings.users.*') ? 'active' : '' }}" href="{{ route('settings.users.index') }}"><i class="bi bi-person-gear"></i> Manajemen User</a>
                    <a class="{{ request()->routeIs('settings.tarif.*') ? 'active' : '' }}" href="{{ route('settings.tarif.index') }}"><i class="bi bi-cash-coin"></i> Setting Tarif</a>
                    <a class="{{ request()->routeIs('settings.app.*') ? 'active' : '' }}" href="{{ route('settings.app.edit') }}"><i class="bi bi-sliders"></i> Setting Aplikasi</a>
                </div>
            </div>
        @endif

        <a class="{{ request()->routeIs('profile.password.*') ? 'active' : '' }}" href="{{ route('profile.password.edit') }}"><i class="bi bi-key"></i> Ganti Password</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
        </form>
    </nav>
</aside>
