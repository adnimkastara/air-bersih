@extends('layouts.admin')
@section('title', 'Monitoring')
@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endsection
@section('content')
@include('layouts.partials.page-header', ['title' => 'Monitoring Berbasis Peta', 'subtitle' => 'Pantau titik pelanggan dan input laporan gangguan.'])
@include('layouts.partials.alerts')
<div class="card"><form method="GET" action="{{ route('monitoring.index') }}" class="toolbar">
<div><label>Status Pelanggan</label><select name="status"><option value="">Semua</option><option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option><option value="menunggak" @selected(($filters['status'] ?? '') === 'menunggak')>Menunggak</option><option value="gangguan" @selected(($filters['status'] ?? '') === 'gangguan')>Gangguan</option></select></div>
<div><label>Jenis Laporan</label><select name="jenis_laporan"><option value="">Semua</option><option value="gangguan" @selected(($filters['jenis_laporan'] ?? '') === 'gangguan')>Gangguan</option><option value="keluhan" @selected(($filters['jenis_laporan'] ?? '') === 'keluhan')>Keluhan</option></select></div>
<div><label>Status Penanganan</label><select name="status_penanganan"><option value="">Semua</option><option value="baru" @selected(($filters['status_penanganan'] ?? '') === 'baru')>Baru</option><option value="diproses" @selected(($filters['status_penanganan'] ?? '') === 'diproses')>Diproses</option><option value="selesai" @selected(($filters['status_penanganan'] ?? '') === 'selesai')>Selesai</option></select></div>
<div style="display:flex;gap:8px;"><button class="btn btn-primary" type="submit">Filter</button><a href="{{ route('monitoring.index') }}" class="btn btn-outline">Reset</a></div>
</form></div>
<div class="grid-2">
<div class="card"><h3 style="margin-top:0;">Peta Monitoring</h3><div id="map" style="height:460px;border:1px solid var(--line);border-radius:12px;"></div><div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;"><span class="badge badge-success">Aktif</span><span class="badge badge-warning">Menunggak</span><span class="badge badge-danger">Gangguan</span></div></div>
<div class="card"><h3 style="margin-top:0;">Input Laporan</h3><form method="POST" action="{{ route('monitoring.store') }}" enctype="multipart/form-data">@csrf
<div style="margin-bottom:10px;"><label>Pelanggan</label><select name="pelanggan_id" required><option value="">Pilih pelanggan</option>@foreach($pelangganOptions as $pelanggan)<option value="{{ $pelanggan->id }}" @selected((string) old('pelanggan_id') === (string) $pelanggan->id)>{{ $pelanggan->kode_pelanggan }} · {{ $pelanggan->name }}</option>@endforeach</select></div>
<div class="grid-2"><div><label>Jenis Laporan</label><select name="jenis_laporan" required><option value="gangguan" @selected(old('jenis_laporan', 'gangguan') === 'gangguan')>Gangguan</option><option value="keluhan" @selected(old('jenis_laporan') === 'keluhan')>Keluhan</option></select></div><div><label>Status Penanganan</label><select name="status_penanganan" required><option value="baru" @selected(old('status_penanganan', 'baru') === 'baru')>Baru</option><option value="diproses" @selected(old('status_penanganan') === 'diproses')>Diproses</option><option value="selesai" @selected(old('status_penanganan') === 'selesai')>Selesai</option></select></div></div>
<div style="margin-top:10px;"><label>Judul</label><input type="text" name="judul" value="{{ old('judul') }}" required></div>
<div style="margin-top:10px;"><label>Deskripsi</label><textarea name="deskripsi" rows="4" required>{{ old('deskripsi') }}</textarea></div>
<div style="margin-top:10px;"><label>Upload Foto (opsional)</label><input type="file" name="foto_gangguan" accept="image/*"><div class="muted">JPG/JPEG/PNG/WEBP · max 3MB.</div></div>
<button type="submit" class="btn btn-success" style="margin-top:12px;">Simpan Laporan</button>
</form></div>
</div>
<div class="card table-wrap"><h3 style="margin-top:0;">Daftar Laporan</h3><table><thead><tr><th>Waktu</th><th>Pelanggan</th><th>Jenis</th><th>Judul / Deskripsi</th><th>Status</th><th>Foto</th></tr></thead><tbody>
@forelse($laporans as $laporan)
<tr><td>{{ optional($laporan->reported_at)->format('d M Y H:i') ?? '-' }}</td><td>{{ $laporan->pelanggan?->kode_pelanggan }}<br><strong>{{ $laporan->pelanggan?->name }}</strong></td><td><span class="badge {{ $laporan->jenis_laporan === 'gangguan' ? 'badge-danger' : 'badge-warning' }}">{{ ucfirst($laporan->jenis_laporan) }}</span></td><td><strong>{{ $laporan->judul }}</strong><div class="muted">{{ $laporan->deskripsi }}</div></td><td><span class="badge {{ $laporan->status_penanganan === 'selesai' ? 'badge-success' : ($laporan->status_penanganan === 'diproses' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($laporan->status_penanganan) }}</span></td><td>@if($laporan->foto_path)<a href="{{ asset('storage/' . $laporan->foto_path) }}" target="_blank" class="btn btn-outline btn-sm">Lihat</a>@else - @endif</td></tr>
@empty <tr><td colspan="6">@include('layouts.partials.empty-state', ['message' => 'Belum ada laporan gangguan/keluhan.'])</td></tr> @endforelse
</tbody></table><div style="margin-top:10px;">{{ $laporans->links() }}</div></div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const map = L.map('map').setView([-7.6189, 110.9507], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
const statusColor = { aktif: '#16a34a', menunggak: '#d97706', gangguan: '#dc2626' };
const points = @json($mapPoints); const bounds = [];
points.forEach((point) => { const marker = L.circleMarker([point.lat, point.lng], { radius: 8, color: statusColor[point.status] ?? '#2563eb', fillOpacity: .9 }).addTo(map); marker.bindPopup(`<strong>${point.name}</strong><br>Kode: ${point.kode}<br>Status: ${point.status}<br><a href="${point.show_url}">Lihat detail</a>`); bounds.push([point.lat, point.lng]); });
if (bounds.length) map.fitBounds(bounds, { padding: [30, 30] });
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
        if (!bounds.length) {
            map.setView([pos.coords.latitude, pos.coords.longitude], 15);
        }
        L.circleMarker([pos.coords.latitude, pos.coords.longitude], { radius: 7, color: '#2563eb' }).addTo(map).bindPopup('Lokasi petugas saat ini');
    });
}
</script>
@endpush
