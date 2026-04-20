@extends('layouts.admin')
@section('title', 'Pembayaran Kecamatan (Per Desa)')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Pembayaran Kecamatan (Per Desa)', 'subtitle' => 'Pencatatan pembayaran/setoran desa ke kecamatan.'])
@include('layouts.partials.alerts')

<div class="card">
    <form method="GET" action="{{ route('district-billings.payments') }}" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
        <div><label>Periode</label><input type="month" name="period" value="{{ $period }}"></div>
        <button class="btn btn-outline" type="submit">Lihat</button>
        <a href="{{ route('district-billings.index', ['period' => $period]) }}" class="btn btn-outline">Lihat Tagihan Kecamatan</a>
    </form>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Desa</th><th>Periode</th><th>Total Tagihan</th><th>Nominal Setoran</th><th>Tanggal Bayar</th><th>Metode</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($billings as $item)
            <tr>
                <td>{{ $item->desa?->name }}</td>
                <td>{{ $item->period }}</td>
                <td>Rp {{ number_format($item->total_setoran, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->paid_amount, 0, ',', '.') }}</td>
                <td>{{ $item->paid_at?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ $item->payment_method ? str($item->payment_method)->replace('_', ' ')->title() : '-' }}</td>
                <td>{{ str($item->payment_status)->replace('_', ' ')->title() }}</td>
                <td>
                    @if($canRecordPayment && $item->payment_status !== 'lunas')
                        <form method="POST" action="{{ route('district-billings.record-payment', $item) }}" style="display:grid;gap:6px;min-width:220px;">
                            @csrf
                            <input type="number" step="0.01" min="1" name="amount" placeholder="Nominal setoran" required>
                            <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required>
                            <select name="payment_method"><option value="transfer_bank">Transfer Bank</option><option value="tunai">Tunai</option><option value="lainnya">Lainnya</option></select>
                            <button class="btn btn-primary btn-sm" type="submit">Catat Setoran</button>
                        </form>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Belum ada data pembayaran desa pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
