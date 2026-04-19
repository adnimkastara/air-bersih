@extends('layouts.admin')
@section('title', 'Edit Pelanggan')
@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endsection
@section('content')
@include('layouts.partials.page-header', ['title' => 'Edit Pelanggan'])
@include('layouts.partials.alerts')
<div class="card">
<form method="POST" action="{{ route('pelanggan.update', $pelanggan) }}">@csrf @method('PUT')
<div class="grid-2">
<div><label>Kode Pelanggan</label><input type="text" name="kode_pelanggan" value="{{ old('kode_pelanggan', $pelanggan->kode_pelanggan) }}" required></div>
<div><label>Nama Pelanggan</label><input type="text" name="name" value="{{ old('name', $pelanggan->name) }}" required></div>
<div><label>No HP</label><input type="text" name="phone" value="{{ old('phone', $pelanggan->phone) }}"></div>
<div><label>Email</label><input type="email" name="email" value="{{ old('email', $pelanggan->email) }}"></div>
<div class="full"><label>Alamat</label><textarea name="address" required>{{ old('address', $pelanggan->address) }}</textarea></div>
<div><label>Dusun</label><input type="text" name="dusun" value="{{ old('dusun', $pelanggan->dusun) }}" required></div>
<div><label>Jenis Pelanggan</label><select name="jenis_pelanggan" required>@foreach(['rumah_tangga' => 'Rumah Tangga', 'niaga' => 'Niaga', 'sosial' => 'Sosial', 'instansi' => 'Instansi'] as $value => $label)<option value="{{ $value }}" @selected(old('jenis_pelanggan', $pelanggan->jenis_pelanggan) === $value)>{{ $label }}</option>@endforeach</select></div>
<div><label>Nomor Meter</label><input type="text" name="nomor_meter" value="{{ old('nomor_meter', $pelanggan->nomor_meter) }}" required></div>
<div><label>Status</label><select name="status" required><option value="aktif" @selected(old('status', $pelanggan->status) == 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $pelanggan->status) == 'nonaktif')>Nonaktif</option></select></div>
<div><label>Kecamatan</label><select name="kecamatan_id"><option value="">-- Pilih Kecamatan --</option>@foreach($kecamatans as $kecamatan)<option value="{{ $kecamatan->id }}" @selected(old('kecamatan_id', $pelanggan->kecamatan_id) == $kecamatan->id)>{{ $kecamatan->name }}</option>@endforeach</select></div>
<div><label>Desa</label><select name="desa_id" required><option value="">-- Pilih Desa --</option>@foreach($desas as $desa)<option value="{{ $desa->id }}" @selected(old('desa_id', $pelanggan->desa_id) == $desa->id)>{{ $desa->name }} ({{ $desa->kecamatan?->name ?? 'Kecamatan' }})</option>@endforeach</select></div>
<div><label>Assign ke Petugas</label><select name="assigned_petugas_id"><option value="">-- Pilih Petugas --</option>@foreach($petugas as $user)<option value="{{ $user->id }}" @selected(old('assigned_petugas_id', $pelanggan->assigned_petugas_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
<div><label>Latitude</label><input id="latitude" type="text" name="latitude" value="{{ old('latitude', $pelanggan->latitude) }}"></div>
<div><label>Longitude</label><input id="longitude" type="text" name="longitude" value="{{ old('longitude', $pelanggan->longitude) }}"></div>
<div class="full"><label>Ubah Titik di Peta</label><div id="map" style="height:330px;border:1px solid var(--line);border-radius:12px;"></div></div>
</div>
<div style="display:flex;gap:8px;margin-top:14px;"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('pelanggan.index') }}" class="btn btn-outline">Batal</a></div>
</form></div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const initialLat = Number(@json(old('latitude', $pelanggan->latitude ?: -2.548926))); const initialLng = Number(@json(old('longitude', $pelanggan->longitude ?: 118.014863)));
const map = L.map('map').setView([initialLat, initialLng], (initialLat === -2.548926 && initialLng === 118.014863) ? 5 : 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
let marker = (!Number.isNaN(initialLat) && !Number.isNaN(initialLng)) ? L.marker([initialLat, initialLng]).addTo(map) : null;
map.on('click', ({latlng}) => { document.getElementById('latitude').value = latlng.lat.toFixed(7); document.getElementById('longitude').value = latlng.lng.toFixed(7); marker ? marker.setLatLng(latlng) : marker = L.marker(latlng).addTo(map); });
</script>
@endpush
