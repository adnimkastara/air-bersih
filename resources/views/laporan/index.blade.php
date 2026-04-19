@extends('layouts.admin')
@section('title', 'Laporan')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Modul Laporan', 'subtitle' => 'Filter data dan export laporan.'])
<div class="card">
<form method="GET" action="{{ route('laporan.index') }}" class="toolbar">
<div><label>Dari Tanggal</label><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
<div><label>Sampai Tanggal</label><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
<div><label>Desa</label><select name="desa_id"><option value="">Semua Desa</option>@foreach($desas as $desa)<option value="{{ $desa->id }}" @selected(($filters['desa_id'] ?? null) == $desa->id)>{{ $desa->name }}</option>@endforeach</select></div>
<div style="display:flex;gap:8px;"><button type="submit" class="btn btn-primary">Terapkan</button><a href="{{ route('laporan.index') }}" class="btn btn-outline">Reset</a></div>
</form>
<p class="muted">Laporan tersedia: pelanggan, tagihan, pembayaran, tunggakan, gangguan, keuangan.</p>
</div>

@php $titles = ['pelanggan' => 'Laporan Pelanggan','tagihan' => 'Laporan Tagihan','pembayaran' => 'Laporan Pembayaran','tunggakan' => 'Laporan Tunggakan','gangguan' => 'Laporan Gangguan','keuangan' => 'Laporan Keuangan Sederhana']; @endphp
@foreach($titles as $key => $title)
<div class="card">
<div class="page-header" style="margin-bottom:10px;"><div><h3 style="margin:0;">{{ $title }}</h3></div><div style="display:flex;gap:8px;"><a class="btn btn-outline btn-sm" href="{{ route('laporan.export.pdf', array_merge($filters, ['report' => $key])) }}">Export PDF</a><a class="btn btn-primary btn-sm" href="{{ route('laporan.export.excel', array_merge($filters, ['report' => $key])) }}">Export Excel</a></div></div>
<div class="table-wrap"><table><thead><tr>@forelse(array_keys($reports[$key][0] ?? ['data' => 'Tidak ada data']) as $column)<th>{{ str($column)->replace('_', ' ')->title() }}</th>@empty<th>Data</th>@endforelse</tr></thead><tbody>
@forelse($reports[$key] as $row)<tr>@foreach($row as $column => $value)<td>@if(is_numeric($value) && (str_contains($column, 'jumlah') || str_contains($column, 'total') || $column === 'selisih'))Rp {{ number_format((float) $value, 0, ',', '.') }}@else{{ $value }}@endif</td>@endforeach</tr>@empty<tr><td colspan="20">@include('layouts.partials.empty-state', ['message' => 'Tidak ada data.'])</td></tr>@endforelse
</tbody></table></div>
</div>
@endforeach
@endsection
