<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dashboardTitle }}</title>
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
            width: min(880px, 92vw);
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            border: none;
            border-radius: 10px;
            background: #0f172a;
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
        }
        .button.small {
            background: #2563eb;
        }
        .shortcut-grid {
            display:grid;
            gap:10px;
            margin-top:20px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $dashboardTitle }}</h1>
        <p>Selamat datang kembali, <strong>{{ $user->name }}</strong>.</p>

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

        <p>{{ $dashboardDescription }}</p>

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

        <h3>Shortcut Menu Utama</h3>
        <div class="shortcut-grid">
            @foreach ($shortcuts as $shortcut)
                <a href="{{ route($shortcut['route']) }}" class="button small">{{ $shortcut['label'] }}</a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">
            @csrf
            <button type="submit" class="button">Log out</button>
        </form>

        <p>
            <a href="{{ url('/') }}">Back to welcome page</a>
        </p>
    </div>
</body>
</html>
