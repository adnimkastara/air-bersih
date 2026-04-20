@extends('layouts.admin')
@section('title', 'Detail Keluhan')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Detail Keluhan', 'subtitle' => $laporan->kode_keluhan ?? '-'])
@include('layouts.partials.alerts')
<div class="card">
    <p><strong>Judul:</strong> {{ $laporan->judul }}</p>
    <p><strong>Deskripsi:</strong> {{ $laporan->deskripsi }}</p>
    <p><strong>Pelapor:</strong> {{ $laporan->pelapor ?? '-' }}</p>
    <p><strong>No HP/WA Pelapor:</strong> {{ $laporan->no_hp ?? '-' }}</p>
    <p><strong>Koordinat:</strong> {{ $laporan->latitude ?? '-' }}, {{ $laporan->longitude ?? '-' }}</p>
    <form method="POST" action="{{ route('keluhan.update', $laporan) }}" class="grid-2">
        @csrf
        @method('PUT')
        <div><label>Status</label><select name="status_penanganan"><option value="baru" @selected($laporan->status_penanganan==='baru')>Baru</option><option value="diproses" @selected($laporan->status_penanganan==='diproses')>Diproses</option><option value="selesai" @selected($laporan->status_penanganan==='selesai')>Selesai</option></select></div>
        <div><label>Prioritas</label><select name="prioritas"><option value="rendah" @selected($laporan->prioritas==='rendah')>Rendah</option><option value="sedang" @selected($laporan->prioritas==='sedang')>Sedang</option><option value="tinggi" @selected($laporan->prioritas==='tinggi')>Tinggi</option></select></div>
        <div class="full"><button class="btn btn-primary">Update</button></div>
    </form>
</div>
@endsection
