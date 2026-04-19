@extends('layouts.admin')
@section('title', 'Detail Pelanggan')
@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endsection
@section('content')
@include('layouts.partials.page-header', ['title' => 'Detail Pelanggan', 'actions' => '<a href="'.route('pelanggan.edit', $pelanggan).'" class="btn btn-primary">Edit</a>'])
<div class="card">
<div class="grid-2">
<div><label>Kode Pelanggan</label><div>{{ $pelanggan->kode_pelanggan }}</div></div>
<div><label>Nama</label><div>{{ $pelanggan->name }}</div></div>
<div><label>Nomor Meter</label><div>{{ $pelanggan->nomor_meter ?? '-' }}</div></div>
<div><label>Jenis</label><div>{{ str_replace('_', ' ', ucfirst($pelanggan->jenis_pelanggan ?? '-')) }}</div></div>
<div><label>No HP</label><div>{{ $pelanggan->phone ?? '-' }}</div></div>
<div><label>Status</label><div><span class="badge {{ $pelanggan->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($pelanggan->status) }}</span></div></div>
<div><label>Desa / Kecamatan</label><div>{{ $pelanggan->desa?->name ?? '-' }} / {{ $pelanggan->kecamatan?->name ?? '-' }}</div></div>
<div><label>Dusun</label><div>{{ $pelanggan->dusun ?? '-' }}</div></div>
<div><label>Petugas</label><div>{{ $pelanggan->assignedPetugas?->name ?? '-' }}</div></div>
<div><label>Koordinat</label><div>{{ $pelanggan->latitude ?? '-' }}, {{ $pelanggan->longitude ?? '-' }}</div></div>
<div class="full"><label>Alamat</label><div>{{ $pelanggan->address }}</div></div>
</div>
<div style="margin-top:14px;"><label>Peta Lokasi</label><div id="map" style="height:340px;border:1px solid var(--line);border-radius:12px;"></div></div>
<div style="margin-top:14px;"><a href="{{ route('pelanggan.index') }}" class="btn btn-outline">Kembali ke daftar</a></div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const lat = Number(@json($pelanggan->latitude ?: -2.548926)); const lng = Number(@json($pelanggan->longitude ?: 118.014863));
const map = L.map('map').setView([lat, lng], (lat === -2.548926 && lng === 118.014863) ? 5 : 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
if (!Number.isNaN(lat) && !Number.isNaN(lng)) { L.marker([lat, lng]).addTo(map).bindPopup(`<strong>{{ addslashes($pelanggan->name) }}</strong><br>{{ addslashes($pelanggan->address) }}`).openPopup(); }
</script>
@endpush
