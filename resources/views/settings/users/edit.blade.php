@extends('layouts.admin')

@section('title', 'Pengaturan • Edit User')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Edit User',
        'subtitle' => 'Perbarui data user dan lakukan reset password jika diperlukan.'
    ])
    @include('layouts.partials.alerts')

    <div class="grid-2">
        <div class="card">
            <h3 style="margin-top:0;">Data User</h3>
            <form method="POST" action="{{ route('settings.users.update', $user) }}" class="grid-2">
                @csrf
                @method('PUT')
                <div><label>Nama</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
                <div><label>Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required></div>

                <div><label>Role</label><input type="text" value="{{ $user->role?->name }}" disabled></div>

                @if($actor->isRoot())
                    <div>
                        <label>Desa</label>
                        <select name="desa_id" required>
                            @foreach($desas as $desa)
                                <option value="{{ $desa->id }}" @selected((string) old('desa_id', $user->desa_id) === (string) $desa->id)>{{ $desa->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($user->hasRole('petugas_lapangan'))
                    <div>
                        <label>Jenis Petugas</label>
                        <select name="petugas_subtype">
                            <option value="pencatat_meter" @selected(old('petugas_subtype', $user->petugas_subtype) === 'pencatat_meter')>Pencatat Meter</option>
                            <option value="penagih_iuran" @selected(old('petugas_subtype', $user->petugas_subtype) === 'penagih_iuran')>Penagih Iuran</option>
                        </select>
                    </div>
                @endif

                <div>
                    <label>Status</label>
                    <select name="is_active">
                        <option value="1" @selected(old('is_active', (string) (int) $user->is_active) == '1')>Aktif</option>
                        <option value="0" @selected(old('is_active', (string) (int) $user->is_active) == '0')>Nonaktif</option>
                    </select>
                </div>

                <div class="full" style="display:flex;gap:8px;">
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                    <a class="btn btn-outline" href="{{ route('settings.users.index') }}">Kembali</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Reset Password</h3>
            <form method="POST" action="{{ route('settings.users.reset-password', $user) }}" class="grid-2">
                @csrf
                @method('PUT')
                <div class="full"><label>Password Baru</label><input type="password" name="password" required></div>
                <div class="full"><label>Konfirmasi Password</label><input type="password" name="password_confirmation" required></div>
                <div class="full"><button class="btn btn-danger" type="submit">Reset Password</button></div>
            </form>
        </div>
    </div>
@endsection
