<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencatatan Meter</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}
        table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;}
        th,td{padding:12px;border-bottom:1px solid #e2e8f0;vertical-align:top;}
        th{background:#f1f5f9;text-align:left;}
        a{padding:10px 14px;border-radius:8px;text-decoration:none;border:none;cursor:pointer;background:#0f172a;color:#fff;display:inline-block;}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
        .badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;}
        .pending{background:#fef3c7;color:#92400e;}
        .terverifikasi{background:#dcfce7;color:#166534;}
        .ditolak{background:#fee2e2;color:#991b1b;}
        .anomali{color:#dc2626;font-weight:700;}
    </style>
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
            <tr>
                <th>Pelanggan</th>
                <th>Meter Lalu</th>
                <th>Meter Kini</th>
                <th>Konsumsi</th>
                <th>Tanggal Catat</th>
                <th>Petugas</th>
                <th>Foto Meter</th>
                <th>Status Verifikasi</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meterRecords as $record)
                <tr>
                    <td>{{ $record->pelanggan?->kode_pelanggan ?? '-' }}<br>{{ $record->pelanggan?->name ?? '-' }}</td>
                    <td>{{ number_format($record->meter_previous_month) }}</td>
                    <td>{{ number_format($record->meter_current_month) }}</td>
                    <td>
                        {{ number_format($record->meter_current_month - $record->meter_previous_month) }}
                        @if($record->is_anomaly)
                            <div class="anomali">Anomali</div>
                        @endif
                    </td>
                    <td>{{ $record->recorded_at?->format('Y-m-d') }}</td>
                    <td>{{ $record->petugas?->name ?? '-' }}</td>
                    <td>
                        @if($record->meter_photo_path)
                            <a href="{{ asset('storage/'.$record->meter_photo_path) }}" target="_blank" style="padding:6px 10px;background:#2563eb;">Lihat Foto</a>
                        @else
                            -
                        @endif
                    </td>
                    <td><span class="badge {{ $record->verification_status }}">{{ ucfirst($record->verification_status) }}</span></td>
                    <td>{{ $record->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">Belum ada data pencatatan meter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">{{ $meterRecords->links() }}</div>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
