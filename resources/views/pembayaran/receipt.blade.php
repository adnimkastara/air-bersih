<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;} .receipt{max-width:700px;background:#fff;padding:24px;border:1px solid #e2e8f0;border-radius:12px;} h1{margin-top:0;} .field{margin-bottom:12px;} .field strong{display:block;color:#334155;} .button{display:inline-flex;padding:10px 16px;border-radius:10px;background:#0f172a;color:#fff;text-decoration:none;} .proof{margin-top:16px;}</style>
</head>
<body>
    <div class="receipt">
        <h1>Bukti Pembayaran</h1>
        <div class="field"><strong>ID Pembayaran</strong>{{ $pembayaran->id }}</div>
        <div class="field"><strong>Tagihan</strong>#{{ $pembayaran->tagihan?->id ?? '-' }}</div>
        <div class="field"><strong>Pelanggan</strong>{{ $pembayaran->tagihan?->pelanggan?->name ?? '-' }}</div>
        <div class="field"><strong>Metode Pembayaran</strong>{{ str($pembayaran->payment_method)->replace('_', ' ')->title() }}</div>
        <div class="field"><strong>Jumlah Bayar</strong>Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</div>
        <div class="field"><strong>Tanggal Bayar</strong>{{ $pembayaran->paid_at->format('Y-m-d') }}</div>
        <div class="field"><strong>Petugas</strong>{{ $pembayaran->petugas?->name ?? '-' }}</div>
        <div class="field"><strong>Catatan</strong>{{ $pembayaran->notes ?? '-' }}</div>

        @if($pembayaran->proof_path)
            <div class="proof">
                <strong>Bukti Bayar</strong>
                <p><a href="{{ asset('storage/' . $pembayaran->proof_path) }}" target="_blank">Lihat File Bukti Bayar</a></p>
            </div>
        @endif

        <div style="margin-top:24px;">
            <a href="{{ route('pembayaran.index') }}" class="button">Kembali</a>
            <a href="javascript:window.print()" class="button" style="margin-left:12px;background:#2563eb;">Cetak</a>
        </div>
    </div>
</body>
</html>
