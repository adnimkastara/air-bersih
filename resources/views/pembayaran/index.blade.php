@extends('layouts.admin')
@section('title', 'Pembayaran')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Pembayaran', 'actions' => '<a href="'.route('pembayaran.create').'" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Catat Pembayaran</a>'])
@include('layouts.partials.alerts')
<div class="card table-wrap">
<table>
<thead><tr><th>Tagihan</th><th>Pelanggan</th><th>Metode</th><th>Nominal</th><th>Tanggal</th><th>Bukti</th><th>Petugas</th><th>Aksi</th></tr></thead>
<tbody>
@forelse($pembayarans as $pembayaran)
<tr>
<td>{{ $pembayaran->tagihan?->id ?? '-' }}</td>
<td>{{ $pembayaran->tagihan?->pelanggan?->name ?? '-' }}</td>
<td>{{ str($pembayaran->payment_method)->replace('_', ' ')->title() }}</td>
<td>Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</td>
<td>{{ $pembayaran->paid_at->format('Y-m-d') }}</td>
<td>@if($pembayaran->proof_path)<a href="{{ asset('storage/' . $pembayaran->proof_path) }}" target="_blank" class="btn btn-outline btn-sm">Lihat</a>@else - @endif</td>
<td>{{ $pembayaran->petugas?->name ?? '-' }}</td>
<td><a href="{{ route('pembayaran.receipt', $pembayaran) }}" class="btn btn-primary btn-sm">Cetak</a></td>
</tr>
@empty
<tr><td colspan="8">@include('layouts.partials.empty-state', ['message' => 'Belum ada data pembayaran.'])</td></tr>
@endforelse
</tbody>
</table>
</div>
@endsection
