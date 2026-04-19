<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan</title>
    <style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;}th{background:#f1f5f9;text-align:left;}a,button{padding:10px 14px;border-radius:8px;text-decoration:none;border:none;cursor:pointer;}a{background:#0f172a;color:#fff;}button{background:#2563eb;color:#fff;} .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}.badge{padding:6px 10px;border-radius:8px;color:#fff;}</style>
</head>
<body>
    <div class="top">
        <h1>Tagihan</h1>
        <form method="POST" action="{{ route('tagihan.generate') }}" style="margin:0;">
            @csrf
            <button type="submit">Generate Tagihan</button>
        </form>
    </div>

    @if(session('status'))
        <div style="margin-bottom:16px;padding:14px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">{{ session('status') }}</div>
    @endif

    <table>
        <thead>
            <tr><th>Pelanggan</th><th>Periode</th><th>Jumlah</th><th>Status</th><th>Jatuh Tempo</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            @foreach($tagihans as $tagihan)
                <tr>
                    <td>{{ $tagihan->pelanggan?->name ?? '-' }}</td>
                    <td>{{ $tagihan->period }}</td>
                    <td>Rp {{ number_format($tagihan->amount, 0, ',', '.') }}</td>
                    <td><span class="badge" style="background:{{ $tagihan->status === 'lunas' ? '#16a34a' : ($tagihan->status === 'menunggak' ? '#dc2626' : ($tagihan->status === 'terbit' ? '#2563eb' : '#7c3aed')) }};">{{ ucfirst($tagihan->status) }}</span></td>
                    <td>{{ $tagihan->due_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>
                        @if($tagihan->status === 'draft')
                            <form method="POST" action="{{ route('tagihan.publish', $tagihan) }}" style="display:inline-block;">
                                @csrf
                                <button type="submit">Terbitkan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
