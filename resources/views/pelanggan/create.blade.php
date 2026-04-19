<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pelanggan</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}form{max-width:700px;background:#fff;padding:20px;border:1px solid #e2e8f0;border-radius:12px;}label{display:block;margin-bottom:8px;}select,input,textarea{width:100%;padding:10px;margin-bottom:16px;border:1px solid #cbd5e1;border-radius:10px;}button{padding:12px 18px;border-radius:10px;background:#0f172a;color:#fff;border:none;cursor:pointer;}a{color:#2563eb;}</style>
</head>
<body>
    <h1>Tambah Pelanggan</h1>

    @if($errors->any())
        <div style="margin-bottom:16px;padding:14px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pelanggan.store') }}">
        @csrf

        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" required>

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}">

        <label>Telepon</label>
        <input type="text" name="phone" value="{{ old('phone') }}">

        <label>Alamat</label>
        <textarea name="address">{{ old('address') }}</textarea>

        <label>Kecamatan</label>
        <select name="kecamatan_id">
            <option value="">-- Pilih Kecamatan --</option>
            @foreach($kecamatans as $kecamatan)
                <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id') == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
            @endforeach
        </select>

        <label>Desa</label>
        <select name="desa_id">
            <option value="">-- Pilih Desa --</option>
            @foreach($desas as $desa)
                <option value="{{ $desa->id }}" {{ old('desa_id') == $desa->id ? 'selected' : '' }}>{{ $desa->name }} ({{ $desa->kecamatan?->name ?? 'Kecamatan' }})</option>
            @endforeach
        </select>

        <label>Petugas Lapangan</label>
        <select name="assigned_petugas_id">
            <option value="">-- Pilih Petugas --</option>
            @foreach($petugas as $user)
                <option value="{{ $user->id }}" {{ old('assigned_petugas_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>

        <label>Latitude</label>
        <input type="text" name="latitude" value="{{ old('latitude') }}">

        <label>Longitude</label>
        <input type="text" name="longitude" value="{{ old('longitude') }}">

        <label>Status</label>
        <select name="status">
            <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>

        <button type="submit">Simpan</button>
    </form>

    <p><a href="{{ route('pelanggan.index') }}">Kembali ke Daftar Pelanggan</a></p>
</body>
</html>
