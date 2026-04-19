@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endsection

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Data Pelanggan',
        'subtitle' => 'Kelola pelanggan, wilayah, status, dan assignment petugas.',
        'actions' => '<a href="'.route('pelanggan.create').'" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pelanggan</a>'
    ])
    @include('layouts.partials.alerts')

    <div class="card">
        <form method="GET" action="{{ route('pelanggan.index') }}" class="toolbar">
            <div><label>Pencarian</label><input type="text" name="q" placeholder="Kode, nama, meter, no hp" value="{{ $filters['q'] ?? '' }}"></div>
            <div><label>Status</label><select name="status"><option value="">Semua</option><option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option><option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option></select></div>
            <div><label>Desa</label><select name="desa_id"><option value="">Semua desa</option>@foreach($desas as $desa)<option value="{{ $desa->id }}" @selected((string)($filters['desa_id'] ?? '') === (string)$desa->id)>{{ $desa->name }}</option>@endforeach</select></div>
            <div><label>Petugas</label><select name="assigned_petugas_id"><option value="">Semua petugas</option>@foreach($petugas as $user)<option value="{{ $user->id }}" @selected((string)($filters['assigned_petugas_id'] ?? '') === (string)$user->id)>{{ $user->name }}</option>@endforeach</select></div>
            <div style="display:flex;gap:8px;"><button class="btn btn-primary" type="submit">Filter</button><a href="{{ route('pelanggan.index') }}" class="btn btn-outline">Reset</a></div>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Peta Titik Pelanggan</h3>
        <div id="map" style="height:360px;border:1px solid var(--line);border-radius:12px;"></div>
    </div>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>Kode</th><th>Nama</th><th>Wilayah</th><th>Jenis</th><th>Nomor Meter</th><th>Petugas</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($pelanggans as $pelanggan)
                <tr>
                    <td>{{ $pelanggan->kode_pelanggan ?? '-' }}</td>
                    <td>{{ $pelanggan->name }}</td>
                    <td>{{ $pelanggan->desa?->name ?? '-' }} / {{ $pelanggan->kecamatan?->name ?? '-' }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($pelanggan->jenis_pelanggan ?? '-')) }}</td>
                    <td>{{ $pelanggan->nomor_meter ?? '-' }}</td>
                    <td>{{ $pelanggan->assignedPetugas?->name ?? '-' }}</td>
                    <td><span class="badge {{ $pelanggan->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($pelanggan->status) }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="{{ route('pelanggan.show', $pelanggan) }}" class="btn btn-outline btn-sm">Detail</a>
                            <a href="{{ route('pelanggan.edit', $pelanggan) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form action="{{ route('pelanggan.destroy', $pelanggan) }}" method="POST" onsubmit="return confirm('Hapus pelanggan ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">@include('layouts.partials.empty-state', ['message' => 'Data pelanggan belum tersedia.'])</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $pelanggans->links() }}</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const map = L.map('map').setView([-2.548926, 118.014863], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
const points = @json($mapPoints); const bounds = [];
points.forEach((point) => { L.marker([point.lat, point.lng]).addTo(map).bindPopup(`<strong>${point.name}</strong><br>Kode: ${point.kode}<br><a href="${point.show_url}">Lihat detail</a>`); bounds.push([point.lat, point.lng]); });
if (bounds.length) map.fitBounds(bounds, { padding: [30, 30] });
</script>
@endpush
