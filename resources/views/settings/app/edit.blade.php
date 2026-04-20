@extends('layouts.admin')

@section('title', 'Pengaturan • Branding Aplikasi')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Branding Aplikasi',
        'subtitle' => 'Atur identitas aplikasi yang dipakai landing page, login, navbar, sidebar, title, dan favicon.'
    ])
    @include('layouts.partials.alerts')

    <div class="card">
        <form method="POST" action="{{ route('settings.app.update') }}" enctype="multipart/form-data" class="grid-2">
            @csrf
            @method('PUT')

            <div>
                <label>Nama Aplikasi</label>
                <input type="text" name="nama_aplikasi" value="{{ old('nama_aplikasi', $setting->nama_aplikasi) }}" placeholder="Tirta Sejahtera">
            </div>

            <div>
                <label>Subjudul Aplikasi</label>
                <input type="text" name="subjudul_aplikasi" value="{{ old('subjudul_aplikasi', $setting->subjudul_aplikasi) }}" placeholder="Sistem Pengelolaan Air Bersih Desa dan Kecamatan">
            </div>

            <div>
                <label>Warna Tema Utama</label>
                <input type="color" name="theme_color" value="{{ old('theme_color', $setting->theme_color ?: '#1d4ed8') }}" style="height: 44px;">
            </div>

            <div>
                <label>Logo (Landing + Login + Navbar + Sidebar)</label>
                <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg">
                @if($setting->logo_path)
                    <small style="display:block; margin-top:6px; color:#64748b;">Logo aktif: <a href="{{ \\Illuminate\\Support\\Facades\\Storage::disk('public')->url($setting->logo_path) }}" target="_blank">lihat file</a></small>
                @endif
            </div>

            <div>
                <label>Favicon</label>
                <input type="file" name="favicon" accept=".png,.ico">
                @if($setting->favicon_path)
                    <small style="display:block; margin-top:6px; color:#64748b;">Favicon aktif: <a href="{{ \\Illuminate\\Support\\Facades\\Storage::disk('public')->url($setting->favicon_path) }}" target="_blank">lihat file</a></small>
                @endif
            </div>

            <div><label>Nama Kecamatan</label><input type="text" name="nama_kecamatan" value="{{ old('nama_kecamatan', $setting->nama_kecamatan ?: ($user->isKecamatanLevel() ? null : $globalSetting->nama_kecamatan)) }}"></div>
            <div><label>Nama Unit Pengelola</label><input type="text" name="nama_unit_pengelola" value="{{ old('nama_unit_pengelola', $setting->nama_unit_pengelola) }}" placeholder="Contoh: BUMDES Berkah Mulya Desa Karanganyar"></div>
            <div><label>Tipe Pengelola</label><input type="text" name="tipe_pengelola" value="{{ old('tipe_pengelola', $setting->tipe_pengelola) }}" placeholder="BUMDES / KPSPAM / Lainnya"></div>
            <div><label>Alamat</label><input type="text" name="alamat" value="{{ old('alamat', $setting->alamat) }}"></div>
            <div><label>Kontak</label><input type="text" name="kontak" value="{{ old('kontak', $setting->kontak) }}"></div>
            <div><label>Nama Ketua / Direktur</label><input type="text" name="nama_ketua_direktur" value="{{ old('nama_ketua_direktur', $setting->nama_ketua_direktur) }}"></div>
            <div><label>Nama Sekretaris</label><input type="text" name="nama_sekretaris" value="{{ old('nama_sekretaris', $setting->nama_sekretaris) }}"></div>
            <div><label>Nama Bendahara</label><input type="text" name="nama_bendahara" value="{{ old('nama_bendahara', $setting->nama_bendahara) }}"></div>

            <div class="full" style="display:flex;gap:8px;">
                <button class="btn btn-primary" type="submit">Simpan Branding</button>
            </div>
        </form>
    </div>
@endsection
