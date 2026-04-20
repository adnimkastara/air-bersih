@extends('layouts.admin')
@section('title', 'Setting Tarif')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Setting Tarif', 'subtitle' => 'Kelola tarif per desa (abonemen, tarif dasar, dan kategori pelanggan).'])
@include('layouts.partials.alerts')

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h3 style="margin:0;">A. Tarif Desa</h3>
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
        <div><label>Kategori</label><input type="text" name="category" placeholder="rumah_tangga" required></div>
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
        <thead><tr><th>Nama</th><th>Desa</th><th>Kategori</th><th>Abonemen</th><th>Dasar</th><th>per m3</th><th>Denda</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($desaTarifs as $tarif)
            <tr>
                <td>{{ $tarif->name }}</td>
                <td>{{ $tarif->desa?->name ?? '-' }}</td>
                <td>{{ $tarif->category }}</td>
                <td>Rp {{ number_format($tarif->abonemen, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->tarif_dasar, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->tarif_per_m3, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->late_fee_per_day, 0, ',', '.') }}</td>
                <td>{{ ucfirst($tarif->status) }}</td>
                <td>
                    <details>
                        <summary>Edit</summary>
                        <form method="POST" action="{{ route('settings.tarif.update-desa', $tarif) }}" class="grid-2" style="margin-top:8px;min-width:420px;">
                            @csrf
                            @method('PUT')
                            <div><label>Nama</label><input type="text" name="name" value="{{ $tarif->name }}" required></div>
                            <div><label>Desa</label><select name="village_id" @if(!auth()->user()->isRoot()) disabled @endif required>
                                @foreach($desas as $desa)
                                    <option value="{{ $desa->id }}" @selected($desa->id === $tarif->village_id)>{{ $desa->name }}</option>
                                @endforeach
                            </select></div>
                            <div><label>Kategori</label><input type="text" name="category" value="{{ $tarif->category }}" required></div>
                            <div><label>Status</label><select name="status"><option value="aktif" @selected($tarif->status==='aktif')>Aktif</option><option value="nonaktif" @selected($tarif->status==='nonaktif')>Nonaktif</option></select></div>
                            <div><label>Abonemen</label><input type="number" min="0" name="abonemen" value="{{ (float) $tarif->abonemen }}" required></div>
                            <div><label>Tarif Dasar</label><input type="number" min="0" name="tarif_dasar" value="{{ (float) $tarif->tarif_dasar }}" required></div>
                            <div><label>Tarif per m³</label><input type="number" min="0" name="tarif_per_m3" value="{{ (float) $tarif->tarif_per_m3 }}" required></div>
                            <div><label>Denda per Hari</label><input type="number" min="0" name="late_fee_per_day" value="{{ (float) $tarif->late_fee_per_day }}"></div>
                            <div class="full"><button class="btn btn-primary btn-sm" type="submit">Update</button></div>
                        </form>
                        <form method="POST" action="{{ route('settings.tarif.destroy-desa', $tarif) }}" onsubmit="return confirm('Hapus tarif ini?')" style="margin-top:6px;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                        </form>
                    </details>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">Belum ada tarif desa.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
