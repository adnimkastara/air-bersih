<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;}th{background:#f1f5f9;text-align:left;}a,button{padding:10px 14px;border-radius:8px;text-decoration:none;border:none;cursor:pointer;}a{background:#0f172a;color:#fff;}button{background:#2563eb;color:#fff;} .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}</style>
</head>
<body>
    <div class="top">
        <h1>Pembayaran</h1>
        <a href="{{ route('pembayaran.create') }}">Catat Pembayaran</a>
    </div>

    @if(session('status'))
        <div style="margin-bottom:16px;padding:14px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">{{ session('status') }}</div>
    @endif

    <table>
        <thead>
            <tr><th>Tagihan</th><th>Pelanggan</th><th>Jumlah</th><th>Tanggal Bayar</th><th>Petugas</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @foreach($pembayarans as $pembayaran)
                <tr>
                    <td>{{ $pembayaran->tagihan?->id ?? '-' }}</td>
                    <td>{{ $pembayaran->tagihan?->pelanggan?->name ?? '-' }}</td>
                    <td>Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</td>
                    <td>{{ $pembayaran->paid_at->format('Y-m-d') }}</td>
                    <td>{{ $pembayaran->petugas?->name ?? '-' }}</td>
                    <td><a href="{{ route('pembayaran.receipt', $pembayaran) }}">Cetak Bukti</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
