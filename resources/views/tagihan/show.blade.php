<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tagihan</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;max-width:900px;}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
        .row{padding:8px 0;border-bottom:1px dashed #e2e8f0;}
        .muted{color:#475569;}
        table{width:100%;border-collapse:collapse;margin-top:14px;}
        th,td{border-bottom:1px solid #e2e8f0;padding:10px;text-align:left;}
        th{background:#f1f5f9;}
        a{padding:10px 14px;background:#0f172a;color:white;text-decoration:none;border-radius:8px;display:inline-block;margin-top:14px;}
    </style>
</head>
<body>
    <div class="card">
        <h1>Detail Tagihan Pelanggan</h1>
        <div class="grid">
            <div class="row"><strong>Pelanggan:</strong> {{ $tagihan->pelanggan?->name }}</div>
            <div class="row"><strong>Kode:</strong> {{ $tagihan->pelanggan?->kode_pelanggan ?? '-' }}</div>
            <div class="row"><strong>Periode:</strong> {{ $tagihan->period }}</div>
            <div class="row"><strong>Status:</strong> {{ ucfirst($tagihan->status) }}</div>
            <div class="row"><strong>Tarif Dasar:</strong> Rp {{ number_format($tagihan->base_amount, 0, ',', '.') }}</div>
            <div class="row"><strong>Tarif Pemakaian:</strong> Rp {{ number_format($tagihan->usage_amount, 0, ',', '.') }}</div>
            <div class="row"><strong>Selisih Meter:</strong> {{ number_format($tagihan->usage_m3, 0, ',', '.') }} m³</div>
            <div class="row"><strong>Denda Keterlambatan:</strong> Rp {{ number_format($tagihan->late_fee, 0, ',', '.') }}</div>
            <div class="row"><strong>Total Tagihan:</strong> Rp {{ number_format($tagihan->amount, 0, ',', '.') }}</div>
            <div class="row"><strong>Total Dibayar:</strong> Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
            <div class="row"><strong>Sisa:</strong> Rp {{ number_format(max(0, $tagihan->amount - $totalPaid), 0, ',', '.') }}</div>
            <div class="row"><strong>Jatuh Tempo:</strong> {{ $tagihan->due_date?->format('Y-m-d') ?? '-' }}</div>
        </div>

        <h2 style="margin-top:18px;">Riwayat Pembayaran</h2>
        <table>
            <thead>
            <tr><th>Tanggal Bayar</th><th>Nominal</th><th>Petugas</th><th>Catatan</th></tr>
            </thead>
            <tbody>
            @forelse($tagihan->pembayarans as $bayar)
                <tr>
                    <td>{{ $bayar->paid_at?->format('Y-m-d') }}</td>
                    <td>Rp {{ number_format($bayar->amount, 0, ',', '.') }}</td>
                    <td>{{ $bayar->petugas?->name ?? '-' }}</td>
                    <td class="muted">{{ $bayar->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada pembayaran untuk tagihan ini.</td></tr>
            @endforelse
            </tbody>
        </table>

        <a href="{{ route('tagihan.index') }}">Kembali ke Daftar Tagihan</a>
    </div>
</body>
</html>
