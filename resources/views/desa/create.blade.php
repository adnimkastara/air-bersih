<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Desa</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}form{max-width:600px;background:#fff;padding:20px;border:1px solid #e2e8f0;border-radius:12px;}label{display:block;margin-bottom:8px;}select,input{width:100%;padding:10px;margin-bottom:16px;border:1px solid #cbd5e1;border-radius:10px;}button{padding:12px 18px;border-radius:10px;background:#0f172a;color:#fff;border:none;cursor:pointer;}a{color:#2563eb;}</style>
</head>
<body>
    <h1>Tambah Desa</h1>

    @if($errors->any())
        <div style="margin-bottom:16px;padding:14px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('desa.store') }}">
        @csrf
        <label>Pilih Kecamatan</label>
        <select name="kecamatan_id" required>
            <option value="">-- Pilih Kecamatan --</option>
            @foreach($kecamatans as $kecamatan)
                <option value="{{ $kecamatan->id }}" {{ old('kecamatan_id') == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
            @endforeach
        </select>

        <label>Nama Desa</label>
        <input type="text" name="name" value="{{ old('name') }}" required>

        <button type="submit">Simpan</button>
    </form>

    <p><a href="{{ route('desa.index') }}">Kembali ke Daftar Desa</a></p>
</body>
</html>
