<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul Laporan</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #0f172a; }
        .wrap { max-width: 1200px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 12px; }
        input, select, button, a.btn { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; text-decoration: none; }
        button, a.btn { background: #1d4ed8; color: #fff; border: none; cursor: pointer; text-align: center; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; }
        .row-head { display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .actions { display:flex; gap:8px; }
        .muted { color: #64748b; font-size: 13px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Modul Laporan</h1>

    <div class="card">
        <form method="GET" action="{{ route('laporan.index') }}">
            <div class="grid">
                <div>
                    <label>Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div>
                    <label>Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div>
                    <label>Desa</label>
                    <select name="desa_id">
                        <option value="">Semua Desa</option>
                        @foreach($desas as $desa)
                            <option value="{{ $desa->id }}" @selected(($filters['desa_id'] ?? null) == $desa->id)>{{ $desa->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="align-self:end;">
                    <button type="submit">Terapkan Filter</button>
                </div>
            </div>
        </form>
        <p class="muted">Laporan tersedia: pelanggan, tagihan, pembayaran, tunggakan, gangguan, dan keuangan sederhana.</p>
    </div>

    @php
        $titles = [
            'pelanggan' => 'Laporan Pelanggan',
            'tagihan' => 'Laporan Tagihan',
            'pembayaran' => 'Laporan Pembayaran',
            'tunggakan' => 'Laporan Tunggakan',
            'gangguan' => 'Laporan Gangguan',
            'keuangan' => 'Laporan Keuangan Sederhana',
        ];
    @endphp

    @foreach($titles as $key => $title)
        <div class="card">
            <div class="row-head">
                <h2 style="margin:0;">{{ $title }}</h2>
                <div class="actions">
                    <a class="btn" href="{{ route('laporan.export.pdf', array_merge($filters, ['report' => $key])) }}">Export PDF</a>
                    <a class="btn" href="{{ route('laporan.export.excel', array_merge($filters, ['report' => $key])) }}">Export Excel</a>
                </div>
            </div>

            <table>
                <thead>
                <tr>
                    @forelse(array_keys($reports[$key][0] ?? ['data' => 'Tidak ada data']) as $column)
                        <th>{{ str($column)->replace('_', ' ')->title() }}</th>
                    @empty
                        <th>Data</th>
                    @endforelse
                </tr>
                </thead>
                <tbody>
                @forelse($reports[$key] as $row)
                    <tr>
                        @foreach($row as $column => $value)
                            <td>
                                @if(is_numeric($value) && (str_contains($column, 'jumlah') || str_contains($column, 'total') || $column === 'selisih'))
                                    Rp {{ number_format((float) $value, 0, ',', '.') }}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="20">Tidak ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <p><a class="btn" href="{{ route('dashboard') }}" style="width:auto;">Kembali ke Dashboard</a></p>
</div>
</body>
</html>
