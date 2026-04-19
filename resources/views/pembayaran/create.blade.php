@extends('layouts.admin')
@section('title', 'Tambah Pembayaran')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Catat Pembayaran'])
@include('layouts.partials.alerts')
<div class="card" style="max-width:760px;">
<form method="POST" action="{{ route('pembayaran.store') }}" enctype="multipart/form-data">
@csrf
<div style="margin-bottom:14px;"><label>Pilih Tagihan</label><select name="tagihan_id" required><option value="">-- Pilih Tagihan --</option>@foreach($tagihans as $tagihan)<option value="{{ $tagihan->id }}" @selected(old('tagihan_id') == $tagihan->id)>#{{ $tagihan->id }} - {{ $tagihan->pelanggan?->name ?? 'Pelanggan' }} - Rp {{ number_format($tagihan->amount, 0, ',', '.') }} - {{ ucfirst($tagihan->status) }}</option>@endforeach</select></div>
<div style="margin-bottom:14px;"><label>Metode Pembayaran</label><select name="payment_method" required><option value="">-- Pilih --</option>@foreach($paymentMethods as $method)<option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ str($method)->replace('_', ' ')->title() }}</option>@endforeach</select></div>
<div style="margin-bottom:14px;"><label>Nominal</label><input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required></div>
<div style="margin-bottom:14px;"><label>Tanggal Bayar</label><input type="date" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" required></div>
<div style="margin-bottom:14px;"><label>Bukti Bayar (opsional)</label><input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp"></div>
<div style="margin-bottom:14px;"><label>Catatan</label><textarea name="notes">{{ old('notes') }}</textarea></div>
<div style="display:flex;gap:8px;"><button class="btn btn-primary" type="submit">Simpan Pembayaran</button><a href="{{ route('pembayaran.index') }}" class="btn btn-outline">Batal</a></div>
</form>
</div>
@endsection
