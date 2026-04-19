@extends('layouts.admin')
@section('title', 'Bukti Pembayaran')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Bukti Pembayaran'])
<div class="card" style="max-width:760px;">
<div class="grid-2">
<div><label>ID Pembayaran</label><div>{{ $pembayaran->id }}</div></div>
<div><label>Tagihan</label><div>#{{ $pembayaran->tagihan?->id ?? '-' }}</div></div>
<div><label>Pelanggan</label><div>{{ $pembayaran->tagihan?->pelanggan?->name ?? '-' }}</div></div>
<div><label>Metode</label><div>{{ str($pembayaran->payment_method)->replace('_', ' ')->title() }}</div></div>
<div><label>Jumlah Bayar</label><div>Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</div></div>
<div><label>Tanggal Bayar</label><div>{{ $pembayaran->paid_at->format('Y-m-d') }}</div></div>
<div><label>Petugas</label><div>{{ $pembayaran->petugas?->name ?? '-' }}</div></div>
<div><label>Catatan</label><div>{{ $pembayaran->notes ?? '-' }}</div></div>
</div>
@if($pembayaran->proof_path)
<div style="margin-top:12px;"><a href="{{ asset('storage/' . $pembayaran->proof_path) }}" class="btn btn-outline" target="_blank">Lihat Bukti Bayar</a></div>
@endif
<div style="margin-top:16px;display:flex;gap:8px;"><a href="{{ route('pembayaran.index') }}" class="btn btn-outline">Kembali</a><a href="javascript:window.print()" class="btn btn-primary">Cetak</a></div>
</div>
@endsection
