@extends('layouts.admin')
@section('title', 'Pencatatan Meter')
@section('content')
@include('layouts.partials.page-header', ['title' => 'Pencatatan Meter', 'actions' => '<a class="btn btn-primary" href="'.route('meter_records.create').'">Tambah Catatan</a>'])
@include('layouts.partials.alerts')
<div class="card table-wrap">
<table>
<thead><tr><th>Pelanggan</th><th>Meter Lalu</th><th>Meter Kini</th><th>Konsumsi</th><th>Tanggal</th><th>Petugas</th><th>Foto</th><th>Verifikasi</th><th>Catatan</th></tr></thead>
<tbody>
@forelse($meterRecords as $record)
<tr>
<td>{{ $record->pelanggan?->kode_pelanggan ?? '-' }}<br>{{ $record->pelanggan?->name ?? '-' }}</td>
<td>{{ number_format($record->meter_previous_month) }}</td>
<td>{{ number_format($record->meter_current_month) }}</td>
<td>{{ number_format($record->meter_current_month - $record->meter_previous_month) }} @if($record->is_anomaly)<div class="badge badge-danger">Anomali</div>@endif</td>
<td>{{ $record->recorded_at?->format('Y-m-d') }}</td>
<td>{{ $record->petugas?->name ?? '-' }}</td>
<td>@if($record->meter_photo_path)<a href="{{ asset('storage/'.$record->meter_photo_path) }}" target="_blank" class="btn btn-outline btn-sm">Lihat</a>@else - @endif</td>
<td><span class="badge {{ $record->verification_status === 'terverifikasi' ? 'badge-success' : ($record->verification_status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">{{ ucfirst($record->verification_status) }}</span></td>
<td>{{ $record->notes ?? '-' }}</td>
</tr>
@empty
<tr><td colspan="9">@include('layouts.partials.empty-state', ['message' => 'Belum ada data pencatatan meter.'])</td></tr>
@endforelse
</tbody></table>
</div>
<div>{{ $meterRecords->links() }}</div>
@endsection
