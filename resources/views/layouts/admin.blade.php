<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Air Bersih Desa')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg:#f3f6fb; --card:#ffffff; --line:#dbe4f0; --text:#0f172a; --muted:#64748b; --primary:#1d4ed8; --danger:#dc2626; --radius:14px; }
        *{box-sizing:border-box;} body{margin:0;font-family:'Inter',sans-serif;color:var(--text);background:var(--bg);} .admin-shell{display:grid;grid-template-columns:260px 1fr;min-height:100vh;}
        .sidebar{background:#0f172a;color:#e2e8f0;padding:24px 18px;} .brand{font-size:1.05rem;font-weight:700;display:flex;gap:10px;align-items:center;margin-bottom:20px;}
        .menu{display:grid;gap:6px;} .menu a,.menu button{color:#cbd5e1;text-decoration:none;padding:10px 12px;border-radius:10px;display:flex;gap:9px;align-items:center;font-size:.92rem;background:none;border:none;text-align:left;width:100%;cursor:pointer;}
        .menu a:hover,.menu a.active,.menu button:hover{background:rgba(148,163,184,.2);color:#fff;} .menu-group{margin-top:8px;} .menu-group-title{padding:10px 12px 6px;font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;} .submenu{display:grid;gap:6px;padding-left:8px;} .main{padding:20px 24px 30px;} .topbar{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;} .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:16px;margin-bottom:14px;} .grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;} .grid-2 .full{grid-column:1/-1;} label{display:block;font-size:.82rem;color:var(--muted);margin-bottom:6px;} input,select,textarea{width:100%;border:1px solid var(--line);border-radius:10px;padding:10px 12px;background:#fff;} .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 12px;border-radius:10px;border:1px solid transparent;text-decoration:none;cursor:pointer;} .btn-primary{background:var(--primary);color:#fff;} .btn-outline{border-color:var(--line);color:var(--text);background:#fff;} .btn-danger{background:var(--danger);color:#fff;} .btn-sm{padding:6px 9px;font-size:.82rem;} .toolbar{display:grid;grid-template-columns:2fr 1fr;gap:10px;align-items:end;} .table-wrap{overflow:auto;} table{width:100%;border-collapse:collapse;} th,td{padding:10px;border-bottom:1px solid var(--line);text-align:left;font-size:.9rem;} .badge{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:.76rem;font-weight:600;} .badge-success{background:#dcfce7;color:#166534;} .badge-danger{background:#fee2e2;color:#991b1b;} @media(max-width:960px){.admin-shell{grid-template-columns:1fr;}.grid-2,.toolbar{grid-template-columns:1fr;}}
    </style>
    @yield('head')
</head>
<body>
@php($user = auth()->user())
<div class="admin-shell">
    <aside class="sidebar">
        <div class="brand"><i class="bi bi-droplet-half"></i> Air Bersih Desa</div>
        <nav class="menu">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>

            @if($user?->isRoot() || $user?->isAdminDesa())
                <a href="{{ route('desa.index') }}"><i class="bi bi-houses"></i> Desa</a>
            @endif

            @if($user?->isRoot())
                <a href="{{ route('kecamatan.index') }}"><i class="bi bi-map"></i> Kecamatan</a>
            @endif

            @if($user?->isRoot() || $user?->isAdminDesa() || $user?->isPetugasLapangan())
                <a href="{{ route('pelanggan.index') }}"><i class="bi bi-people"></i> Pelanggan</a>
                <a href="{{ route('tagihan.index') }}"><i class="bi bi-receipt"></i> Tagihan</a>
                @if($user?->isRoot() || $user?->isAdminDesa())
                    <a href="{{ route('district-billings.index') }}"><i class="bi bi-building"></i> Tagihan Kecamatan</a>
                    <a href="{{ route('district-billings.payments') }}"><i class="bi bi-bank"></i> Pembayaran Kecamatan</a>
                @endif
                <a href="{{ route('meter_records.index') }}"><i class="bi bi-speedometer"></i> Meter Record</a>
                <a href="{{ route('pembayaran.index') }}"><i class="bi bi-cash-stack"></i> Pembayaran</a>
                <a href="{{ route('monitoring.index') }}"><i class="bi bi-geo-alt"></i> Monitoring</a>
            @endif

            @if($user?->isRoot() || $user?->isAdminDesa())
                <a href="{{ route('laporan.index') }}"><i class="bi bi-bar-chart"></i> Laporan</a>
                <div class="menu-group">
                    <div class="menu-group-title">Pengaturan</div>
                    <div class="submenu">
                        <a href="{{ route('settings.users.index') }}"><i class="bi bi-person-gear"></i> Manajemen User</a>
                        <a href="{{ route('settings.tarif.index') }}"><i class="bi bi-cash-coin"></i> Setting Tarif</a>
                        <a href="{{ route('settings.app.edit') }}"><i class="bi bi-sliders"></i> Setting Aplikasi</a>
                    </div>
                </div>
            @endif

            <a href="{{ route('profile.password.edit') }}"><i class="bi bi-key"></i> Ganti Password</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar">
            <strong>Panel Admin</strong>
            <div>{{ $user?->name }} ({{ $user?->role?->name }})</div>
        </div>
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
