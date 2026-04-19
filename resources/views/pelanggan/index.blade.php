<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body { font-family: Arial, sans-serif; background:#f8fafc; padding:24px; }
        .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
        .button { padding:10px 14px; border-radius:8px; text-decoration:none; border:none; cursor:pointer; background:#0f172a; color:#fff; }
        .button-blue { background:#2563eb; }
        .button-red { background:#ef4444; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:16px; }
        .filters { display:grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:10px; }
        table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #e2e8f0; }
        th, td { padding:12px; border-bottom:1px solid #e2e8f0; text-align:left; }
        th { background:#f1f5f9; }
        #map { height: 360px; border-radius: 12px; border: 1px solid #cbd5e1; }
        .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; }
        .aktif { background:#dcfce7; color:#166534; }
        .nonaktif { background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>
    <div class="top">
        <h1>Data Pelanggan</h1>
        <a href="{{ route('pelanggan.create') }}" class="button">Tambah Pelanggan</a>
    </div>

    @if(session('status'))
        <div class="card" style="background:#dcfce7;color:#166534;border-color:#bbf7d0;">{{ session('status') }}</div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('pelanggan.index') }}" class="filters">
            <input type="text" name="q" placeholder="Cari kode, nama, meter, no hp, alamat, dusun" value="{{ $filters['q'] ?? '' }}">

            <select name="status">
                <option value="">Semua status</option>
                <option value="aktif" {{ ($filters['status'] ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ ($filters['status'] ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <select name="desa_id">
                <option value="">Semua desa</option>
                @foreach($desas as $desa)
                    <option value="{{ $desa->id }}" {{ (string)($filters['desa_id'] ?? '') === (string)$desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                @endforeach
            </select>

            <select name="assigned_petugas_id">
                <option value="">Semua petugas</option>
                @foreach($petugas as $user)
                    <option value="{{ $user->id }}" {{ (string)($filters['assigned_petugas_id'] ?? '') === (string)$user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>

            <div style="display:flex;gap:8px;">
                <button class="button" type="submit">Filter</button>
                <a href="{{ route('pelanggan.index') }}" class="button button-blue">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Peta Titik Pelanggan</h3>
        <div id="map"></div>
        <p style="color:#64748b;">Menampilkan titik pelanggan dari data yang sedang terfilter.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Wilayah</th>
                <th>Jenis</th>
                <th>Nomor Meter</th>
                <th>Petugas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggans as $pelanggan)
                <tr>
                    <td>{{ $pelanggan->kode_pelanggan ?? '-' }}</td>
                    <td>{{ $pelanggan->name }}</td>
                    <td>{{ $pelanggan->desa?->name ?? '-' }} / {{ $pelanggan->kecamatan?->name ?? '-' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($pelanggan->jenis_pelanggan ?? '-')) }}</td>
                    <td>{{ $pelanggan->nomor_meter ?? '-' }}</td>
                    <td>{{ $pelanggan->assignedPetugas?->name ?? '-' }}</td>
                    <td><span class="badge {{ $pelanggan->status }}">{{ ucfirst($pelanggan->status) }}</span></td>
                    <td style="display:flex;gap:8px;flex-wrap:wrap;">
                        <a href="{{ route('pelanggan.show', $pelanggan) }}" class="button button-blue">Detail</a>
                        <a href="{{ route('pelanggan.edit', $pelanggan) }}" class="button button-blue">Edit</a>
                        <form action="{{ route('pelanggan.destroy', $pelanggan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus pelanggan ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="button button-red" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Data pelanggan belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $pelanggans->links() }}</div>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const map = L.map('map').setView([-2.548926, 118.014863], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const points = @json($mapPoints);
        const bounds = [];

        points.forEach((point) => {
            const marker = L.marker([point.lat, point.lng]).addTo(map);
            marker.bindPopup(`<strong>${point.name}</strong><br>Kode: ${point.kode}<br><a href="${point.show_url}">Lihat detail</a>`);
            bounds.push([point.lat, point.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
    </script>
</body>
</html>
