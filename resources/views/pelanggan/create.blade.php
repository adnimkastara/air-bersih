@extends('layouts.admin')

@section('title', 'Tambah Pelanggan')
@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endsection

@section('content')
@include('layouts.partials.page-header', ['title' => 'Tambah Pelanggan'])
@include('layouts.partials.alerts')
<div class="card">
<form method="POST" action="{{ route('pelanggan.store') }}">
@csrf
<div class="grid-2">
<div><label>Nama Pelanggan</label><input type="text" name="name" value="{{ old('name') }}" required></div>
<div><label>Info Kode Pelanggan</label><div style="padding:10px 12px;border:1px dashed var(--line);border-radius:10px;background:#f8fbff;">Kode pelanggan akan digenerate otomatis berdasarkan kode desa.</div></div>
<div><label>No HP</label><input type="text" name="phone" value="{{ old('phone') }}"></div>
<div><label>Email (opsional)</label><input type="email" name="email" value="{{ old('email') }}"></div>
<div class="full"><label>Alamat</label><textarea name="address" required>{{ old('address') }}</textarea></div>
<div><label>Dusun</label><input type="text" name="dusun" value="{{ old('dusun') }}" required></div>
<div><label>Jenis Pelanggan</label><select name="jenis_pelanggan" required><option value="">-- Pilih --</option>@foreach(['rumah_tangga' => 'Rumah Tangga', 'niaga' => 'Niaga', 'sosial' => 'Sosial', 'instansi' => 'Instansi'] as $value => $label)<option value="{{ $value }}" @selected(old('jenis_pelanggan') === $value)>{{ $label }}</option>@endforeach</select></div>
<div><label>Nomor Meter</label><input type="text" name="nomor_meter" value="{{ old('nomor_meter') }}" required></div>
<div><label>Status</label><select name="status" required><option value="aktif" @selected(old('status') == 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status') == 'nonaktif')>Nonaktif</option></select></div>
<div><label>Kecamatan</label><select name="kecamatan_id"><option value="">-- Pilih Kecamatan --</option>@foreach($kecamatans as $kecamatan)<option value="{{ $kecamatan->id }}" @selected(old('kecamatan_id') == $kecamatan->id)>{{ $kecamatan->name }}</option>@endforeach</select></div>
<div><label>Desa</label><select name="desa_id" required><option value="">-- Pilih Desa --</option>@foreach($desas as $desa)<option value="{{ $desa->id }}" @selected(old('desa_id') == $desa->id)>{{ $desa->name }} ({{ $desa->kecamatan?->name ?? 'Kecamatan' }})</option>@endforeach</select></div>
<div><label>Assign ke Petugas</label><select name="assigned_petugas_id"><option value="">-- Pilih Petugas --</option>@foreach($petugas as $user)<option value="{{ $user->id }}" @selected(old('assigned_petugas_id') == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
<input id="latitude" type="hidden" name="latitude" value="{{ old('latitude') }}">
<input id="longitude" type="hidden" name="longitude" value="{{ old('longitude') }}">
<div class="full"><label>Pilih Titik di Peta</label><div style="display:flex;gap:8px;margin-bottom:8px;"><button class="btn btn-outline btn-sm" type="button" id="useMyLocation">Gunakan Lokasi Saya</button><span id="coordLabel">Belum memilih titik.</span></div><div id="map" style="height:330px;border:1px solid var(--line);border-radius:12px;"></div></div>
</div>
<div style="display:flex;gap:8px;margin-top:14px;"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('pelanggan.index') }}" class="btn btn-outline">Batal</a></div>
</form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const fallback = @json($defaultCenter);
const initialLat = Number(@json(old('latitude') ?: null));
const initialLng = Number(@json(old('longitude') ?: null));
const centerLat = Number.isFinite(initialLat) ? initialLat : fallback.lat;
const centerLng = Number.isFinite(initialLng) ? initialLng : fallback.lng;
const map = L.map('map').setView([centerLat, centerLng], Number.isFinite(initialLat) ? 15 : 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
let marker = null;
const coordLabel = document.getElementById('coordLabel');
const setPoint = (latlng, zoom = null) => { document.getElementById('latitude').value = latlng.lat.toFixed(7); document.getElementById('longitude').value = latlng.lng.toFixed(7); coordLabel.textContent = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`; marker ? marker.setLatLng(latlng) : marker = L.marker(latlng, {draggable: true}).addTo(map); if (marker.dragging) marker.on('dragend', (e) => setPoint(e.target.getLatLng())); if (zoom) map.setView(latlng, zoom); };
if (Number.isFinite(initialLat) && Number.isFinite(initialLng)) setPoint(L.latLng(initialLat, initialLng));
map.on('click', ({latlng}) => setPoint(latlng));
document.getElementById('useMyLocation').addEventListener('click', () => navigator.geolocation?.getCurrentPosition((pos) => setPoint(L.latLng(pos.coords.latitude, pos.coords.longitude), 16)));
</script>
@endpush
