@extends('layouts.admin')

@section('title', 'Pengaturan • Tambah User')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Tambah User',
        'subtitle' => $actor->isRoot() ? 'Root hanya dapat membuat akun admin desa.' : 'Admin desa hanya dapat membuat petugas lapangan untuk desanya sendiri.'
    ])
    @include('layouts.partials.alerts')

    <div class="card">
        <form method="POST" action="{{ route('settings.users.store') }}" class="grid-2">
            @csrf
            <div><label>Nama</label><input type="text" name="name" value="{{ old('name') }}" required></div>
            <div><label>Email Login</label><input type="email" name="email" value="{{ old('email') }}" required></div>

            <div>
                <label>Role</label>
                <input type="text" value="{{ $actor->isRoot() ? 'admin_desa' : 'petugas_lapangan' }}" disabled>
            </div>

            @if($actor->isRoot())
                <div>
                    <label>Desa</label>
                    <select name="desa_id" required>
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
