@extends('layouts.admin')
@section('title', 'Setting Tarif')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Setting Tarif', 'subtitle' => 'Kelola tarif pelanggan desa. Tarif kecamatan hanya dikelola oleh Root.'])
@include('layouts.partials.alerts')

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h3 style="margin:0;">A. Tarif Desa (Pelanggan Rumah Tangga)</h3>
        @if($canManageKecamatanTarif)
            <a href="{{ route('settings.tarif.kecamatan') }}" class="btn btn-outline"><i class="bi bi-building"></i> Kelola Tarif Kecamatan</a>
        @endif
    </div>
    <form method="POST" action="{{ route('settings.tarif.store-desa') }}" class="grid-2" style="margin-top:12px;">
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
@endsection
