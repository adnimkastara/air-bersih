@extends('layouts.admin')
@section('title', 'Gangguan & Keluhan')
@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
@endsection
@section('content')
@include('layouts.partials.page-header', ['title' => 'Gangguan & Keluhan', 'subtitle' => 'Data keluhan terintegrasi dashboard dan monitoring.', 'actions' => '<a href="'.route('keluhan.create').'" class="btn btn-primary">Tambah Keluhan</a>'])
@include('layouts.partials.alerts')
<div class="grid-2">
    <div class="card">
        <form method="GET" class="grid-2">
            <div><label>Status</label><select name="status_penanganan"><option value="">Semua</option><option value="baru" @selected(($filters['status_penanganan'] ?? '')==='baru')>Baru</option><option value="diproses" @selected(($filters['status_penanganan'] ?? '')==='diproses')>Diproses</option><option value="selesai" @selected(($filters['status_penanganan'] ?? '')==='selesai')>Selesai</option></select></div>
            <div><label>Prioritas</label><select name="prioritas"><option value="">Semua</option><option value="rendah" @selected(($filters['prioritas'] ?? '')==='rendah')>Rendah</option><option value="sedang" @selected(($filters['prioritas'] ?? '')==='sedang')>Sedang</option><option value="tinggi" @selected(($filters['prioritas'] ?? '')==='tinggi')>Tinggi</option></select></div>
            <div class="full" style="display:flex;gap:8px;"><button class="btn btn-primary">Filter</button><a href="{{ route('keluhan.index') }}" class="btn btn-outline">Reset</a></div>
        </form>
    </div>
    <div class="card"><div id="keluhan-map" style="height:260px;border-radius:12px;"></div></div>
</div>
<div class="card table-wrap"><table><thead><tr><th>Kode</th><th>Judul</th><th>Jenis</th><th>Prioritas</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($laporans as $item)
<tr><td>{{ $item->kode_keluhan ?? '-' }}</td><td>{{ $item->judul }}</td><td>{{ ucfirst($item->jenis_laporan) }}</td><td>{{ ucfirst($item->prioritas ?? 'sedang') }}</td><td>{{ ucfirst($item->status_penanganan) }}</td><td><a href="{{ route('keluhan.show', $item) }}" class="btn btn-outline btn-sm">Detail</a></td></tr>
@empty
<tr><td colspan="6">Belum ada keluhan.</td></tr>
@endforelse
</tbody></table><div style="margin-top:10px;">{{ $laporans->links() }}</div></div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
const points = @json($mapPoints);
const map = L.map('keluhan-map').setView([-7.6189, 110.9507], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
const bounds = [];
points.forEach((p) => { L.marker([p.lat, p.lng]).addTo(map).bindPopup(`<strong>${p.judul}</strong><br><a href="${p.url}">Detail</a>`); bounds.push([p.lat,p.lng]);});
if (bounds.length) map.fitBounds(bounds, {padding:[20,20]});
</script>
@endpush
