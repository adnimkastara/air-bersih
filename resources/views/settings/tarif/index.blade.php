@extends('layouts.admin')
@section('title', 'Setting Tarif')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Setting Tarif', 'subtitle' => 'Pisahkan tarif pelanggan desa dan tarif setoran desa ke kecamatan.'])
@include('layouts.partials.alerts')

<div class="card">
    <h3 style="margin-top:0;">A. Tarif Desa (Pelanggan Rumah Tangga)</h3>
    <form method="POST" action="{{ route('settings.tarif.store-desa') }}" class="grid-2">
        @csrf
        <div><label>Nama Tarif</label><input type="text" name="name" required></div>
        <div><label>Desa</label><select name="village_id" @if(!auth()->user()->isRoot()) disabled @endif required>
            @foreach($desas as $desa)
                <option value="{{ $desa->id }}">{{ $desa->name }}</option>
            @endforeach
        </select></div>
        <div><label>Kategori Pelanggan</label><input type="text" name="category" placeholder="rumah_tangga"></div>
        <div><label>Status</label><select name="status"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
        <div><label>Abonemen</label><input type="number" min="0" name="abonemen" value="0" required></div>
        <div><label>Tarif Dasar</label><input type="number" min="0" name="tarif_dasar" value="0" required></div>
        <div><label>Tarif per m³</label><input type="number" min="0" name="tarif_per_m3" value="0" required></div>
        <div><label>Denda per Hari</label><input type="number" min="0" name="late_fee_per_day" value="0"></div>
        <div class="full"><button class="btn btn-primary" type="submit">Simpan Tarif Desa</button></div>
    </form>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Nama</th><th>Desa</th><th>Kategori</th><th>Abonemen</th><th>Dasar</th><th>per m3</th><th>Denda</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($desaTarifs as $tarif)
            <tr>
                <td>{{ $tarif->name }}</td>
                <td>{{ $tarif->desa?->name ?? '-' }}</td>
                <td>{{ $tarif->category ?? 'semua' }}</td>
                <td>Rp {{ number_format($tarif->abonemen, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->tarif_dasar, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->tarif_per_m3, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->late_fee_per_day, 0, ',', '.') }}</td>
                <td>{{ ucfirst($tarif->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="8">Belum ada tarif desa.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h3 style="margin-top:0;">B. Tarif Kecamatan (Setoran Desa ke Kecamatan)</h3>
    @if($canManageKecamatanTarif)
    <form method="POST" action="{{ route('settings.tarif.store-kecamatan') }}" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;">
        @csrf
        <div><label>Nama Tarif</label><input type="text" name="name" value="Tarif Setoran Kecamatan" required></div>
        <div><label>Tarif per m³</label><input type="number" min="0" name="tarif_per_m3" value="600" required></div>
        <div><label>Status</label><select name="status"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
        <div style="display:flex;align-items:end;"><button class="btn btn-primary" type="submit">Simpan Tarif Kecamatan</button></div>
    </form>
    @else
    <p>Tarif kecamatan hanya dapat diubah oleh Root.</p>
    @endif
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
