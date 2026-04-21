@extends('layouts.admin')

@section('title', 'Pengaturan • Branding Aplikasi')

@section('content')
    @include('layouts.partials.page-header', [
        'title' => 'Branding Aplikasi',
        'subtitle' => 'Atur identitas aplikasi yang dipakai landing page, login, navbar, sidebar, title, dan favicon.'
    ])
    @include('layouts.partials.alerts')
    @php
        $logoUrl = \App\Support\BrandingResolver::resolveImageUrl($setting->logo_path, 'assets/logo/logo-main.svg');
        $logoIconUrl = \App\Support\BrandingResolver::resolveImageUrl($setting->logo_icon_path, 'assets/logo/logo-icon.svg');
        $faviconUrl = \App\Support\BrandingResolver::resolveImageUrl($setting->favicon_path, 'favicon.ico');
        $globalBranding = \App\Support\BrandingResolver::resolve($user);
    @endphp

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
                <label>Warna Tema Sekunder</label>
                <input type="color" name="secondary_color" value="{{ old('secondary_color', $setting->secondary_color ?: '#14b8a6') }}" style="height: 44px;">
            </div>

            <div>
                <label>Logo (Landing + Login + Navbar)</label>
                <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg">
                @if($logoUrl)
                    <small style="display:block; margin-top:6px; color:#64748b;">
                        Logo aktif: <a href="{{ $logoUrl }}" target="_blank">lihat file</a>
                    </small>
                @endif
            </div>

            <div>
                <label>Logo Icon (Sidebar + ikon ringkas)</label>
                <input type="file" name="logo_icon" accept=".png,.jpg,.jpeg,.webp,.svg">
                @if($logoIconUrl)
                    <small style="display:block; margin-top:6px; color:#64748b;">Logo icon aktif: <a href="{{ $logoIconUrl }}" target="_blank">lihat file</a></small>
                @endif
            </div>

            <div>
                <label>Favicon</label>
                <input type="file" name="favicon" accept=".png,.ico">
                @if($faviconUrl)
                    <small style="display:block; margin-top:6px; color:#64748b;">Favicon aktif: <a href="{{ $faviconUrl }}" target="_blank">lihat file</a></small>
                @endif
            </div>

            <div class="full" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
                <div style="border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                    <small style="display:block; margin-bottom:6px; color:#64748b;">Preview Logo</small>
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Preview logo" style="max-height:56px; width:auto;">
                    @else
                        <small style="color:#94a3b8;">Belum ada logo.</small>
                    @endif
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                    <small style="display:block; margin-bottom:6px; color:#64748b;">Preview Logo Icon</small>
                    @if($logoIconUrl)
                        <img src="{{ $logoIconUrl }}" alt="Preview logo icon" style="height:40px; width:40px; object-fit:contain;">
                    @else
                        <small style="color:#94a3b8;">Belum ada logo icon.</small>
                    @endif
                </div>
                <div style="border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                    <small style="display:block; margin-bottom:6px; color:#64748b;">Preview Favicon</small>
                    @if($faviconUrl)
                        <img src="{{ $faviconUrl }}" alt="Preview favicon" style="height:24px; width:24px; object-fit:contain;">
                    @else
                        <small style="color:#94a3b8;">Belum ada favicon.</small>
                    @endif
                </div>
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

