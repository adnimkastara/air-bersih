<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#111;margin:24px} .doc{max-width:1100px;margin:auto}
        .kop{text-align:center;border-bottom:3px double #000;padding-bottom:12px;margin-bottom:16px}
        .kop h2,.kop h3,.kop p{margin:2px 0}
        .meta{margin-bottom:10px;font-size:13px}
        table{width:100%;border-collapse:collapse} th,td{border:1px solid #000;padding:6px 8px;font-size:12px;vertical-align:top} th{background:#f3f3f3}
        .text-right{text-align:right}.signature{margin-top:34px;display:grid;grid-template-columns:1fr 1fr;gap:40px}.box{text-align:center}.space{height:64px}
        @media print {.no-print{display:none} body{margin:0.5cm}}
    </style>
</head>
<body>
<div class="doc">
    <div class="no-print" style="text-align:right;margin-bottom:8px;"><button onclick="window.print()">Print / Save PDF</button></div>
    <div class="kop">
        <h2>{{ $setting?->nama_unit_pengelola ?: 'Unit Pengelola Air Bersih Desa' }}</h2>
        <h3>KECAMATAN {{ strtoupper($setting?->nama_kecamatan ?: '-') }}</h3>
        <p>{{ $setting?->alamat ?: '-' }} | {{ $setting?->kontak ?: '-' }}</p>
    </div>
    <h3 style="text-align:center;margin:4px 0 12px;">{{ strtoupper($title) }}</h3>
    <div class="meta">
        <div>Periode filter: {{ $filters['date_from'] ?? '-' }} s/d {{ $filters['date_to'] ?? '-' }}</div>
        <div>Filter desa: {{ $filters['desa_id'] ?? 'Semua Desa' }}</div>
        <div>Tanggal cetak: {{ $printedAt->locale('id')->translatedFormat('d F Y H:i') }}</div>
    </div>

    @php
        $currencyColumns = ['jumlah','total','selisih','amount','abonemen','tarif_dasar','tarif_per_m3','jumlah_tunggakan','total_tagihan','total_pembayaran','total_tunggakan','total_setoran'];
        $dateColumns = ['tanggal','jatuh_tempo','dibuat_pada','tanggal_bayar','dilaporkan_pada'];
        $headers = array_keys($rows[0] ?? ['data' => 'Tidak ada data']);
        $namaDesa = $setting?->desa?->name ?? ($filters['desa_id'] ? 'Desa ID '.$filters['desa_id'] : 'Semua Desa');
    @endphp

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ str($header)->replace('_', ' ')->title() }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $column => $value)
                        @php
                            $isCurrency = collect($currencyColumns)->contains(fn($key) => str_contains($column, $key));
                            $isDate = collect($dateColumns)->contains(fn($key) => str_contains($column, $key));
                        @endphp
                        <td class="{{ $isCurrency ? 'text-right' : '' }}">
                            @if($isCurrency && is_numeric($value))
                                Rp {{ number_format((float) $value, 0, ',', '.') }}
                            @elseif($isDate && !empty($value))
                                {{ \Illuminate\Support\Carbon::parse($value)->locale('id')->translatedFormat('d F Y H:i') }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="20">Tidak ada data untuk filter yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <div class="box">
            <div>{{ $namaDesa }}, {{ $printedAt->locale('id')->translatedFormat('d F Y') }}</div>
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
