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
        :root {
            --bg: #f3f6fb;
            --card: #ffffff;
            --line: #dbe4f0;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #1d4ed8;
            --primary-soft: #dbeafe;
            --danger: #dc2626;
            --success: #059669;
            --warning: #d97706;
            --radius: 14px;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; color: var(--text); background: var(--bg); }
        .admin-shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .sidebar { background: #0f172a; color: #e2e8f0; padding: 24px 18px; }
        .brand { font-size: 1.05rem; font-weight: 700; display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        .menu { display: grid; gap: 6px; }
        .menu a { color: #cbd5e1; text-decoration: none; padding: 10px 12px; border-radius: 10px; display: flex; gap: 9px; align-items: center; font-size: .92rem; }
        .menu a:hover, .menu a.active { background: rgba(148,163,184,.2); color: #fff; }
        .main { padding: 20px 24px 30px; }
        .topbar { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 12px 16px; margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center; }
        .topbar .user { color: var(--muted); font-size: .9rem; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px; margin-bottom: 16px; box-shadow: 0 8px 24px rgba(15,23,42,.04); }
        .page-header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap; margin-bottom: 16px; }
        .page-header h1 { margin:0; font-size: 1.35rem; }
        .page-header p { margin:6px 0 0; color: var(--muted); }
        .btn { display:inline-flex; align-items:center; gap:8px; border-radius:10px; border:1px solid transparent; padding:9px 13px; font-weight:600; text-decoration:none; cursor:pointer; }
        .btn-primary { background: var(--primary); color:#fff; }
        .btn-outline { border-color: var(--line); color: var(--text); background:#fff; }
        .btn-danger { background: var(--danger); color:#fff; }
        .btn-success { background: var(--success); color:#fff; }
        .btn-sm { padding:6px 10px; font-size: .82rem; }
        .toolbar { display:grid; gap:12px; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); align-items:end; }
        label { display:block; margin-bottom:6px; font-weight:600; font-size: .88rem; }
        input, select, textarea { width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font: inherit; }
        textarea { min-height: 92px; resize: vertical; }
        .table-wrap { overflow:auto; border:1px solid var(--line); border-radius: 12px; }
        table { width:100%; border-collapse: collapse; background:#fff; }
        th, td { padding:12px; border-bottom:1px solid #e6edf6; vertical-align: top; text-align:left; }
        th { background: #f8fbff; font-size:.85rem; text-transform: uppercase; letter-spacing:.03em; color:#334155; }
        tbody tr:hover { background: #fafcff; }
        .badge { display:inline-flex; padding:4px 10px; border-radius:999px; font-size:.75rem; font-weight:700; }
        .badge-success { background:#dcfce7; color:#166534; }
        .badge-danger { background:#fee2e2; color:#991b1b; }
        .badge-warning { background:#fef3c7; color:#92400e; }
        .badge-info { background:#dbeafe; color:#1e40af; }
        .empty-state { text-align:center; padding:28px 12px; color: var(--muted); }
        .grid-2 { display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:14px; }
        .full { grid-column: 1 / -1; }
        .alert { border-radius:10px; padding:12px 14px; margin-bottom:14px; border:1px solid transparent; }
        .alert-success { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
        .alert-danger { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
        .muted { color:var(--muted); font-size:.85rem; }
        @media (max-width: 1024px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar { padding-bottom: 8px; }
            .menu { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
        }
        @media (max-width: 768px) {
            .main { padding: 14px; }
            .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
    @yield('head')
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div class="brand"><i class="bi bi-droplet-half"></i> Air Bersih Desa</div>
        <nav class="menu">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="{{ route('desa.index') }}"><i class="bi bi-houses"></i> Desa</a>
            <a href="{{ route('kecamatan.index') }}"><i class="bi bi-map"></i> Kecamatan</a>
            <a href="{{ route('pelanggan.index') }}"><i class="bi bi-people"></i> Pelanggan</a>
            <a href="{{ route('tagihan.index') }}"><i class="bi bi-receipt"></i> Tagihan</a>
            <a href="{{ route('pembayaran.index') }}"><i class="bi bi-cash-stack"></i> Pembayaran</a>
            <a href="{{ route('laporan.index') }}"><i class="bi bi-bar-chart"></i> Laporan</a>
            <a href="{{ route('monitoring.index') }}"><i class="bi bi-geo-alt"></i> Monitoring</a>
            <a href="{{ route('meter_records.index') }}"><i class="bi bi-speedometer"></i> Meter Records</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar">
            <strong>Panel Admin</strong>
            <div class="user">{{ auth()->user()->name ?? 'Pengguna' }}</div>
        </div>
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
