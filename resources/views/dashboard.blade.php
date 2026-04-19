<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=Nunito:400,600,700&display=swap">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
        }
        .container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            width: min(760px, 90vw);
            padding: 32px;
        }
        h1 {
            margin-top: 0;
            font-size: 2rem;
            color: #0f172a;
        }
        p {
            color: #475569;
            line-height: 1.75;
        }
        .meta {
            display: grid;
            gap: 12px;
            margin: 24px 0;
        }
        .meta div {
            padding: 18px 20px;
            background: #f1f5f9;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        a {
            color: #3b82f6;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 12px 18px;
            margin-top: 16px;
            border: none;
            border-radius: 10px;
            background: #0f172a;
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
        }
        .button.small {
            background: #2563eb;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <p>Welcome back, <strong>{{ $user->name }}</strong>.</p>

        <div class="meta">
            <div>
                <strong>Email</strong>
                <div>{{ $user->email }}</div>
            </div>
            <div>
                <strong>Role</strong>
                <div>{{ $role?->name ?? 'No role assigned' }}</div>
            </div>
        </div>

        <p>
            Ini dashboard ringkas operasional. Gunakan menu untuk mengelola wilayah, pelanggan, meter, tagihan, dan pembayaran.
        </p>

        <div class="meta">
            <div>
                <strong>Total Pelanggan</strong>
                <div>{{ $totalPelanggan }}</div>
            </div>
            <div>
                <strong>Total Tagihan</strong>
                <div>{{ $totalTagihan }}</div>
            </div>
            <div>
                <strong>Total Pembayaran</strong>
                <div>{{ $totalPembayaran }}</div>
            </div>
            <div>
                <strong>Total Tunggakan</strong>
                <div>{{ $totalTunggakan }}</div>
            </div>
            <div>
                <strong>Jumlah Gangguan</strong>
                <div>{{ $totalGangguan }}</div>
            </div>
        </div>

        <div style="display:grid; gap:10px; margin-top:20px;">
            <a href="{{ route('kecamatan.index') }}" class="button small">Master Kecamatan</a>
            <a href="{{ route('desa.index') }}" class="button small">Master Desa</a>
            <a href="{{ route('pelanggan.index') }}" class="button small">Data Pelanggan</a>
            <a href="{{ route('meter_records.index') }}" class="button small">Pencatatan Meter</a>
            <a href="{{ route('tagihan.index') }}" class="button small">Tagihan</a>
            <a href="{{ route('pembayaran.index') }}" class="button small">Pembayaran</a>
        </div>

        @if ($user->isAdmin())
            <a href="{{ route('admin') }}" class="button small">Admin Management</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="button">Log out</button>
        </form>

        <p>
            <a href="{{ url('/') }}">Back to welcome page</a>
        </p>
    </div>
</body>
</html>
