@extends('layouts.admin')
@section('title', 'Setting Tarif Kecamatan')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Setting Tarif Kecamatan', 'subtitle' => 'Khusus Root: tarif setoran desa ke kecamatan.'])
@include('layouts.partials.alerts')

<div class="card">
    <h3 style="margin-top:0;">Tarif Kecamatan (Setoran Desa ke Kecamatan)</h3>
    <form method="POST" action="{{ route('settings.tarif.store-kecamatan') }}" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;">
        @csrf
        <div><label>Nama Tarif</label><input type="text" name="name" value="Tarif Setoran Kecamatan" required></div>
        <div><label>Tarif per m³</label><input type="number" min="0" name="tarif_per_m3" value="600" required></div>
        <div><label>Status</label><select name="status"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
        <div style="display:flex;align-items:end;"><button class="btn btn-primary" type="submit">Simpan Tarif Kecamatan</button></div>
    </form>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Nama</th><th>Tarif per m3</th><th>Status</th><th>Mulai Berlaku</th></tr></thead>
        <tbody>
        @forelse($kecamatanTarifs as $tarif)
            <tr>
                <td>{{ $tarif->name }}</td>
                <td>Rp {{ number_format($tarif->tarif_per_m3, 0, ',', '.') }}</td>
                <td>{{ ucfirst($tarif->status) }}</td>
                <td>{{ $tarif->effective_start?->format('Y-m-d') ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada tarif kecamatan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
