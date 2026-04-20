@extends('layouts.admin')
@section('title', 'Input Keluhan')
@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
@endsection
@section('content')
@include('layouts.partials.page-header', ['title' => 'Input Keluhan / Gangguan', 'subtitle' => 'Form additive untuk sumber data dashboard keluhan.'])
<div class="card">
<form method="POST" action="{{ route('keluhan.store') }}" enctype="multipart/form-data" class="grid-2">
@csrf
<div><label>Pelanggan (opsional)</label><select name="pelanggan_id" id="pelanggan_id"><option value="">-</option>@foreach($pelanggans as $pelanggan)<option value="{{ $pelanggan->id }}" @selected(old('pelanggan_id') == $pelanggan->id)>{{ $pelanggan->kode_pelanggan }} - {{ $pelanggan->name }}</option>@endforeach</select></div>
<div><label>Nama Pelapor</label><input type="text" name="pelapor" id="pelapor" value="{{ old('pelapor') }}" placeholder="Isi manual jika bukan pelanggan"></div>
<div><label>No. HP / WhatsApp Pelapor</label><input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" required></div>
<div><label>Desa (opsional)</label><select name="desa_id"><option value="">-</option>@foreach($desas as $desa)<option value="{{ $desa->id }}">{{ $desa->name }}</option>@endforeach</select></div>
<div><label>Jenis</label><select name="jenis_laporan" required><option value="gangguan">Gangguan</option><option value="keluhan">Keluhan</option></select></div>
<div><label>Prioritas</label><select name="prioritas" required><option value="rendah">Rendah</option><option value="sedang" selected>Sedang</option><option value="tinggi">Tinggi</option></select></div>
<div><label>Status</label><select name="status_penanganan" required><option value="baru">Baru</option><option value="diproses">Diproses</option><option value="selesai">Selesai</option></select></div>
<div><label>Tanggal Kejadian</label><input type="datetime-local" name="tanggal_kejadian"></div>
<div class="full"><label>Judul</label><input type="text" name="judul" required></div>
<div class="full"><label>Deskripsi</label><textarea name="deskripsi" rows="4" required></textarea></div>
<div class="full"><label>Lokasi Text</label><input type="text" name="lokasi_text"></div>
<input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
<input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
<div class="full"><div style="display:flex;gap:8px;margin-bottom:8px;"><button class="btn btn-outline" type="button" id="useMyLocation">Gunakan Lokasi Saya</button><span id="coordLabel">Belum pilih titik</span></div><div id="map" style="height:300px;border-radius:12px;"></div></div>
<div><label>Foto</label><input type="file" name="foto_gangguan" accept="image/*"></div>
<div class="full"><button class="btn btn-primary">Simpan</button></div>
</form>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
const fallbackCenter = @json($defaultCenter);
const pelangganOptions = @json($pelanggans->mapWithKeys(fn($item) => [$item->id => ['name' => $item->name, 'phone' => $item->phone]]));
const map = L.map('map').setView([fallbackCenter.lat, fallbackCenter.lng], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
const latInput = document.getElementById('latitude'); const lngInput = document.getElementById('longitude'); const coordLabel = document.getElementById('coordLabel');
const pelangganInput = document.getElementById('pelanggan_id');
const pelaporInput = document.getElementById('pelapor');
const noHpInput = document.getElementById('no_hp');
let marker;
function setPoint(lat, lng, zoom = 16) { latInput.value = lat; lngInput.value = lng; coordLabel.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`; if (!marker) { marker = L.marker([lat,lng], {draggable:true}).addTo(map); marker.on('dragend', (e) => {const p=e.target.getLatLng(); setPoint(p.lat,p.lng, map.getZoom());}); } else marker.setLatLng([lat,lng]); map.setView([lat,lng], zoom); }
function applyPelangganDefaults() {
    const selectedId = pelangganInput.value;
    if (!selectedId || !pelangganOptions[selectedId]) return;
    const pelanggan = pelangganOptions[selectedId];
    pelaporInput.value = pelanggan.name || pelaporInput.value;
    noHpInput.value = pelanggan.phone || noHpInput.value;
}
map.on('click', (e) => setPoint(e.latlng.lat, e.latlng.lng));
document.getElementById('useMyLocation').addEventListener('click', () => { if (!navigator.geolocation) return; navigator.geolocation.getCurrentPosition((pos) => setPoint(pos.coords.latitude, pos.coords.longitude), () => {}, {enableHighAccuracy:true,timeout:8000}); });
pelangganInput.addEventListener('change', applyPelangganDefaults);
if (latInput.value && lngInput.value) setPoint(parseFloat(latInput.value), parseFloat(lngInput.value));
applyPelangganDefaults();
</script>
@endpush
