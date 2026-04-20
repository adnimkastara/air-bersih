@extends('layouts.admin')

@section('title', 'Pengaturan • Setting Aplikasi')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Setting Aplikasi',
        'subtitle' => $user->isRoot() ? 'Kelola identitas global aplikasi.' : 'Kelola identitas unit pengelola desa Anda.'
    ])
    @include('layouts.partials.alerts')

    <div class="card">
        <form method="POST" action="{{ route('settings.app.update') }}" class="grid-2">
            @csrf
            @method('PUT')
            <div><label>Nama Kecamatan</label><input type="text" name="nama_kecamatan" value="{{ old('nama_kecamatan', $setting->nama_kecamatan ?: ($user->isRoot() ? null : $globalSetting->nama_kecamatan)) }}"></div>
            <div><label>Nama Unit Pengelola</label><input type="text" name="nama_unit_pengelola" value="{{ old('nama_unit_pengelola', $setting->nama_unit_pengelola) }}" placeholder="Contoh: BUMDES Berkah Mulya Desa Karanganyar"></div>
            <div><label>Tipe Pengelola</label><input type="text" name="tipe_pengelola" value="{{ old('tipe_pengelola', $setting->tipe_pengelola) }}" placeholder="BUMDES / KPSPAM / Lainnya"></div>
            <div><label>Nama Aplikasi</label><input type="text" name="nama_aplikasi" value="{{ old('nama_aplikasi', $setting->nama_aplikasi) }}" placeholder="Opsional"></div>
            <div><label>Alamat</label><input type="text" name="alamat" value="{{ old('alamat', $setting->alamat) }}"></div>
            <div><label>Kontak</label><input type="text" name="kontak" value="{{ old('kontak', $setting->kontak) }}"></div>
            <div><label>Nama Ketua / Direktur</label><input type="text" name="nama_ketua_direktur" value="{{ old('nama_ketua_direktur', $setting->nama_ketua_direktur) }}"></div>
            <div><label>Nama Sekretaris</label><input type="text" name="nama_sekretaris" value="{{ old('nama_sekretaris', $setting->nama_sekretaris) }}"></div>
            <div><label>Nama Bendahara</label><input type="text" name="nama_bendahara" value="{{ old('nama_bendahara', $setting->nama_bendahara) }}"></div>
            <div class="full" style="display:flex;gap:8px;">
                <button class="btn btn-primary" type="submit">Simpan Setting</button>
            </div>
        </form>
    </div>
@endsection
