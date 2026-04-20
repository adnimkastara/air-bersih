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
        $faviconUrl = \App\Support\BrandingResolver::resolveImageUrl($setting->favicon_path, 'favicon.ico');
        $logoIconUrl = $logoUrl;
        $storageLinkExists = \Illuminate\Support\Facades\File::exists(public_path('storage'));
    @endphp

    <div class="card">
        <div style="margin-bottom:12px; padding:10px 12px; border-radius:10px; border:1px solid #dbe4f0; background:#f8fafc; color:#334155; font-size:.85rem;">
            Status akses file upload:
            @if($storageLinkExists)
                <strong>public/storage tersedia</strong>.
            @else
                <strong>public/storage tidak ditemukan</strong>. Sistem tetap memakai fallback route <code>/branding-media/*</code> agar logo/favicon upload tetap tampil.
            @endif
        </div>

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
                @if($logoUrl)
                    <small style="display:block; margin-top:6px; color:#64748b;">
                        Logo aktif:
                        <a href="{{ $logoUrl }}" target="_blank">lihat file</a> ·
                        <a href="#" id="test-logo-url" data-url="{{ $logoUrl }}">Test URL Logo Aktif</a>
                    </small>
                    <small id="test-logo-result" style="display:block; margin-top:4px; color:#64748b;"></small>
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

@push('scripts')
<script>
    (function () {
        const trigger = document.getElementById('test-logo-url');
        const result = document.getElementById('test-logo-result');

        if (!trigger || !result) return;

        trigger.addEventListener('click', async function (event) {
            event.preventDefault();

            const targetUrl = this.dataset.url;
            if (!targetUrl) {
                result.textContent = 'URL logo tidak tersedia.';
                result.style.color = '#b91c1c';
                return;
            }

            result.textContent = 'Menguji akses URL logo...';
            result.style.color = '#64748b';

            try {
                const response = await fetch(targetUrl, { method: 'HEAD', cache: 'no-store' });
                if (response.ok) {
                    result.textContent = `Berhasil diakses (HTTP ${response.status}).`;
                    result.style.color = '#166534';
                } else {
                    result.textContent = `Gagal diakses (HTTP ${response.status}).`;
                    result.style.color = '#b45309';
                }
            } catch (error) {
                result.textContent = 'Gagal diakses (network/CORS/error browser). Coba klik "lihat file".';
                result.style.color = '#b91c1c';
            }
        });
    })();
</script>
@endpush
