<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dashboardTitle }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --bg-deep: #061a2f;
            --bg-blue: #0d2f53;
            --cyan: #23d5ff;
            --turquoise: #1cc5be;
            --soft-green: #76f6b4;
            --white: #f6fbff;
            --text-main: #e8f5ff;
            --text-soft: #9dc0d8;
            --line: rgba(255, 255, 255, 0.16);
            --glass: rgba(255, 255, 255, 0.08);
            --glass-strong: rgba(255, 255, 255, 0.14);
            --shadow: 0 20px 40px rgba(1, 23, 39, 0.35);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background:
                radial-gradient(circle at 12% 20%, rgba(35, 213, 255, 0.28), transparent 30%),
                radial-gradient(circle at 80% 0%, rgba(28, 197, 190, 0.25), transparent 30%),
                radial-gradient(circle at 82% 78%, rgba(118, 246, 180, 0.15), transparent 30%),
                linear-gradient(140deg, var(--bg-deep), #082745 45%, var(--bg-blue));
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: auto;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            filter: blur(70px);
            z-index: 0;
        }

        body::before {
            top: 8%;
            left: -80px;
            background: rgba(35, 213, 255, 0.28);
        }

        body::after {
            right: -80px;
            bottom: 10%;
            background: rgba(118, 246, 180, 0.2);
        }

        .layout {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 22px;
            padding: 24px;
            max-width: 1550px;
            margin: 0 auto;
        }

        .sidebar,
        .topbar,
        .hero,
        .panel {
            backdrop-filter: blur(14px);
            background: linear-gradient(160deg, var(--glass), rgba(255, 255, 255, 0.03));
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .sidebar {
            padding: 24px 18px;
            min-height: calc(100vh - 48px);
            position: sticky;
            top: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 26px;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(140deg, rgba(35, 213, 255, 0.28), rgba(28, 197, 190, 0.16));
            border: 1px solid rgba(255, 255, 255, 0.24);
            font-size: 1.4rem;
        }

        .brand h1 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .brand p { margin: 0; color: var(--text-soft); font-size: 0.82rem; }

        .menu-title {
            margin: 16px 8px 10px;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-soft);
        }

        .menu {
            display: grid;
            gap: 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            color: var(--text-main);
            text-decoration: none;
            border: 1px solid transparent;
            transition: .2s ease;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateX(2px);
        }

        .menu-item .icon {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-foot {
            margin-top: 22px;
            padding: 16px;
            border-radius: 14px;
            background: linear-gradient(145deg, rgba(35, 213, 255, 0.14), rgba(118, 246, 180, 0.09));
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: var(--text-main);
        }

        .sidebar-foot p { margin: 4px 0; color: var(--text-soft); font-size: .85rem; }

        .content {
            display: grid;
            gap: 18px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            gap: 14px;
        }

        .topbar h2 {
            margin: 0;
            font-size: 1.3rem;
        }

        .search {
            flex: 1;
            max-width: 420px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 10px 12px;
            color: var(--text-soft);
        }

        .search input {
            width: 100%;
            background: transparent;
            border: none;
            color: var(--white);
            outline: none;
            font-size: 0.92rem;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chip {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.08);
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-weight: 700;
            background: linear-gradient(130deg, rgba(35, 213, 255, 0.4), rgba(28, 197, 190, 0.25));
        }

        .hero {
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .hero::after {
            content: "";
            position: absolute;
            right: -40px;
            top: -80px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(35, 213, 255, 0.45) 0%, rgba(35, 213, 255, 0) 65%);
        }

        .hero h3 {
            margin: 0;
            font-size: 1.45rem;
            max-width: 640px;
        }

        .hero p { color: var(--text-soft); max-width: 700px; }

        .hero-stats {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        .hero-stats div {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px;
        }

        .hero-stats small { color: var(--text-soft); }
        .hero-stats strong { display: block; margin-top: 4px; font-size: 1.1rem; }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: linear-gradient(155deg, rgba(255, 255, 255, 0.1), rgba(255,255,255,0.03));
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(35, 213, 255, 0.22), transparent 65%);
            top: -30px;
            right: -30px;
        }

        .stat-card .big-icon { font-size: 1.5rem; }
        .stat-card h4 { margin: 8px 0 2px; font-size: 0.9rem; color: var(--text-soft); font-weight: 500; }
        .stat-card strong { font-size: 1.55rem; }

        .grid-main {
            display: grid;
            grid-template-columns: 1.55fr 1fr;
            gap: 12px;
        }

        .panel { padding: 18px; }

        .panel h4 { margin: 0 0 14px; font-size: 1rem; }

        .bar-chart {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
            align-items: end;
            height: 230px;
            padding: 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .bar-wrap { text-align: center; font-size: .75rem; color: var(--text-soft); }

        .bar {
            width: 100%;
            border-radius: 12px 12px 6px 6px;
            background: linear-gradient(180deg, rgba(35, 213, 255, 0.92), rgba(28, 197, 190, 0.55));
            box-shadow: 0 8px 20px rgba(35, 213, 255, 0.24);
        }

        .ring-wrap {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-bottom: 14px;
        }

        .ring {
            --angle: 60%;
            width: 128px;
            height: 128px;
            border-radius: 50%;
            background: conic-gradient(var(--soft-green) var(--angle), rgba(255,255,255,0.15) 0);
            display: grid;
            place-items: center;
        }

        .ring::after {
            content: "";
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: #083356;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .ring-label {
            position: absolute;
            font-size: .95rem;
            font-weight: 700;
        }

        .monitor-list, .complaint-list {
            display: grid;
            gap: 10px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.07);
        }

        .badge {
            font-size: .75rem;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(118, 246, 180, 0.16);
            color: #adffd4;
            border: 1px solid rgba(118, 246, 180, 0.35);
        }

        .badge.warn {
            color: #fff1b3;
            border-color: rgba(255, 215, 112, 0.35);
            background: rgba(255, 215, 112, 0.14);
        }

        .grid-bottom {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 12px;
        }

        .map {
            border-radius: 16px;
            min-height: 250px;
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg, rgba(35, 213, 255, 0.18), rgba(28, 197, 190, 0.12)),
                repeating-linear-gradient(45deg, rgba(255,255,255,0.08) 0 1px, transparent 1px 12px),
                rgba(8, 43, 72, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.17);
        }

        .river {
            position: absolute;
            left: -8%;
            top: 45%;
            width: 120%;
            height: 72px;
            border-radius: 999px;
            transform: rotate(-8deg);
            background: linear-gradient(90deg, rgba(35, 213, 255, 0.1), rgba(35, 213, 255, 0.45), rgba(118, 246, 180, 0.25));
            filter: blur(0.5px);
        }

        .pin {
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #fff;
            background: var(--cyan);
            box-shadow: 0 0 0 10px rgba(35, 213, 255, 0.12);
        }

        .map-note {
            position: absolute;
            right: 12px;
            top: 12px;
            padding: 10px;
            border-radius: 12px;
            background: rgba(8, 27, 46, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.16);
            font-size: .84rem;
            max-width: 220px;
        }

        .logout-btn {
            margin-top: 16px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            padding: 10px 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
        }

        @media (max-width: 1180px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { min-height: auto; position: static; }
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid-main, .grid-bottom { grid-template-columns: 1fr; }
        }

        @media (max-width: 760px) {
            .topbar { flex-wrap: wrap; }
            .search { order: 3; max-width: 100%; width: 100%; }
            .cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
@php
    $totalTagihan = (float) $totalTagihan;
    $totalPembayaran = (float) $totalPembayaran;
    $totalTunggakan = (float) $totalTunggakan;
    $paymentPercent = $totalTagihan > 0 ? round(($totalPembayaran / $totalTagihan) * 100) : 0;
    $months = $chartData ?? [];
@endphp

<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">💧</div>
            <div>
                <h1>{{ $namaUnitPengelola ?: 'Air Bersih Desa' }}</h1>
                <p>{{ $namaKecamatan ? 'Kecamatan '.$namaKecamatan : 'Sistem Pengelolaan Cerdas' }}</p>
            </div>
        </div>

        <div class="menu-title">Navigasi Utama</div>
        <nav class="menu">
            <a class="menu-item" href="#"><span class="icon">🏠</span>Beranda</a>
            <a class="menu-item" href="#statistik"><span class="icon">📊</span>Statistik</a>
            <a class="menu-item" href="#monitoring"><span class="icon">🛰️</span>Monitoring</a>
            <a class="menu-item" href="#peta"><span class="icon">🗺️</span>Peta Pelanggan</a>
            <a class="menu-item" href="#keluhan"><span class="icon">🛠️</span>Gangguan & Keluhan</a>
        </nav>

        <div class="menu-title">Menu Cepat</div>
        <nav class="menu">
            @foreach ($shortcuts as $shortcut)
                <a class="menu-item" href="{{ route($shortcut['route']) }}">
                    <span class="icon">➜</span>{{ $shortcut['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="sidebar-foot">
            <strong>Status Layanan Hari Ini</strong>
            <p>Distribusi air berjalan normal pada mayoritas wilayah desa.</p>
            <p><small>Perbarui data monitoring setiap pergantian shift petugas.</small></p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">Keluar dari Sistem</button>
        </form>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <h2>{{ $dashboardTitle }}</h2>
                <small style="color: var(--text-soft);">{{ $namaUnitPengelola ? 'Identitas pengelola aktif: '.$namaUnitPengelola : 'Panel kontrol layanan air bersih desa yang profesional & terpercaya.' }}</small>
            </div>

            <label class="search">
                🔎
                <input type="text" placeholder="Cari pelanggan, tagihan, atau lokasi...">
            </label>

            <div class="top-actions">
                <div class="chip">🔔</div>
                <div class="profile">
                    <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <div>
                        <strong style="font-size:.87rem;">{{ $user->name }}</strong><br>
                        <small style="color: var(--text-soft);">{{ $role?->name ?? 'Admin' }}</small>
                    </div>
                </div>
            </div>
        </header>

        <section class="hero">
            <h3>Ringkasan Kinerja Pelayanan Air Bersih Desa</h3>
            <p>{{ $dashboardDescription }}</p>
            <div class="hero-stats">
                @if($isKecamatanDashboard)
                    <div><small>Jumlah Desa Aktif</small><strong>{{ number_format($jumlahDesaAktif, 0, ',', '.') }}</strong></div>
                    <div><small>Total Pelanggan Rumah Tangga</small><strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong></div>
                    <div><small>Total Pemakaian Kecamatan</small><strong>{{ number_format($totalPemakaianM3, 0, ',', '.') }} m³</strong></div>
                    <div><small>Total Tunggakan Setoran Desa</small><strong>Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</strong></div>
                @else
                    <div><small>Jumlah Pelanggan Aktif</small><strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong></div>
                    <div><small>Jumlah Gangguan Tercatat</small><strong>{{ number_format($totalGangguan, 0, ',', '.') }}</strong></div>
                    <div><small>Tingkat Pembayaran</small><strong>{{ $paymentPercent }}%</strong></div>
                    <div><small>Total Tunggakan</small><strong>Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</strong></div>
                @endif
            </div>
        </section>

        <section id="statistik" class="cards">
            <article class="stat-card">
                <div class="big-icon">👥</div>
                <h4>{{ $isKecamatanDashboard ? 'Total Pelanggan Rumah Tangga' : 'Total Pelanggan' }}</h4>
                <strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong>
            </article>
            <article class="stat-card">
                <div class="big-icon">🧾</div>
                <h4>{{ $isKecamatanDashboard ? 'Total Tagihan Desa ke Kecamatan' : 'Total Tagihan' }}</h4>
                <strong>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</strong>
            </article>
            <article class="stat-card">
                <div class="big-icon">💳</div>
                <h4>{{ $isKecamatanDashboard ? 'Total Pembayaran Desa ke Kecamatan' : 'Total Pembayaran' }}</h4>
                <strong>Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</strong>
            </article>
            <article class="stat-card">
                <div class="big-icon">📍</div>
                <h4>Gangguan Layanan</h4>
                <strong>{{ number_format($totalGangguan, 0, ',', '.') }} Kasus</strong>
            </article>
        </section>

        <section class="grid-main">
            <article class="panel">
                <h4>{{ $isKecamatanDashboard ? 'Grafik Kepatuhan Setoran per Desa' : 'Grafik Pendapatan • Tagihan • Pembayaran' }}</h4>
                <div class="bar-chart">
                    @foreach ($months as $month)
                        <div class="bar-wrap">
                            <div class="bar" style="height: {{ $month['value'] }}%;"></div>
                            <div style="margin-top: 6px;">{{ $month['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article id="monitoring" class="panel" style="position:relative;">
                <h4>Panel Monitoring Petugas</h4>
                <div class="ring-wrap">
                    <div class="ring" style="--angle: {{ $paymentPercent }}%;"></div>
                    <div class="ring-label">{{ $paymentPercent }}%</div>
                    <div>
                        <strong>Kepatuhan Pembayaran</strong>
                        <p style="margin: 6px 0 0; color: var(--text-soft); font-size: .88rem;">Persentase pembayaran terhadap total tagihan berjalan.</p>
                    </div>
                </div>
                <div class="monitor-list">
                    <div class="item"><span>Shift Pagi - Unit Distribusi Utara</span><span class="badge">Normal</span></div>
                    <div class="item"><span>Shift Siang - Unit Distribusi Tengah</span><span class="badge">Stabil</span></div>
                    <div class="item"><span>Shift Malam - Unit Distribusi Selatan</span><span class="badge warn">Perlu Pengecekan</span></div>
                </div>
            </article>
        </section>


        @if($isKecamatanDashboard)
        <section class="panel" style="overflow:auto;">
            <h4>Ringkasan Kecamatan per Desa (Periode {{ $selectedPeriod }})</h4>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Desa</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Jml Pelanggan</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Pemakaian</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Tagihan RT</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Pembayaran RT</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Tagihan Kec.</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Pembayaran Kec.</th>
                        <th style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.2);padding:8px;">Status Setoran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($villageSummaries as $row)
                        <tr>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">{{ $row['desa'] }}</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">{{ number_format($row['jumlah_pelanggan'], 0, ',', '.') }}</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">{{ number_format($row['total_pemakaian_m3'], 0, ',', '.') }} m³</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">Rp {{ number_format($row['total_tagihan_rumah_tangga'], 0, ',', '.') }}</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">Rp {{ number_format($row['total_pembayaran_rumah_tangga'], 0, ',', '.') }}</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">Rp {{ number_format($row['total_tagihan_kecamatan'], 0, ',', '.') }}</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">Rp {{ number_format($row['total_pembayaran_kecamatan'], 0, ',', '.') }}</td>
                            <td style="padding:8px;border-bottom:1px solid rgba(255,255,255,0.12);">{{ str($row['status_setoran'])->replace('_', ' ')->title() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="padding:8px;">Belum ada data ringkasan desa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        @endif

        <section class="grid-bottom">
            <article id="peta" class="panel">
                <h4>Peta Lokasi Pelanggan & Titik Distribusi</h4>
                <div class="map">
                    <div class="river"></div>
                    <span class="pin" style="top: 22%; left: 28%;"></span>
                    <span class="pin" style="top: 41%; left: 57%;"></span>
                    <span class="pin" style="top: 64%; left: 38%;"></span>
                    <span class="pin" style="top: 70%; left: 71%;"></span>
                    <div class="map-note">
                        <strong>Zona Layanan Dominan</strong>
                        <div style="color:var(--text-soft); margin-top: 4px;">Titik pelanggan aktif terpusat di area tengah dan selatan desa.</div>
                    </div>
                </div>
            </article>

            <article id="keluhan" class="panel">
                <h4>Notifikasi & Aktivitas Keluhan Terbaru</h4>
                <div class="complaint-list">
                    @forelse(($recentKeluhan ?? []) as $keluhan)
                        <div class="item">
                            <div>
                                <strong style="font-size:.88rem;">{{ $keluhan->judul }}</strong>
                                <div style="font-size:.78rem; color:var(--text-soft);">Dilaporkan {{ optional($keluhan->reported_at)->diffForHumans() ?? '-' }}</div>
                            </div>
                            <span class="badge {{ $keluhan->status_penanganan === 'selesai' ? '' : 'warn' }}">{{ ucfirst($keluhan->status_penanganan) }}</span>
                        </div>
                    @empty
                        <div class="item"><div style="font-size:.85rem;color:var(--text-soft);">Belum ada aktivitas keluhan terbaru.</div></div>
                    @endforelse
                </div>
            </article>
        </section>
    </main>
</div>
</body>
</html>
