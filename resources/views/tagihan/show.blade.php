@extends('layouts.admin')
@section('title', 'Detail Tagihan')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Detail Tagihan Pelanggan'])
<div class="card">
<div class="grid-2">
<div><label>Pelanggan</label><div>{{ $tagihan->pelanggan?->name }}</div></div>
<div><label>Kode</label><div>{{ $tagihan->pelanggan?->kode_pelanggan ?? '-' }}</div></div>
<div><label>Periode</label><div>{{ $tagihan->period }}</div></div>
<div><label>Status</label><div>{{ ucfirst($tagihan->status) }}</div></div>
<div><label>Tarif Dasar</label><div>Rp {{ number_format($tagihan->base_amount, 0, ',', '.') }}</div></div>
<div><label>Tarif Pemakaian</label><div>Rp {{ number_format($tagihan->usage_amount, 0, ',', '.') }}</div></div>
<div><label>Selisih Meter</label><div>{{ number_format($tagihan->usage_m3, 0, ',', '.') }} m³</div></div>
<div><label>Denda</label><div>Rp {{ number_format($tagihan->late_fee, 0, ',', '.') }}</div></div>
<div><label>Total Tagihan</label><div>Rp {{ number_format($tagihan->amount, 0, ',', '.') }}</div></div>
<div><label>Total Dibayar</label><div>Rp {{ number_format($totalPaid, 0, ',', '.') }}</div></div>
<div><label>Sisa</label><div>Rp {{ number_format(max(0, $tagihan->amount - $totalPaid), 0, ',', '.') }}</div></div>
<div><label>Jatuh Tempo</label><div>{{ $tagihan->due_date?->format('Y-m-d') ?? '-' }}</div></div>
</div>
</div>
<div class="card table-wrap">
<h3 style="margin-top:0;">Riwayat Pembayaran</h3>
<table><thead><tr><th>Tanggal</th><th>Nominal</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody>
@forelse($tagihan->pembayarans as $bayar)
<tr><td>{{ $bayar->paid_at?->format('Y-m-d') }}</td><td>Rp {{ number_format($bayar->amount, 0, ',', '.') }}</td><td>{{ $bayar->petugas?->name ?? '-' }}</td><td>{{ $bayar->notes ?? '-' }}</td></tr>
@empty <tr><td colspan="4">@include('layouts.partials.empty-state', ['message' => 'Belum ada pembayaran untuk tagihan ini.'])</td></tr> @endforelse
</tbody></table>
</div>
<div style="display:flex;gap:8px;"><a href="{{ route('tagihan.index') }}" class="btn btn-outline">Kembali</a><a href="{{ route('tagihan.print', $tagihan) }}" class="btn btn-primary" target="_blank">Cetak Billing</a></div>
@endsection
