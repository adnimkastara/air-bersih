@extends('layouts.admin')

@section('title', $dashboardTitle)

@section('head')
<style>
    .dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(260px, .55fr);
        gap: 18px;
        align-items: stretch;
    }

    .hero-panel {
        padding: 28px;
        border-radius: 16px;
        background:
            linear-gradient(135deg, rgba(0, 97, 255, .08), rgba(14, 165, 233, .04)),
            #fff;
        box-shadow: var(--shadow-soft);
        border: 1px solid rgba(226, 232, 240, .9);
    }

    .hero-panel h2 {
        margin: 0;
        font-size: 1.8rem;
        line-height: 1.2;
        letter-spacing: 0;
    }

    .hero-panel p {
        margin: 12px 0 0;
        color: var(--muted);
        max-width: 760px;
    }

    .quick-actions {
        display: grid;
        gap: 10px;
    }

    .quick-actions a {
        justify-content: flex-start;
        width: 100%;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: 18px 0;
    }

    .stat-card {
        display: grid;
        gap: 14px;
        padding: 22px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, .86);
        box-shadow: var(--shadow-soft);
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 1.15rem;
    }

    .stat-icon.blue { background: #eaf2ff; color: #0061ff; }
    .stat-icon.green { background: #dcfce7; color: #15803d; }
    .stat-icon.amber { background: #fef3c7; color: #b45309; }
    .stat-icon.rose { background: #ffe4e6; color: #be123c; }

    .stat-card small {
        color: var(--muted);
        font-weight: 700;
    }

    .stat-card strong {
        font-size: 1.65rem;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: 0;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(320px, .75fr);
        gap: 18px;
    }

    .panel-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .panel-title h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
    }

    .bar-chart {
        display: grid;
        grid-template-columns: repeat(6, minmax(56px, 1fr));
        gap: 12px;
        align-items: end;
        min-height: 260px;
        padding: 18px;
        border-radius: 16px;
        background: linear-gradient(180deg, #f8fafc, #fff);
        border: 1px solid #eef2f7;
    }

    .bar-wrap {
        display: grid;
        align-items: end;
        gap: 8px;
        height: 220px;
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        text-align: center;
    }

    .bar {
        min-height: 12px;
        border-radius: 12px 12px 6px 6px;
        background: linear-gradient(180deg, #0061ff, rgba(14, 165, 233, .42));
        box-shadow: 0 12px 24px rgba(0, 97, 255, .16);
    }

    .progress-ring {
        width: 132px;
        height: 132px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: conic-gradient(#0061ff var(--angle), #e2e8f0 0);
    }

    .progress-ring::before {
        content: attr(data-value);
        width: 94px;
        height: 94px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: #fff;
        color: var(--text);
        font-size: 1.25rem;
        font-weight: 800;
        box-shadow: inset 0 0 0 1px #edf2f7;
    }

    .activity-list {
        display: grid;
        gap: 10px;
    }

    .activity-item {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
    }

    .map-preview {
        min-height: 290px;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        background:
            linear-gradient(135deg, rgba(0, 97, 255, .1), rgba(20, 184, 166, .08)),
            repeating-linear-gradient(45deg, rgba(15, 23, 42, .05) 0 1px, transparent 1px 14px),
            #f8fafc;
        border: 1px solid #eef2f7;
    }

    .map-river {
        position: absolute;
        left: -8%;
        top: 48%;
        width: 120%;
        height: 70px;
        border-radius: 999px;
        transform: rotate(-8deg);
        background: linear-gradient(90deg, rgba(0,97,255,.12), rgba(14,165,233,.32), rgba(20,184,166,.18));
    }

    .map-pin {
        position: absolute;
        width: 14px;
        height: 14px;
        border-radius: 999px;
        border: 3px solid #fff;
        background: #0061ff;
        box-shadow: 0 0 0 8px rgba(0, 97, 255, .12);
    }

    .dashboard-bottom {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
        gap: 18px;
        margin-top: 18px;
    }

    @media (max-width: 1180px) {
        .dashboard-hero,
        .dashboard-grid,
        .dashboard-bottom {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .bar-chart {
            overflow-x: auto;
        }
    }
</style>
@endsection

@section('content')
@php
    $user = auth()->user();
    $totalTagihan = (float) $totalTagihan;
    $totalPembayaran = (float) $totalPembayaran;
    $totalTunggakan = (float) $totalTunggakan;
    $paymentPercent = $totalTagihan > 0 ? min(100, round(($totalPembayaran / $totalTagihan) * 100)) : 0;
    $months = $chartData ?? [];
@endphp

<div class="dashboard-hero">
    <section class="hero-panel">
        <div class="badge badge-success" style="margin-bottom:14px;">Status layanan aktif</div>
        <h2>{{ $dashboardTitle }}</h2>
        <p>{{ $dashboardDescription }}</p>
        <div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;">
            <span class="badge badge-success">{{ $namaUnitPengelola ?: 'Air Bersih Desa' }}</span>
            @if($namaKecamatan)
                <span class="badge" style="background:#eaf2ff;color:#0056e0;">Kecamatan {{ $namaKecamatan }}</span>
            @endif
        </div>
    </section>

    <aside class="card" style="margin:0;">
        <div class="panel-title">
            <h3>Menu Cepat</h3>
            <span class="badge" style="background:#f1f5f9;color:#475569;">{{ $user?->role?->name ?? 'Admin' }}</span>
        </div>
        <div class="quick-actions">
            @foreach ($shortcuts as $shortcut)
                <a class="btn btn-outline" href="{{ route($shortcut['route']) }}">
                    <i class="bi bi-arrow-right-circle"></i>{{ $shortcut['label'] }}
                </a>
            @endforeach
        </div>
    </aside>
</div>

<section class="stats-grid" id="statistik">
    <article class="stat-card">
        <span class="stat-icon blue"><i class="bi bi-people"></i></span>
        <small>{{ $isKecamatanDashboard ? 'Total Pelanggan Rumah Tangga' : 'Total Pelanggan' }}</small>
        <strong>{{ number_format($totalPelanggan, 0, ',', '.') }}</strong>
    </article>
    <article class="stat-card">
        <span class="stat-icon amber"><i class="bi bi-receipt"></i></span>
        <small>{{ $isKecamatanDashboard ? 'Total Tagihan Desa ke Kecamatan' : 'Total Tagihan' }}</small>
        <strong>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</strong>
    </article>
    <article class="stat-card">
        <span class="stat-icon green"><i class="bi bi-credit-card"></i></span>
        <small>{{ $isKecamatanDashboard ? 'Total Pembayaran Desa ke Kecamatan' : 'Total Pembayaran' }}</small>
        <strong>Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</strong>
    </article>
    <article class="stat-card">
        <span class="stat-icon rose"><i class="bi bi-exclamation-triangle"></i></span>
        <small>{{ $isKecamatanDashboard ? 'Total Tunggakan Setoran' : 'Gangguan Layanan' }}</small>
        <strong>{{ $isKecamatanDashboard ? 'Rp '.number_format($totalTunggakan, 0, ',', '.') : number_format($totalGangguan, 0, ',', '.').' Kasus' }}</strong>
    </article>
</section>

<section class="dashboard-grid">
    <article class="card" style="margin:0;">
        <div class="panel-title">
            <h3>{{ $isKecamatanDashboard ? 'Grafik Kepatuhan Setoran per Desa' : 'Grafik Pendapatan, Tagihan, Pembayaran' }}</h3>
            <span class="badge" style="background:#f1f5f9;color:#475569;">Periode berjalan</span>
        </div>
        <div class="bar-chart">
            @foreach ($months as $month)
                <div class="bar-wrap">
                    <div class="bar" style="height: {{ max(8, (int) $month['value']) }}%;"></div>
                    <div>{{ $month['label'] }}</div>
                </div>
            @endforeach
        </div>
    </article>

    <article class="card" id="monitoring" style="margin:0;">
        <div class="panel-title">
            <h3>Kepatuhan Pembayaran</h3>
        </div>
        <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;margin-bottom:18px;">
            <div class="progress-ring" style="--angle: {{ $paymentPercent }}%;" data-value="{{ $paymentPercent }}%"></div>
            <div style="max-width:260px;">
                <strong style="font-size:1rem;">Pembayaran terhadap total tagihan</strong>
                <p class="muted" style="margin:8px 0 0;">Pantau performa pembayaran dan tindak lanjuti wilayah dengan tunggakan tinggi.</p>
            </div>
        </div>
        <div class="activity-list">
            <div class="activity-item"><span>Shift Pagi - Unit Distribusi Utara</span><span class="badge badge-success">Normal</span></div>
            <div class="activity-item"><span>Shift Siang - Unit Distribusi Tengah</span><span class="badge badge-success">Stabil</span></div>
            <div class="activity-item"><span>Shift Malam - Unit Distribusi Selatan</span><span class="badge badge-warning">Perlu Cek</span></div>
        </div>
    </article>
</section>

@if($isKecamatanDashboard)
    <section class="card table-wrap" style="margin-top:18px;">
        <div class="panel-title" style="padding:0 26px;">
            <h3>Ringkasan Kecamatan per Desa (Periode {{ $selectedPeriod }})</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Desa</th>
                    <th>Jml Pelanggan</th>
                    <th>Pemakaian</th>
                    <th>Tagihan RT</th>
                    <th>Pembayaran RT</th>
                    <th>Tagihan Kec.</th>
                    <th>Pembayaran Kec.</th>
                    <th>Status Setoran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($villageSummaries as $row)
                    <tr>
                        <td>{{ $row['desa'] }}</td>
                        <td>{{ number_format($row['jumlah_pelanggan'], 0, ',', '.') }}</td>
                        <td>{{ number_format($row['total_pemakaian_m3'], 0, ',', '.') }} m3</td>
                        <td>Rp {{ number_format($row['total_tagihan_rumah_tangga'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['total_pembayaran_rumah_tangga'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['total_tagihan_kecamatan'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['total_pembayaran_kecamatan'], 0, ',', '.') }}</td>
                        <td>{{ str($row['status_setoran'])->replace('_', ' ')->title() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">@include('layouts.partials.empty-state', ['message' => 'Belum ada data ringkasan desa.'])</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endif

<section class="dashboard-bottom">
    <article class="card" id="peta" style="margin:0;">
        <div class="panel-title">
            <h3>Peta Lokasi Pelanggan & Titik Distribusi</h3>
        </div>
        <div class="map-preview">
            <div class="map-river"></div>
            <span class="map-pin" style="top:22%;left:28%;"></span>
            <span class="map-pin" style="top:41%;left:57%;"></span>
            <span class="map-pin" style="top:64%;left:38%;"></span>
            <span class="map-pin" style="top:70%;left:71%;"></span>
        </div>
    </article>

    <article class="card" id="keluhan" style="margin:0;">
        <div class="panel-title">
            <h3>Notifikasi & Aktivitas Keluhan</h3>
        </div>

        @if(($user?->role?->name ?? null) === 'petugas_lapangan')
            <div class="activity-list" style="margin-bottom:16px;">
                @forelse(($latestNotifications ?? []) as $notification)
                    <div class="activity-item">
                        <div>
                            <strong>{{ data_get($notification->data, 'judul', 'Keluhan Baru') }}</strong>
                            <div class="muted" style="font-size:.82rem;margin-top:4px;">{{ data_get($notification->data, 'message', '-') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="activity-item"><span class="muted">Belum ada notifikasi baru.</span></div>
                @endforelse
            </div>
        @endif

        <div class="activity-list">
            @forelse(($recentKeluhan ?? []) as $keluhan)
                <div class="activity-item">
                    <div>
                        <strong>{{ $keluhan->judul }}</strong>
                        <div class="muted" style="font-size:.82rem;margin-top:4px;">Dilaporkan {{ optional($keluhan->reported_at)->diffForHumans() ?? '-' }}</div>
                    </div>
                    <span class="badge {{ $keluhan->status_penanganan === 'selesai' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($keluhan->status_penanganan) }}</span>
                </div>
            @empty
                <div class="activity-item"><span class="muted">Belum ada aktivitas keluhan terbaru.</span></div>
            @endforelse
        </div>
    </article>
</section>
@endsection
