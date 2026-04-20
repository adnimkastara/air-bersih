@extends('layouts.admin')

@section('title', 'Tambah Desa')

@section('content')
    @include('layouts.partials.page-header', ['title' => 'Tambah Desa', 'subtitle' => 'Isi data wilayah desa baru.'])
    @include('layouts.partials.alerts')

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('desa.store') }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label>Pilih Kecamatan</label>
                <select name="kecamatan_id" required>
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id') == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label>Kode Desa</label>
                <input type="text" name="kode_desa" value="{{ old('kode_desa') }}" required placeholder="Contoh: 2001">
                <small style="color:var(--muted);">Kode bisnis desa (unik per kecamatan), dipakai sebagai prefix kode pelanggan.</small>
            </div>

            <div style="margin-bottom:14px;">
                <label>Nama Desa</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('desa.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
