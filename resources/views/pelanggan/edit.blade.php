<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pelanggan</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; padding: 24px; }
        form { max-width: 920px; background: #fff; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display:block; margin-bottom: 8px; font-weight: 600; }
        select,input,textarea { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:10px; }
        textarea { min-height: 90px; }
        #map { height: 340px; border-radius: 12px; border:1px solid #cbd5e1; margin: 10px 0 18px; }
        button { padding:12px 18px; border-radius:10px; background:#0f172a; color:#fff; border:none; cursor:pointer; }
        a { color:#2563eb; }
        .full { grid-column: 1 / -1; }
    </style>
</head>
<body>
    <h1>Edit Pelanggan</h1>

    @if($errors->any())
        <div style="margin-bottom:16px;padding:14px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;max-width:920px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pelanggan.update', $pelanggan) }}">
        @csrf
        @method('PUT')

        <div class="grid">
            <div>
                <label>Kode Pelanggan</label>
                <input type="text" name="kode_pelanggan" value="{{ old('kode_pelanggan', $pelanggan->kode_pelanggan) }}" required>
            </div>
            <div>
                <label>Nama Pelanggan</label>
                <input type="text" name="name" value="{{ old('name', $pelanggan->name) }}" required>
            </div>
            <div>
                <label>No HP</label>
                <input type="text" name="phone" value="{{ old('phone', $pelanggan->phone) }}">
            </div>
            <div>
                <label>Email (opsional)</label>
                <input type="email" name="email" value="{{ old('email', $pelanggan->email) }}">
            </div>
            <div class="full">
                <label>Alamat</label>
                <textarea name="address" required>{{ old('address', $pelanggan->address) }}</textarea>
            </div>
            <div>
                <label>Dusun</label>
                <input type="text" name="dusun" value="{{ old('dusun', $pelanggan->dusun) }}" required>
            </div>
            <div>
                <label>Jenis Pelanggan</label>
                <select name="jenis_pelanggan" required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach(['rumah_tangga' => 'Rumah Tangga', 'niaga' => 'Niaga', 'sosial' => 'Sosial', 'instansi' => 'Instansi'] as $value => $label)
                        <option value="{{ $value }}" {{ old('jenis_pelanggan', $pelanggan->jenis_pelanggan) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Nomor Meter</label>
                <input type="text" name="nomor_meter" value="{{ old('nomor_meter', $pelanggan->nomor_meter) }}" required>
            </div>
            <div>
                <label>Status</label>
                <select name="status" required>
                    <option value="aktif" {{ old('status', $pelanggan->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status', $pelanggan->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <label>Kecamatan</label>
                <select name="kecamatan_id">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id', $pelanggan->kecamatan_id) == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Desa</label>
                <select name="desa_id" required>
                    <option value="">-- Pilih Desa --</option>
                    @foreach($desas as $desa)
                        <option value="{{ $desa->id }}" {{ old('desa_id', $pelanggan->desa_id) == $desa->id ? 'selected' : '' }}>{{ $desa->name }} ({{ $desa->kecamatan?->name ?? 'Kecamatan' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Assign ke Petugas</label>
                <select name="assigned_petugas_id">
                    <option value="">-- Pilih Petugas --</option>
                    @foreach($petugas as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_petugas_id', $pelanggan->assigned_petugas_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Latitude</label>
                <input id="latitude" type="text" name="latitude" value="{{ old('latitude', $pelanggan->latitude) }}">
            </div>
            <div>
                <label>Longitude</label>
                <input id="longitude" type="text" name="longitude" value="{{ old('longitude', $pelanggan->longitude) }}">
            </div>
            <div class="full">
                <label>Ubah Titik Pelanggan di Peta (klik pada peta)</label>
                <div id="map"></div>
            </div>
        </div>

        <button type="submit">Perbarui</button>
    </form>

    <p><a href="{{ route('pelanggan.index') }}">Kembali ke Daftar Pelanggan</a></p>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const initialLat = Number(@json(old('latitude', $pelanggan->latitude ?: -2.548926)));
        const initialLng = Number(@json(old('longitude', $pelanggan->longitude ?: 118.014863)));

        const map = L.map('map').setView([initialLat, initialLng], (initialLat === -2.548926 && initialLng === 118.014863) ? 5 : 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = null;
        if (!Number.isNaN(initialLat) && !Number.isNaN(initialLng)) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }

        map.on('click', (event) => {
            const { lat, lng } = event.latlng;

            document.getElementById('latitude').value = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
        });
    </script>
</body>
</html>
