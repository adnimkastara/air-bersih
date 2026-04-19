<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f8fafc;padding:24px;}
        table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e2e8f0;margin-bottom:20px;}
        th,td{padding:12px;border-bottom:1px solid #e2e8f0;vertical-align:top;}
        th{background:#f1f5f9;text-align:left;}
        a,button,input{padding:10px 14px;border-radius:8px;text-decoration:none;border:1px solid transparent;}
        a{background:#0f172a;color:#fff;display:inline-block;}
        button{background:#2563eb;color:#fff;cursor:pointer;}
        input[type="month"]{background:#fff;border-color:#cbd5e1;}
        .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;}
        .badge{padding:6px 10px;border-radius:999px;color:#fff;font-size:12px;font-weight:700;display:inline-block;}
        .card{background:white;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:20px;}
    </style>
</head>
<body>
    <div class="top">
        <h1>Billing / Tagihan</h1>
        <form method="POST" action="{{ route('tagihan.generate') }}" style="display:flex;gap:8px;align-items:center;">
            @csrf
            <input type="month" name="period" value="{{ $selectedPeriod }}" required>
            <button type="submit">Generate Tagihan Bulanan</button>
        </form>
    </div>

    @if(session('status'))
        <div style="margin-bottom:16px;padding:14px;background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div style="margin-bottom:16px;padding:14px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card">
        <h2>Tabel Tarif</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Tarif</th>
                    <th>Jenis Pelanggan</th>
                    <th>Tarif Dasar</th>
                    <th>Tarif Pemakaian / m³</th>
                    <th>Denda Keterlambatan / hari</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tarifs as $tarif)
                    <tr>
                        <td>{{ $tarif->name }}</td>
                        <td>{{ $tarif->customer_type ?? 'Semua jenis' }}</td>
                        <td>Rp {{ number_format($tarif->base_rate, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($tarif->usage_rate, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($tarif->late_fee_per_day, 0, ',', '.') }}</td>
                        <td>{{ $tarif->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada data tarif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Tabel Bill / Tagihan</h2>
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Periode</th>
                    <th>Pemakaian</th>
                    <th>Rincian</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Jatuh Tempo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tagihans as $tagihan)
                    <tr>
                        <td>{{ $tagihan->pelanggan?->name ?? '-' }}</td>
                        <td>{{ $tagihan->period }}</td>
                        <td>{{ number_format($tagihan->usage_m3, 0, ',', '.') }} m³</td>
                        <td>
                            Dasar: Rp {{ number_format($tagihan->base_amount, 0, ',', '.') }}<br>
                            Pakai: Rp {{ number_format($tagihan->usage_amount, 0, ',', '.') }}<br>
                            Denda: Rp {{ number_format($tagihan->late_fee, 0, ',', '.') }}
                        </td>
                        <td>Rp {{ number_format($tagihan->amount, 0, ',', '.') }}</td>
                        <td><span class="badge" style="background:{{ $tagihan->status === 'lunas' ? '#16a34a' : ($tagihan->status === 'menunggak' ? '#dc2626' : ($tagihan->status === 'terbit' ? '#2563eb' : '#7c3aed')) }};">{{ ucfirst($tagihan->status) }}</span></td>
                        <td>{{ $tagihan->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('tagihan.show', $tagihan) }}" style="margin-bottom:8px;background:#0f766e;">Detail</a>
                            @if($tagihan->status === 'draft')
                                <form method="POST" action="{{ route('tagihan.publish', $tagihan) }}" style="display:inline-block;">
                                    @csrf
                                    <button type="submit">Terbitkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
</body>
</html>
