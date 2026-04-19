<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencatatan Meter</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;}th{background:#f1f5f9;text-align:left;}a,button{padding:10px 14px;border-radius:8px;text-decoration:none;border:none;cursor:pointer;}a{background:#0f172a;color:#fff;}button{background:#ef4444;color:#fff;} .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}</style>
</head>
<body>
    <div class="top">
        <h1>Pencatatan Meter</h1>
        <a href="{{ route('meter_records.create') }}">Tambah Catatan</a>
    </div>

    @if(session('status'))
        <div style="margin-bottom:16px;padding:14px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">{{ session('status') }}</div>
    @endif

    <table>
        <thead>
            <tr><th>Pelanggan</th><th>Meter Bulan Lalu</th><th>Meter Bulan Ini</th><th>Konsumsi</th><th>Petugas</th><th>Tanggal</th><th>Catatan</th></tr>
        </thead>
        <tbody>
            @foreach($meterRecords as $record)
                <tr>
                    <td>{{ $record->pelanggan?->name ?? '-' }}</td>
                    <td>{{ number_format($record->meter_previous_month) }}</td>
                    <td>{{ number_format($record->meter_current_month) }}</td>
                    <td>{{ number_format($record->meter_current_month - $record->meter_previous_month) }}</td>
                    <td>{{ $record->petugas?->name ?? '-' }}</td>
                    <td>{{ $record->recorded_at->format('Y-m-d') }}</td>
                    <td>{{ $record->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
