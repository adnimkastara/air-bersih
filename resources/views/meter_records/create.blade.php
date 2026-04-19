<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Catatan Meter</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}
        form{max-width:760px;background:#fff;padding:20px;border:1px solid #e2e8f0;border-radius:12px;}
        label{display:block;margin-bottom:8px;font-weight:600;}
        select,input,textarea{width:100%;padding:10px;margin-bottom:16px;border:1px solid #cbd5e1;border-radius:10px;}
        button{padding:12px 18px;border-radius:10px;background:#0f172a;color:#fff;border:none;cursor:pointer;}
        a{color:#2563eb;}
    </style>
</head>
<body>
    <h1>Tambah Catatan Meter</h1>

    @if($errors->any())
        <div style="margin-bottom:16px;padding:14px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('meter_records.store') }}" enctype="multipart/form-data">
        @csrf

        <label>Pelanggan</label>
        <select name="pelanggan_id" required>
            <option value="">-- Pilih Pelanggan --</option>
            @foreach($pelanggans as $pelanggan)
                <option value="{{ $pelanggan->id }}" {{ old('pelanggan_id') == $pelanggan->id ? 'selected' : '' }}>{{ $pelanggan->kode_pelanggan ?? '-' }} - {{ $pelanggan->name }}</option>
            @endforeach
        </select>

        <label>Petugas Pencatat</label>
        <select name="petugas_id">
            <option value="">-- Pilih Petugas --</option>
            @foreach($petugas as $user)
                <option value="{{ $user->id }}" {{ old('petugas_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
            @endforeach
        </select>

        <label>Meter Bulan Lalu</label>
        <input type="number" name="meter_previous_month" value="{{ old('meter_previous_month') }}" min="0" required>

        <label>Meter Bulan Ini</label>
        <input type="number" name="meter_current_month" value="{{ old('meter_current_month') }}" min="0" required>

        <label>Tanggal Catat</label>
        <input type="date" name="recorded_at" value="{{ old('recorded_at', date('Y-m-d')) }}" required>

        <label>Upload Foto Meter</label>
        <input type="file" name="meter_photo" accept="image/png,image/jpeg,image/webp">

        <label>Status Verifikasi</label>
        <select name="verification_status" required>
            <option value="pending" {{ old('verification_status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="terverifikasi" {{ old('verification_status') === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
            <option value="ditolak" {{ old('verification_status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>

        <label>Catatan Lapangan</label>
        <textarea name="notes" placeholder="Isi catatan jika ada kendala lapangan...">{{ old('notes') }}</textarea>

        <button type="submit">Simpan</button>
    </form>

    <p><a href="{{ route('meter_records.index') }}">Kembali ke Daftar Catatan Meter</a></p>
</body>
</html>
