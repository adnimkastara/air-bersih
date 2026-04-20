@extends('layouts.admin')
@section('title', 'Tambah Catatan Meter')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Tambah Catatan Meter'])
@include('layouts.partials.alerts')
<div class="card" style="max-width:760px;">
<form method="POST" action="{{ route('meter_records.store') }}" enctype="multipart/form-data">@csrf
<div style="margin-bottom:14px;">
    <label>Pelanggan</label>
    <select name="pelanggan_id" id="pelanggan_id" required>
        <option value="">-- Pilih Pelanggan --</option>
        @foreach($pelanggans as $pelanggan)
            <option value="{{ $pelanggan->id }}" data-last-meter="{{ $lastMeters[$pelanggan->id] ?? 0 }}" @selected((string) $selectedPelangganId === (string) $pelanggan->id)>{{ $pelanggan->kode_pelanggan ?? '-' }} - {{ $pelanggan->name }}</option>
        @endforeach
    </select>
</div>
<div style="margin-bottom:14px;"><label>Petugas Pencatat</label><select name="petugas_id"><option value="">-- Pilih Petugas --</option>@foreach($petugas as $user)<option value="{{ $user->id }}" @selected(old('petugas_id') == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
<div style="margin-bottom:14px;">
    <label>Meter Bulan Lalu</label>
    <input type="number" name="meter_previous_month" id="meter_previous_month" value="{{ $defaultPreviousMeter ?? 0 }}" min="0" required>
    <small id="meter_help" style="color:#64748b;display:block;margin-top:6px;">Terisi otomatis dari catatan meter terakhir, tetapi bisa diedit jika ada pergantian meter.</small>
</div>
<div style="margin-bottom:14px;"><label>Meter Bulan Ini</label><input type="number" name="meter_current_month" value="{{ old('meter_current_month') }}" min="0" required></div>
<div style="margin-bottom:14px;"><label>Tanggal Catat</label><input type="date" name="recorded_at" value="{{ old('recorded_at', date('Y-m-d')) }}" required></div>
<div style="margin-bottom:14px;"><label>Upload Foto Meter</label><input type="file" name="meter_photo" accept="image/png,image/jpeg,image/webp"></div>
<div style="margin-bottom:14px;"><label>Status Verifikasi</label><select name="verification_status" required><option value="pending" @selected(old('verification_status', 'pending') === 'pending')>Pending</option><option value="terverifikasi" @selected(old('verification_status') === 'terverifikasi')>Terverifikasi</option><option value="ditolak" @selected(old('verification_status') === 'ditolak')>Ditolak</option></select></div>
<div style="margin-bottom:14px;"><label>Catatan Lapangan</label><textarea name="notes" placeholder="Opsional. Wajib diisi jika angka meter bulan ini lebih kecil dari bulan lalu.">{{ old('notes') }}</textarea></div>
<div style="display:flex;gap:8px;"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('meter_records.index') }}" class="btn btn-outline">Batal</a></div>
</form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const pelangganSelect = document.getElementById('pelanggan_id');
        const previousInput = document.getElementById('meter_previous_month');
        const help = document.getElementById('meter_help');

        if (!pelangganSelect || !previousInput) return;

        function autofillPreviousMeter() {
            const option = pelangganSelect.options[pelangganSelect.selectedIndex];
            if (!option || !option.value) {
                previousInput.value = 0;
                help.textContent = 'Belum memilih pelanggan. Pilih pelanggan untuk mengisi meter bulan lalu otomatis.';
                return;
            }

            const lastMeter = parseInt(option.dataset.lastMeter || '0', 10);
            previousInput.value = Number.isNaN(lastMeter) ? 0 : lastMeter;

            help.textContent = lastMeter > 0
                ? `Terisi otomatis dari catatan meter terakhir (${lastMeter}). Tetap bisa Anda ubah manual jika ada pergantian meter.`
                : 'Belum ada data meter sebelumnya. Nilai awal diisi 0 dan bisa diubah manual bila diperlukan.';
        }

        pelangganSelect.addEventListener('change', autofillPreviousMeter);

        if (!{{ old('meter_previous_month') === null ? 'true' : 'false' }}) {
            help.textContent = 'Nilai meter bulan lalu mengikuti input sebelumnya. Anda tetap dapat mengubah manual.';
        } else {
            autofillPreviousMeter();
        }
    })();
</script>
@endpush
