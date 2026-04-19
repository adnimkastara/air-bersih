<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Peta Pelanggan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body { font-family: Arial, sans-serif; background:#f8fafc; margin:0; padding:24px; color:#0f172a; }
        h1, h2, h3 { margin-top:0; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
        .button { padding:10px 14px; border-radius:8px; text-decoration:none; border:none; cursor:pointer; background:#0f172a; color:#fff; }
        .button-blue { background:#2563eb; }
        .button-green { background:#059669; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:16px; }
        .grid-2 { display:grid; grid-template-columns: 2fr 1fr; gap:16px; }
        .filters { display:grid; grid-template-columns: repeat(3, minmax(180px, 1fr)) auto; gap:10px; }
        #map { height: 460px; border-radius: 12px; border: 1px solid #cbd5e1; }
        .legend { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
        .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; }
        .aktif { background:#dcfce7; color:#166534; }
        .menunggak { background:#fef3c7; color:#92400e; }
        .gangguan { background:#fee2e2; color:#991b1b; }
        .table-wrap { overflow:auto; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:top; }
        th { background:#f1f5f9; }
        input, select, textarea { width:100%; box-sizing:border-box; padding:10px; border:1px solid #cbd5e1; border-radius:8px; }
        label { display:block; margin-bottom:4px; font-weight:600; color:#334155; }
        .form-grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px; }
        .help { color:#64748b; font-size:13px; margin-top:4px; }
        @media (max-width: 960px) {
            .grid-2 { grid-template-columns: 1fr; }
            .filters { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="top">
        <h1>Tahap 9 · Monitoring Berbasis Peta</h1>
        <a href="{{ route('dashboard') }}" class="button button-blue">Kembali ke Dashboard</a>
    </div>

    @if(session('status'))
        <div class="card" style="background:#dcfce7;color:#166534;border-color:#bbf7d0;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="card" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
            <strong>Validasi gagal:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('monitoring.index') }}" class="filters">
            <select name="status">
                <option value="">Filter status pelanggan</option>
                <option value="aktif" {{ ($filters['status'] ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="menunggak" {{ ($filters['status'] ?? '') === 'menunggak' ? 'selected' : '' }}>Menunggak</option>
                <option value="gangguan" {{ ($filters['status'] ?? '') === 'gangguan' ? 'selected' : '' }}>Gangguan</option>
            </select>

            <select name="jenis_laporan">
                <option value="">Semua jenis laporan</option>
                <option value="gangguan" {{ ($filters['jenis_laporan'] ?? '') === 'gangguan' ? 'selected' : '' }}>Gangguan</option>
                <option value="keluhan" {{ ($filters['jenis_laporan'] ?? '') === 'keluhan' ? 'selected' : '' }}>Keluhan</option>
            </select>

            <select name="status_penanganan">
                <option value="">Semua status penanganan</option>
                <option value="baru" {{ ($filters['status_penanganan'] ?? '') === 'baru' ? 'selected' : '' }}>Baru</option>
                <option value="diproses" {{ ($filters['status_penanganan'] ?? '') === 'diproses' ? 'selected' : '' }}>Diproses</option>
                <option value="selesai" {{ ($filters['status_penanganan'] ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>

            <div style="display:flex;gap:8px;">
                <button class="button" type="submit">Filter</button>
                <a href="{{ route('monitoring.index') }}" class="button button-blue">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid-2">
        <div class="card">
            <h3>Peta Monitoring Pelanggan</h3>
            <div id="map"></div>
            <div class="legend">
                <span class="badge aktif">Marker Hijau · Aktif</span>
                <span class="badge menunggak">Marker Oranye · Menunggak</span>
                <span class="badge gangguan">Marker Merah · Gangguan</span>
            </div>
            <p class="help">Marker dibuat berdasarkan status prioritas: <strong>gangguan</strong> → <strong>menunggak</strong> → <strong>aktif</strong>.</p>
        </div>

        <div class="card">
            <h3>Form Input Laporan Gangguan/Keluhan</h3>
            <form method="POST" action="{{ route('monitoring.store') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:10px;">
                    <label>Pelanggan</label>
                    <select name="pelanggan_id" required>
                        <option value="">Pilih pelanggan</option>
                        @foreach($pelangganOptions as $pelanggan)
                            <option value="{{ $pelanggan->id }}" {{ (string) old('pelanggan_id') === (string) $pelanggan->id ? 'selected' : '' }}>
                                {{ $pelanggan->kode_pelanggan }} · {{ $pelanggan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Jenis Laporan</label>
                        <select name="jenis_laporan" required>
                            <option value="gangguan" {{ old('jenis_laporan', 'gangguan') === 'gangguan' ? 'selected' : '' }}>Gangguan</option>
                            <option value="keluhan" {{ old('jenis_laporan') === 'keluhan' ? 'selected' : '' }}>Keluhan</option>
                        </select>
                    </div>
                    <div>
                        <label>Status Penanganan</label>
                        <select name="status_penanganan" required>
                            <option value="baru" {{ old('status_penanganan', 'baru') === 'baru' ? 'selected' : '' }}>Baru</option>
                            <option value="diproses" {{ old('status_penanganan') === 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="selesai" {{ old('status_penanganan') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label>Judul</label>
                    <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Pipa bocor di dusun selatan" required>
                </div>

                <div style="margin-top:10px;">
                    <label>Deskripsi Keluhan/Gangguan</label>
                    <textarea name="deskripsi" rows="4" required>{{ old('deskripsi') }}</textarea>
                </div>

                <div style="margin-top:10px;">
                    <label>Upload Foto Gangguan (opsional)</label>
                    <input type="file" name="foto_gangguan" accept="image/*">
                    <div class="help">Format: JPG/JPEG/PNG/WEBP · Maksimum 3MB.</div>
                </div>

                <button type="submit" class="button button-green" style="margin-top:12px;">Simpan Laporan</button>
            </form>
        </div>
    </div>

    <div class="card table-wrap">
        <h3 style="margin-bottom:10px;">Daftar Laporan Gangguan & Keluhan</h3>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pelanggan</th>
                    <th>Jenis</th>
                    <th>Judul / Deskripsi</th>
                    <th>Status</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporans as $laporan)
                    <tr>
                        <td>{{ optional($laporan->reported_at)->format('d M Y H:i') ?? '-' }}</td>
                        <td>
                            {{ $laporan->pelanggan?->kode_pelanggan }}<br>
                            <strong>{{ $laporan->pelanggan?->name }}</strong>
                        </td>
                        <td><span class="badge {{ $laporan->jenis_laporan === 'gangguan' ? 'gangguan' : 'menunggak' }}">{{ ucfirst($laporan->jenis_laporan) }}</span></td>
                        <td>
                            <strong>{{ $laporan->judul }}</strong>
                            <div class="help">{{ $laporan->deskripsi }}</div>
                        </td>
                        <td><span class="badge {{ $laporan->status_penanganan === 'selesai' ? 'aktif' : ($laporan->status_penanganan === 'diproses' ? 'menunggak' : 'gangguan') }}">{{ ucfirst($laporan->status_penanganan) }}</span></td>
                        <td>
                            @if($laporan->foto_path)
                                <a href="{{ asset('storage/' . $laporan->foto_path) }}" target="_blank" class="button button-blue" style="padding:6px 10px;font-size:12px;">Lihat Foto</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">Belum ada laporan gangguan/keluhan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:10px;">{{ $laporans->links() }}</div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const map = L.map('map').setView([-2.548926, 118.014863], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const statusColor = {
            aktif: '#16a34a',
            menunggak: '#d97706',
            gangguan: '#dc2626'
        };

        const points = @json($mapPoints);
        const bounds = [];

        points.forEach((point) => {
            const marker = L.circleMarker([point.lat, point.lng], {
                radius: 8,
                color: statusColor[point.status] || '#334155',
                fillColor: statusColor[point.status] || '#334155',
                fillOpacity: 0.85,
                weight: 2
            }).addTo(map);

            marker.bindPopup(`<strong>${point.name}</strong><br>Kode: ${point.kode}<br>Status: ${point.status_label}<br><a href="${point.show_url}">Detail pelanggan</a>`);
            bounds.push([point.lat, point.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
    </script>
</body>
</html>
