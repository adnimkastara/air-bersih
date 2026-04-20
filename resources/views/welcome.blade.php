<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $branding['app_name'] }}</title>
    <meta name="theme-color" content="{{ $branding['primary_color'] }}">
    @if(!empty($branding['favicon_url']))
        <link rel="icon" href="{{ $branding['favicon_url'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: {{ $branding['primary_color'] }}; --text:#0f172a; --muted:#64748b; --line:#e2e8f0; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Inter',sans-serif; color:var(--text); background:#f8fafc; }
        .container { width:min(980px, 92%); margin:0 auto; }
        .nav { display:flex; justify-content:space-between; align-items:center; padding:16px 0; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit; }
        .brand img { height:44px; width:auto; object-fit:contain; }
        .brand-mark { width:40px; height:40px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; background:var(--primary); color:#fff; font-size:.8rem; font-weight:800; }
        .brand h1 { margin:0; font-size:1rem; }
        .brand p { margin:2px 0 0; font-size:.82rem; color:var(--muted); }
        .btn { display:inline-flex; align-items:center; justify-content:center; text-decoration:none; border-radius:10px; padding:10px 14px; font-weight:600; }
        .btn-primary { background:var(--primary); color:#fff; }
        .hero { padding:60px 0 44px; text-align:center; }
        .hero h2 { font-size:2rem; margin:0 0 10px; }
        .hero p { margin:0 auto; max-width:700px; color:var(--muted); line-height:1.65; }
        .feature-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin:30px 0 52px; }
        .feature { background:#fff; border:1px solid var(--line); border-radius:14px; padding:16px; text-align:left; }
        .feature h3 { margin:0 0 6px; font-size:1rem; }
        .feature p { margin:0; font-size:.9rem; color:var(--muted); line-height:1.5; }
        footer { padding:20px 0 30px; border-top:1px solid var(--line); color:var(--muted); font-size:.88rem; text-align:center; }
        @media (max-width: 860px) { .feature-grid { grid-template-columns:1fr; } .hero h2 { font-size:1.6rem; } }
    </style>
</head>
<body>
    <div class="container">
        <header class="nav">
            <a href="/" class="brand">
                @include('layouts.partials.brand-media', [
                    'imageUrl' => $branding['logo_url'] ?? null,
                    'appName' => $branding['app_name'] ?? null,
                    'initials' => $branding['initials'] ?? null,
                    'imgClass' => '',
                    'fallbackClass' => 'brand-mark',
                ])
                <span>
                    <h1>{{ $branding['app_name'] }}</h1>
                    <p>{{ $branding['app_subtitle'] }}</p>
                </span>
            </a>

            @if(Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk ke Sistem</a>
            @endif
        </header>

        <main class="hero">
            <h2>{{ $branding['app_name'] }}</h2>
            <p>{{ $branding['app_subtitle'] }}. Platform ini mempermudah pengelolaan pelanggan, meter, tagihan, pembayaran, dan monitoring layanan secara lebih tertib.</p>

            <div class="feature-grid">
                <article class="feature">
                    <h3>Data Pelanggan</h3>
                    <p>Pendataan pelanggan desa/kecamatan dalam satu sistem yang rapi.</p>
                </article>
                <article class="feature">
                    <h3>Tagihan & Pembayaran</h3>
                    <p>Proses tagihan dan pembayaran lebih jelas dan mudah dipantau.</p>
                </article>
                <article class="feature">
                    <h3>Monitoring Layanan</h3>
                    <p>Pemantauan operasional harian dan penanganan keluhan lebih terstruktur.</p>
                </article>
            </div>
        </main>

        <footer>
            <strong>{{ $branding['app_name'] }}</strong><br>
            {{ $branding['app_subtitle'] }}<br>
            &copy; {{ date('Y') }} {{ $branding['app_name'] }}
        </footer>
    </div>
</body>
</html>
