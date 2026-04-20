@extends('layouts.admin')
@section('title', 'Setoran Desa ke Kecamatan')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Billing Setoran Desa → Kecamatan', 'subtitle' => 'Rekap pemakaian desa per periode dikalikan tarif kecamatan aktif.'])
@include('layouts.partials.alerts')

<div class="card">
    <form method="GET" action="{{ route('district-billings.index') }}" style="display:flex;gap:10px;align-items:end;">
        <div><label>Periode</label><input type="month" name="period" value="{{ $period }}"></div>
        <button class="btn btn-outline" type="submit">Lihat</button>
    </form>

    @if($canGenerate)
    <form method="POST" action="{{ route('district-billings.generate') }}" style="margin-top:10px;display:flex;gap:10px;align-items:end;">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <button class="btn btn-primary" type="submit">Generate Rekap Setoran</button>
    </form>
    @endif
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Desa</th><th>Periode</th><th>Total Pemakaian Desa</th><th>Tarif Kecamatan</th><th>Total Setoran</th><th>Status</th><th>Jatuh Tempo</th></tr></thead>
        <tbody>
        @forelse($billings as $item)
            <tr>
                <td>{{ $item->desa?->name }}</td>
                <td>{{ $item->period }}</td>
                <td>{{ number_format($item->total_usage_m3, 0, ',', '.') }} m³</td>
                <td>Rp {{ number_format($item->tarif_per_m3, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->total_setoran, 0, ',', '.') }}</td>
                <td>{{ ucfirst($item->status) }}</td>
                <td>{{ $item->due_date?->format('Y-m-d') ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Belum ada rekap setoran pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
