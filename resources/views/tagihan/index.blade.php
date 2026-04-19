@extends('layouts.admin')

@section('title', 'Tagihan')

@section('content')
@include('layouts.partials.page-header', ['title' => 'Billing / Tagihan', 'subtitle' => 'Generate dan monitor status tagihan bulanan.'])
@include('layouts.partials.alerts')

<div class="card">
    <form method="POST" action="{{ route('tagihan.generate') }}" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
        @csrf
        <div>
            <label>Periode Tagihan</label>
            <input type="month" name="period" value="{{ $selectedPeriod }}" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-lightning"></i> Generate Tagihan Bulanan</button>
    </form>
</div>

<div class="card table-wrap">
    <h3 style="margin-top:0;">Tabel Tarif</h3>
    <table>
        <thead><tr><th>Nama Tarif</th><th>Jenis</th><th>Tarif Dasar</th><th>Pemakaian / m³</th><th>Denda / hari</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($tarifs as $tarif)
            <tr>
                <td>{{ $tarif->name }}</td><td>{{ $tarif->customer_type ?? 'Semua jenis' }}</td>
                <td>Rp {{ number_format($tarif->base_rate, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->usage_rate, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tarif->late_fee_per_day, 0, ',', '.') }}</td>
                <td><span class="badge {{ $tarif->is_active ? 'badge-success' : 'badge-danger' }}">{{ $tarif->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6">@include('layouts.partials.empty-state', ['message' => 'Belum ada data tarif.'])</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card table-wrap">
    <h3 style="margin-top:0;">Daftar Tagihan</h3>
    <table>
        <thead><tr><th>Pelanggan</th><th>Periode</th><th>Pemakaian</th><th>Rincian</th><th>Total</th><th>Status</th><th>Jatuh Tempo</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse($tagihans as $tagihan)
            <tr>
                <td>{{ $tagihan->pelanggan?->name ?? '-' }}</td>
                <td>{{ $tagihan->period }}</td>
                <td>{{ number_format($tagihan->usage_m3, 0, ',', '.') }} m³</td>
                <td>Dasar: Rp {{ number_format($tagihan->base_amount, 0, ',', '.') }}<br>Pakai: Rp {{ number_format($tagihan->usage_amount, 0, ',', '.') }}<br>Denda: Rp {{ number_format($tagihan->late_fee, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($tagihan->amount, 0, ',', '.') }}</td>
                <td>
                    @php $badge = $tagihan->status === 'lunas' ? 'badge-success' : ($tagihan->status === 'menunggak' ? 'badge-danger' : ($tagihan->status === 'terbit' ? 'badge-info' : 'badge-warning')); @endphp
                    <span class="badge {{ $badge }}">{{ ucfirst($tagihan->status) }}</span>
                </td>
                <td>{{ $tagihan->due_date?->format('Y-m-d') ?? '-' }}</td>
                <td>
                    <a href="{{ route('tagihan.show', $tagihan) }}" class="btn btn-outline btn-sm">Detail</a>
                    @if($tagihan->status === 'draft')
                        <form method="POST" action="{{ route('tagihan.publish', $tagihan) }}" style="margin-top:6px;">@csrf <button type="submit" class="btn btn-primary btn-sm">Terbitkan</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8">@include('layouts.partials.empty-state', ['message' => 'Belum ada tagihan.'])</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
