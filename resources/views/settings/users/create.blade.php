@extends('layouts.admin')

@section('title', 'Pengaturan • Tambah User')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Tambah User',
        'subtitle' => $actor->isKecamatanLevel() ? 'Akun level kecamatan dapat membuat admin kecamatan/admin desa/petugas lapangan.' : 'Admin desa hanya dapat membuat petugas lapangan untuk desanya sendiri.'
    ])
    @include('layouts.partials.alerts')

    <div class="card">
        <form method="POST" action="{{ route('settings.users.store') }}" class="grid-2">
            @csrf
            <div><label>Nama</label><input type="text" name="name" value="{{ old('name') }}" required></div>
            <div><label>Email Login</label><input type="email" name="email" value="{{ old('email') }}" required></div>
            <div><label>No. HP / WhatsApp</label><input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx"></div>

            <div>
                <label>Role</label>
                @if($actor->isKecamatanLevel())
                    <select name="role_name" required>
                        <option value="admin_kecamatan" @selected(old('role_name') === 'admin_kecamatan')>admin_kecamatan</option>
                        <option value="admin_desa" @selected(old('role_name', 'admin_desa') === 'admin_desa')>admin_desa</option>
                        <option value="petugas_lapangan" @selected(old('role_name') === 'petugas_lapangan')>petugas_lapangan</option>
                    </select>
                @else
                    <input type="text" value="petugas_lapangan" disabled>
                    <input type="hidden" name="role_name" value="petugas_lapangan">
                @endif
            </div>

            @if($actor->isKecamatanLevel())
                <div>
                    <label>Kecamatan</label>
                    <select name="kecamatan_id">
                        <option value="">-- Pilih Kecamatan --</option>
                        @foreach($kecamatans as $kecamatan)
                            <option value="{{ $kecamatan->id }}" @selected((string) old('kecamatan_id', $actor->kecamatan_id) === (string) $kecamatan->id)>{{ $kecamatan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Desa</label>
                    <select name="desa_id">
                        <option value="">-- Pilih Desa --</option>
                        @foreach($desas as $desa)
                            <option value="{{ $desa->id }}" @selected((string) old('desa_id') === (string) $desa->id)>{{ $desa->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label>Jenis Petugas</label>
                    <select name="petugas_subtype">
                        <option value="pencatat_meter" @selected(old('petugas_subtype') === 'pencatat_meter')>Pencatat Meter</option>
                        <option value="penagih_iuran" @selected(old('petugas_subtype') === 'penagih_iuran')>Penagih Iuran</option>
                    </select>
                </div>
            @endif

            <div>
                <label>Status</label>
                <select name="is_active">
                    <option value="1" @selected(old('is_active', '1') == '1')>Aktif</option>
                    <option value="0" @selected(old('is_active') == '0')>Nonaktif</option>
                </select>
            </div>

            <div><label>Password</label><input type="password" name="password" required></div>
            <div><label>Konfirmasi Password</label><input type="password" name="password_confirmation" required></div>

            <div class="full" style="display:flex;gap:8px;">
                <button class="btn btn-primary" type="submit">Simpan User</button>
                <a class="btn btn-outline" href="{{ route('settings.users.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
