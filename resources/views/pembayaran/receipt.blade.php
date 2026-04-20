<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran #{{ $pembayaran->id }}</title>
    <style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; color: #111827; margin: 20px; }
        .doc { max-width: 860px; margin: 0 auto; }
        .kop { text-align: center; border-bottom: 3px double #111827; padding-bottom: 10px; margin-bottom: 16px; }
        .kop h2, .kop h3, .kop p { margin: 2px 0; }
        .title { text-align: center; font-weight: 700; font-size: 18px; margin-bottom: 14px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td { border: 1px solid #374151; padding: 8px; vertical-align: top; font-size: 12px; }
        .meta .label { width: 30%; background: #f9fafb; font-weight: 600; }
        .note-box { border: 1px solid #374151; padding: 10px; font-size: 12px; min-height: 56px; }
        .print-tools { text-align: right; margin-bottom: 10px; }
        .signature-date { margin-top: 24px; font-size: 12px; }
        .signature { margin-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .sign-box { text-align: center; font-size: 12px; }
        .sign-space { height: 70px; }
        .sign-name { font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
        @media print {
            .print-tools { display: none; }
            body { margin: 0.5cm; }
        }
    </style>
</head>
<body>
@php
    $pelanggan = $pembayaran->tagihan?->pelanggan;
    $desaName = $pelanggan?->desa?->name ? 'Desa '.$pelanggan->desa->name : 'Desa';
    $kecamatanName = $setting?->nama_kecamatan ?: ($pelanggan?->desa?->kecamatan?->name ?? '-');
@endphp
<div class="doc">
    <div class="print-tools"><button onclick="window.print()">Cetak Bukti</button></div>

    <div class="kop">
        <h2>{{ $setting?->nama_unit_pengelola ?: 'Unit Pengelola Air Bersih Desa' }}</h2>
        <h3>KECAMATAN {{ strtoupper($kecamatanName) }}</h3>
        <p>{{ $setting?->alamat ?: '-' }}</p>
    </div>

    <div class="title">BUKTI PEMBAYARAN</div>

    <table class="meta">
        <tr>
            <td class="label">ID Pembayaran / No Bukti</td>
            <td>PAY-{{ str_pad((string) $pembayaran->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pelanggan</td>
            <td>{{ $pelanggan?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kode Pelanggan</td>
            <td>{{ $pelanggan?->kode_pelanggan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Periode Tagihan</td>
            <td>{{ $pembayaran->tagihan?->period ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td>{{ str($pembayaran->payment_method)->replace('_', ' ')->title() }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Bayar</td>
            <td>Rp {{ number_format((float) $pembayaran->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Bayar</td>
            <td>{{ $pembayaran->paid_at?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Petugas Penagih</td>
            <td>{{ $pembayaran->petugas?->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="note-box">
        <strong>Catatan:</strong><br>
        {{ $pembayaran->notes ?: 'Tidak ada catatan tambahan.' }}
    </div>

    <div class="signature-date">{{ $desaName }}, {{ $printedAt->locale('id')->translatedFormat('d F Y') }}</div>
    <div class="signature">
        <div class="sign-box">
            <div>Ketua / Direktur</div>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $setting?->nama_ketua_direktur ?: '(...................................)' }}</div>
        </div>
        <div class="sign-box">
            <div>Bendahara</div>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $setting?->nama_bendahara ?: '(...................................)' }}</div>
        </div>
    </div>
</div>
</body>
</html>
