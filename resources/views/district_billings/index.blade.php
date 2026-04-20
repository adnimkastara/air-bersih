@extends('layouts.admin')
@section('title', 'Tagihan Kecamatan (Per Desa)')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Tagihan Kecamatan (Per Desa)', 'subtitle' => 'Tagihan desa ke kecamatan dihitung dari total pemakaian desa x tarif kecamatan.'])
@include('layouts.partials.alerts')

<div class="card">
    <div style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
        <form method="GET" action="{{ route('district-billings.index') }}" style="display:flex;gap:10px;align-items:end;">
            <div><label>Periode</label><input type="month" name="period" value="{{ $period }}"></div>
            <button class="btn btn-outline" type="submit">Lihat</button>
        </form>
        @if($canGenerate)
            <form method="POST" action="{{ route('district-billings.generate') }}">@csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <button class="btn btn-primary" type="submit">Generate Tagihan Desa</button>
            </form>
        @endif
    </div>
    <div style="margin-top:10px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">
        <div class="card" style="margin:0;"><small>Total Tagihan Desa</small><strong>Rp {{ number_format($totalTagihanDesa, 0, ',', '.') }}</strong></div>
        <div class="card" style="margin:0;"><small>Total Pembayaran Desa</small><strong>Rp {{ number_format($totalPembayaranDesa, 0, ',', '.') }}</strong></div>
        <div class="card" style="margin:0;"><small>Desa Lunas</small><strong>{{ $desaLunas }}</strong></div>
        <div class="card" style="margin:0;"><small>Desa Belum Lunas</small><strong>{{ $desaMenunggak }}</strong></div>
    </div>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Desa</th><th>Periode</th><th>Total Pemakaian</th><th>Tarif Kec.</th><th>Total Tagihan Desa</th><th>Status</th><th>Jatuh Tempo</th></tr></thead>
        <tbody>
        @forelse($billings as $item)
            <tr>
                <td>{{ $item->desa?->name }}</td>
                <td>{{ $item->period }}</td>
                <td>{{ number_format($item->total_usage_m3, 0, ',', '.') }} m³</td>
                <td>Rp {{ number_format($item->tarif_per_m3, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->total_setoran, 0, ',', '.') }}</td>
                <td>{{ str($item->payment_status)->replace('_', ' ')->title() }}</td>
                <td>{{ $item->due_date?->format('Y-m-d') ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada tagihan desa pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
