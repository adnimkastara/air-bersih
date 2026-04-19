@extends('layouts.admin')
@section('title', 'Tambah Catatan Meter')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Tambah Catatan Meter'])
@include('layouts.partials.alerts')
<div class="card" style="max-width:760px;">
<form method="POST" action="{{ route('meter_records.store') }}" enctype="multipart/form-data">@csrf
<div style="margin-bottom:14px;"><label>Pelanggan</label><select name="pelanggan_id" required><option value="">-- Pilih Pelanggan --</option>@foreach($pelanggans as $pelanggan)<option value="{{ $pelanggan->id }}" @selected(old('pelanggan_id') == $pelanggan->id)>{{ $pelanggan->kode_pelanggan ?? '-' }} - {{ $pelanggan->name }}</option>@endforeach</select></div>
<div style="margin-bottom:14px;"><label>Petugas Pencatat</label><select name="petugas_id"><option value="">-- Pilih Petugas --</option>@foreach($petugas as $user)<option value="{{ $user->id }}" @selected(old('petugas_id') == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
<div style="margin-bottom:14px;"><label>Meter Bulan Lalu</label><input type="number" name="meter_previous_month" value="{{ old('meter_previous_month') }}" min="0" required></div>
<div style="margin-bottom:14px;"><label>Meter Bulan Ini</label><input type="number" name="meter_current_month" value="{{ old('meter_current_month') }}" min="0" required></div>
<div style="margin-bottom:14px;"><label>Tanggal Catat</label><input type="date" name="recorded_at" value="{{ old('recorded_at', date('Y-m-d')) }}" required></div>
<div style="margin-bottom:14px;"><label>Upload Foto Meter</label><input type="file" name="meter_photo" accept="image/png,image/jpeg,image/webp"></div>
<div style="margin-bottom:14px;"><label>Status Verifikasi</label><select name="verification_status" required><option value="pending" @selected(old('verification_status', 'pending') === 'pending')>Pending</option><option value="terverifikasi" @selected(old('verification_status') === 'terverifikasi')>Terverifikasi</option><option value="ditolak" @selected(old('verification_status') === 'ditolak')>Ditolak</option></select></div>
<div style="margin-bottom:14px;"><label>Catatan Lapangan</label><textarea name="notes">{{ old('notes') }}</textarea></div>
<div style="display:flex;gap:8px;"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('meter_records.index') }}" class="btn btn-outline">Batal</a></div>
</form>
</div>
@endsection
