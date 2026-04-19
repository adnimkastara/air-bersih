<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body { font-family: Arial, sans-serif; background:#f8fafc; padding:24px; }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; max-width:980px; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px 20px; margin-bottom:16px; }
        .label { color:#64748b; font-size:13px; }
        .value { font-weight:600; }
        #map { height: 360px; border-radius: 12px; border: 1px solid #cbd5e1; margin-top: 12px; }
        a { color:#2563eb; }
    </style>
</head>
<body>
    <h1>Detail Pelanggan</h1>

    <div class="card">
        <div class="grid">
            <div><div class="label">Kode Pelanggan</div><div class="value">{{ $pelanggan->kode_pelanggan }}</div></div>
            <div><div class="label">Nama Pelanggan</div><div class="value">{{ $pelanggan->name }}</div></div>
            <div><div class="label">Nomor Meter</div><div class="value">{{ $pelanggan->nomor_meter ?? '-' }}</div></div>
            <div><div class="label">Jenis Pelanggan</div><div class="value">{{ str_replace('_', ' ', ucfirst($pelanggan->jenis_pelanggan ?? '-')) }}</div></div>
            <div><div class="label">No HP</div><div class="value">{{ $pelanggan->phone ?? '-' }}</div></div>
            <div><div class="label">Status</div><div class="value">{{ ucfirst($pelanggan->status) }}</div></div>
            <div><div class="label">Desa / Kecamatan</div><div class="value">{{ $pelanggan->desa?->name ?? '-' }} / {{ $pelanggan->kecamatan?->name ?? '-' }}</div></div>
            <div><div class="label">Dusun</div><div class="value">{{ $pelanggan->dusun ?? '-' }}</div></div>
            <div><div class="label">Petugas</div><div class="value">{{ $pelanggan->assignedPetugas?->name ?? '-' }}</div></div>
            <div><div class="label">Koordinat</div><div class="value">{{ $pelanggan->latitude ?? '-' }}, {{ $pelanggan->longitude ?? '-' }}</div></div>
            <div style="grid-column:1 / -1;"><div class="label">Alamat</div><div class="value">{{ $pelanggan->address }}</div></div>
        </div>

        <h3>Titik Pelanggan di Peta</h3>
        <div id="map"></div>

        <p style="margin-top:18px;">
            <a href="{{ route('pelanggan.edit', $pelanggan) }}">Edit pelanggan</a> |
            <a href="{{ route('pelanggan.index') }}">Kembali ke daftar pelanggan</a>
        </p>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const lat = Number(@json($pelanggan->latitude ?: -2.548926));
        const lng = Number(@json($pelanggan->longitude ?: 118.014863));

        const map = L.map('map').setView([lat, lng], (lat === -2.548926 && lng === 118.014863) ? 5 : 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            L.marker([lat, lng]).addTo(map)
                .bindPopup(`<strong>{{ addslashes($pelanggan->name) }}</strong><br>{{ addslashes($pelanggan->address) }}`)
                .openPopup();
        }
    </script>
</body>
</html>
