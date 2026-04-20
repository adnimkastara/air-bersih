<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tagihan {{ $tagihan->pelanggan?->name }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#111;margin:20px;font-size:13px} .doc{max-width:900px;margin:auto}
        .kop{text-align:center;border-bottom:3px double #000;padding-bottom:10px;margin-bottom:14px}
        .kop h2,.kop h3,.kop p{margin:2px 0}
        .subtle{font-size:12px;color:#333}
        .meta,.grid{width:100%;border-collapse:collapse;margin-top:8px}
        .meta td{border:1px solid #000;padding:6px;vertical-align:top}
        .grid th,.grid td{border:1px solid #000;padding:8px}
        .grid th{background:#f2f2f2}
        .text-right{text-align:right}
        .signature{margin-top:34px;display:grid;grid-template-columns:1fr 1fr;gap:40px}
        .signature-date{margin-top:28px;font-size:12px}
        .signature .box{text-align:center}.space{height:64px}
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
        <p>{{ $setting?->alamat ?: '-' }}</p>
        <p class="subtle">Identitas Pengelola: {{ $setting?->tipe_pengelola ?: 'Pengelola Air Bersih' }} | Ketua/Direktur: {{ $setting?->nama_ketua_direktur ?: '-' }} | Sekretaris: {{ $setting?->nama_sekretaris ?: '-' }} | Bendahara: {{ $setting?->nama_bendahara ?: '-' }}</p>
    </div>

    <h3 style="text-align:center;margin:4px 0 16px;">HASIL CETAK TAGIHAN AIR BERSIH</h3>

    <table class="meta">
        <tr><td width="22%"><strong>Nama Pelanggan</strong></td><td width="28%">{{ $tagihan->pelanggan?->name ?? '-' }}</td><td width="22%"><strong>Kode Pelanggan</strong></td><td width="28%">{{ $tagihan->pelanggan?->kode_pelanggan ?? '-' }}</td></tr>
        <tr><td><strong>Periode</strong></td><td>{{ $tagihan->period }}</td><td><strong>Jatuh Tempo</strong></td><td>{{ $tagihan->due_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td></tr>
        <tr><td><strong>Alamat</strong></td><td colspan="3">{{ $tagihan->pelanggan?->address ?? '-' }}</td></tr>
    </table>

    <table class="grid">
        <thead><tr><th width="42%">Rincian Tagihan</th><th width="23%">Volume/Keterangan</th><th width="35%" class="text-right">Nominal</th></tr></thead>
        <tbody>
            <tr><td>Abonemen</td><td>-</td><td class="text-right">Rp {{ number_format((float) ($tagihan->tarif?->abonemen ?? 0), 0, ',', '.') }}</td></tr>
            <tr><td>Tarif Dasar</td><td>-</td><td class="text-right">Rp {{ number_format((float) ($tagihan->tarif?->tarif_dasar ?? 0), 0, ',', '.') }}</td></tr>
            <tr><td>Pemakaian</td><td>{{ number_format((float) $tagihan->usage_m3, 0, ',', '.') }} m³</td><td class="text-right">Rp {{ number_format((float) $tagihan->usage_amount, 0, ',', '.') }}</td></tr>
            <tr><td>Denda</td><td>-</td><td class="text-right">Rp {{ number_format((float) $tagihan->late_fee, 0, ',', '.') }}</td></tr>
            @if($tagihan->meterRecord)
                <tr><td>Meter Awal</td><td colspan="2">{{ number_format((float) $tagihan->meterRecord->meter_previous_month, 0, ',', '.') }}</td></tr>
                <tr><td>Meter Akhir</td><td colspan="2">{{ number_format((float) $tagihan->meterRecord->meter_current_month, 0, ',', '.') }}</td></tr>
            @endif
            <tr><th colspan="2" class="text-right">TOTAL TAGIHAN</th><th class="text-right">Rp {{ number_format((float) $tagihan->amount, 0, ',', '.') }}</th></tr>
        </tbody>
    </table>

    <div class="signature-date">{{ $tagihan->pelanggan?->desa?->name ?? '-' }}, {{ $printedAt->locale('id')->translatedFormat('d F Y') }}</div>
    <div class="signature">
        <div class="box">
            <div>Ketua / Direktur</div>
            <div class="space"></div>
            <div><strong>{{ $setting?->nama_ketua_direktur ?: '(...................................)' }}</strong></div>
        </div>
        <div class="box">
            <div>Bendahara</div>
            <div class="space"></div>
            <div><strong>{{ $setting?->nama_bendahara ?: '(...................................)' }}</strong></div>
        </div>
    </div>
</div>
</body>
</html>
