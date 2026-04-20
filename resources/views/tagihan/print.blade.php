<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Billing {{ $tagihan->pelanggan?->name }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#111;margin:24px} .doc{max-width:900px;margin:auto} .kop{text-align:center;border-bottom:3px double #000;padding-bottom:12px;margin-bottom:16px}
        .kop h2,.kop h3,.kop p{margin:2px 0} .meta{width:100%;border-collapse:collapse;margin-bottom:12px} .meta td{padding:4px 0;vertical-align:top}
        table.grid{width:100%;border-collapse:collapse;margin-top:8px} table.grid th,table.grid td{border:1px solid #000;padding:8px}
        .text-right{text-align:right}.signature{margin-top:36px;display:grid;grid-template-columns:1fr 1fr;gap:40px}.signature .box{text-align:center}.space{height:64px}
        @media print {.no-print{display:none} body{margin:0.5cm}}
    </style>
</head>
<body>
<div class="doc">
    <div class="no-print" style="text-align:right;margin-bottom:8px;"><button onclick="window.print()">Print / Save PDF</button></div>
    <div class="kop">
        <h2>{{ $setting?->nama_unit_pengelola ?: 'Unit Pengelola Air Bersih Desa' }}</h2>
        <h3>KECAMATAN {{ strtoupper($setting?->nama_kecamatan ?: ($tagihan->pelanggan?->desa?->kecamatan?->name ?? '-')) }}</h3>
        <p>Desa {{ $tagihan->pelanggan?->desa?->name ?? '-' }}</p>
        <p>{{ $setting?->alamat ?: '-' }} | {{ $setting?->kontak ?: '-' }}</p>
    </div>

    <h3 style="text-align:center;margin:4px 0 16px;">BILLING TAGIHAN AIR BERSIH</h3>

    <table class="meta">
        <tr><td width="180">Nama Pelanggan</td><td>: {{ $tagihan->pelanggan?->name ?? '-' }}</td><td width="180">Kode Pelanggan</td><td>: {{ $tagihan->pelanggan?->kode_pelanggan ?? '-' }}</td></tr>
        <tr><td>Periode</td><td>: {{ $tagihan->period }}</td><td>Jatuh Tempo</td><td>: {{ $tagihan->due_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td colspan="3">: {{ $tagihan->pelanggan?->address ?? '-' }}</td></tr>
    </table>

    <table class="grid">
        <thead><tr><th>Rincian</th><th>Nilai</th><th class="text-right">Nominal</th></tr></thead>
        <tbody>
            <tr><td>Abonemen</td><td>-</td><td class="text-right">Rp {{ number_format((float) ($tagihan->tarif?->abonemen ?? 0), 0, ',', '.') }}</td></tr>
            <tr><td>Tarif Dasar</td><td>-</td><td class="text-right">Rp {{ number_format((float) ($tagihan->tarif?->tarif_dasar ?? 0), 0, ',', '.') }}</td></tr>
            <tr><td>Pemakaian</td><td>{{ number_format((float) $tagihan->usage_m3, 0, ',', '.') }} m³</td><td class="text-right">Rp {{ number_format((float) $tagihan->usage_amount, 0, ',', '.') }}</td></tr>
            <tr><td>Tarif per m³</td><td>{{ number_format((float) ($tagihan->tarif?->tarif_per_m3 ?? 0), 0, ',', '.') }}</td><td class="text-right">-</td></tr>
            <tr><td>Denda</td><td>-</td><td class="text-right">Rp {{ number_format((float) $tagihan->late_fee, 0, ',', '.') }}</td></tr>
            @if($tagihan->meterRecord)
                <tr><td>Meter Awal</td><td colspan="2">{{ number_format((float) $tagihan->meterRecord->meter_previous_month, 0, ',', '.') }}</td></tr>
                <tr><td>Meter Akhir</td><td colspan="2">{{ number_format((float) $tagihan->meterRecord->meter_current_month, 0, ',', '.') }}</td></tr>
                <tr><td>Total Pemakaian</td><td colspan="2">{{ number_format((float) $tagihan->usage_m3, 0, ',', '.') }} m³</td></tr>
            @endif
            <tr><th colspan="2" class="text-right">TOTAL</th><th class="text-right">Rp {{ number_format((float) $tagihan->amount, 0, ',', '.') }}</th></tr>
        </tbody>
    </table>

    <div class="signature">
        <div class="box">
            <div>{{ $tagihan->pelanggan?->desa?->name ?? '-' }}, {{ $printedAt->locale('id')->translatedFormat('d F Y') }}</div>
            <div>Bendahara</div>
            <div class="space"></div>
            <div><strong>{{ $setting?->nama_bendahara ?: '(...................................)' }}</strong></div>
        </div>
        <div class="box">
            <div>&nbsp;</div>
            <div>Ketua / Direktur</div>
            <div class="space"></div>
            <div><strong>{{ $setting?->nama_ketua_direktur ?: '(...................................)' }}</strong></div>
        </div>
    </div>
</div>
</body>
</html>
