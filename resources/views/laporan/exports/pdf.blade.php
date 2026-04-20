<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; color: #1f2937; margin: 20px; }
        .doc { max-width: 1100px; margin: 0 auto; }
        .kop { text-align: center; border-bottom: 3px double #111827; padding-bottom: 10px; margin-bottom: 14px; }
        .kop h2, .kop h3, .kop p { margin: 2px 0; }
        .kop h2 { font-size: 20px; letter-spacing: .3px; }
        .kop h3 { font-size: 15px; font-weight: 600; }
        .kop p { font-size: 12px; color: #374151; }
        .report-title { text-align: center; margin: 10px 0 12px; font-size: 16px; font-weight: 700; text-transform: uppercase; }
        .meta-box { border: 1px solid #d1d5db; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; font-size: 12px; }
        .meta-grid { width: 100%; border-collapse: collapse; }
        .meta-grid td { border: none; padding: 3px 4px; vertical-align: top; }
        .meta-label { width: 150px; color: #4b5563; }
        table.report-table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { border: 1px solid #374151; padding: 6px 8px; font-size: 11.5px; vertical-align: top; }
        .report-table th { background: #f3f4f6; text-align: center; font-weight: 700; }
        .report-table td.text-right { text-align: right; }
        .empty-row { text-align: center; color: #4b5563; padding: 10px 8px; }
        .signature { margin-top: 34px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .sign-box { text-align: center; font-size: 12px; }
        .sign-role { margin-top: 4px; }
        .sign-space { height: 72px; }
        .sign-name { font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
        .no-print { text-align: right; margin-bottom: 8px; }
        @media print { .no-print { display: none; } body { margin: 0.4cm; } }
    </style>
</head>
<body>
<div class="doc">
    <div class="no-print"><button onclick="window.print()">Print / Save PDF</button></div>
    <div class="kop">
        <h2>{{ $setting?->nama_unit_pengelola ?: 'Unit Pengelola Air Bersih Desa' }}</h2>
        <h3>KECAMATAN {{ strtoupper($setting?->nama_kecamatan ?: '-') }}</h3>
        <p>{{ $setting?->alamat ?: '-' }} | {{ $setting?->kontak ?: '-' }}</p>
    </div>

    @php
        $meta = $exportMeta ?? [];
        $reportLabel = data_get($meta, 'report_label', $title);
        $reportTypeLabel = data_get($meta, 'report_type_label', str($report ?? 'laporan')->replace('_', ' ')->title());
        $periodLabel = data_get($meta, 'period_label', 'Semua Periode');
        $desaFilterLabel = data_get($meta, 'desa_label', 'Semua Desa');
        $printedAtLabel = $printedAt->locale('id')->translatedFormat('d F Y H:i');
        $signedAtLabel = $printedAt->locale('id')->translatedFormat('d F Y');
        $signLocation = $setting?->desa?->name
            ?? ($setting?->nama_kecamatan ? 'Kecamatan '.$setting->nama_kecamatan : $desaFilterLabel);
        $bendaharaName = trim((string) ($setting?->nama_bendahara ?? '')) ?: '(...................................)';
        $ketuaName = trim((string) ($setting?->nama_ketua_direktur ?? '')) ?: '(...................................)';

        $currencyColumns = ['jumlah', 'total', 'selisih', 'amount', 'abonemen', 'tarif_dasar', 'tarif_per_m3', 'jumlah_tunggakan', 'total_tagihan', 'total_pembayaran', 'total_tunggakan', 'total_setoran', 'sisa_tunggakan'];
        $dateColumns = ['tanggal', 'jatuh_tempo', 'dibuat_pada', 'tanggal_bayar', 'dilaporkan_pada', 'reported_at', 'due_date', 'created_at', 'updated_at'];
        $headers = array_keys($rows[0] ?? ['keterangan' => 'Tidak ada data']);
        $columnCount = count($headers);
    @endphp

    <div class="report-title">{{ $reportLabel }}</div>

    <div class="meta-box">
        <table class="meta-grid">
            <tr>
                <td class="meta-label">Jenis Laporan</td>
                <td>: {{ $reportTypeLabel }}</td>
            </tr>
            <tr>
                <td class="meta-label">Periode</td>
                <td>: {{ $periodLabel }}</td>
            </tr>
            <tr>
                <td class="meta-label">Filter Desa</td>
                <td>: {{ $desaFilterLabel }}</td>
            </tr>
            <tr>
                <td class="meta-label">Tanggal Cetak</td>
                <td>: {{ $printedAtLabel }}</td>
            </tr>
        </table>
    </div>

    <table class="report-table">
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
                            $normalizedColumn = strtolower((string) $column);
                            $isCurrency = collect($currencyColumns)->contains(fn ($key) => str_contains($normalizedColumn, $key));
                            $isDate = collect($dateColumns)->contains(fn ($key) => str_contains($normalizedColumn, $key));
                        @endphp
                        <td class="{{ $isCurrency ? 'text-right' : '' }}">
                            @if($isCurrency && is_numeric($value))
                                Rp {{ number_format((float) $value, 0, ',', '.') }}
                            @elseif($isDate && !empty($value))
                                @php
                                    $dateValue = \Illuminate\Support\Carbon::parse($value);
                                    $dateFormat = str_contains((string) $value, ':')
                                        ? 'd F Y H:i'
                                        : 'd F Y';
                                @endphp
                                {{ $dateValue->locale('id')->translatedFormat($dateFormat) }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $columnCount }}" class="empty-row">
                        Tidak ada data yang dapat ditampilkan untuk filter laporan saat ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <div class="sign-box">
            <div>{{ $signLocation }}, {{ $signedAtLabel }}</div>
            <div class="sign-role">Bendahara</div>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $bendaharaName }}</div>
        </div>
        <div class="sign-box">
            <div>&nbsp;</div>
            <div class="sign-role">Ketua / Direktur</div>
            <div class="sign-space"></div>
            <div class="sign-name">{{ $ketuaName }}</div>
        </div>
    </div>
</div>
</body>
</html>
