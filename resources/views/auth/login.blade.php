<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Tirta Sejahtera</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root {
            --blue-950: #0b1d5c;
            --blue-900: #122a7a;
            --blue-800: #1e40af;
            --blue-700: #1d4ed8;
            --cyan-500: #06b6d4;
            --teal-500: #14b8a6;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --line: #dbe6f5;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #f8fafc;
            background:
                radial-gradient(circle at 78% 18%, rgba(56, 189, 248, 0.20), transparent 30%),
                radial-gradient(circle at 20% 24%, rgba(34, 211, 238, 0.10), transparent 36%),
                linear-gradient(145deg, var(--blue-950), var(--blue-900) 42%, var(--blue-800) 68%, #1f56d8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
            position: relative;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: absolute;
            left: -10%;
            width: 120%;
            pointer-events: none;
        }

        body::before {
            height: 45vh;
            bottom: -10vh;
            background:
                radial-gradient(100% 160% at 0% 100%, rgba(255, 255, 255, 0.12), transparent 58%),
                radial-gradient(120% 180% at 95% 100%, rgba(103, 232, 249, 0.22), transparent 60%),
                linear-gradient(180deg, rgba(191, 219, 254, 0.04) 0%, rgba(191, 219, 254, 0.24) 100%);
            border-radius: 50% 50% 0 0;
            filter: blur(0.5px);
            transform: rotate(-2deg);
        }

        body::after {
            height: 34vh;
            bottom: -14vh;
            background:
                radial-gradient(120% 180% at 15% 100%, rgba(125, 211, 252, 0.30), transparent 58%),
                radial-gradient(140% 180% at 85% 100%, rgba(240, 249, 255, 0.3), transparent 58%);
            border-radius: 50% 50% 0 0;
            opacity: .85;
            transform: rotate(1.5deg);
        }

        .shell {
            width: min(520px, 100%);
            position: relative;
            z-index: 2;
        }

        .brand-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 22px;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 82px;
            margin-bottom: 14px;
        }

        .logo img {
            width: min(360px, 78vw);
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 6px 15px rgba(15, 23, 42, 0.25));
        }

        .logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 74px;
            height: 74px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--cyan-500), var(--teal-500));
            color: #ffffff;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: .03em;
            box-shadow: 0 16px 32px rgba(8, 47, 73, 0.28);
        }

        .brand-title {
            margin: 0;
            font-size: clamp(1.6rem, 2.8vw, 2rem);
            font-weight: 800;
            letter-spacing: .02em;
        }

        .brand-subtitle,
        .brand-meta {
            margin: 6px 0 0;
            color: rgba(226, 232, 240, 0.92);
        }

        .brand-subtitle { font-size: .97rem; }
        .brand-meta { font-size: .87rem; opacity: .95; }

        .card {
            width: 100%;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(219, 234, 254, 0.7);
            border-radius: 22px;
            box-shadow:
                0 30px 60px rgba(2, 6, 23, 0.24),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(6px);
            padding: 28px;
        }

        .card-title {
            margin: 0 0 18px;
            color: var(--text-dark);
            font-size: 1.85rem;
            font-weight: 700;
        }

        .error {
            margin-bottom: 16px;
            border-radius: 12px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            font-size: .93rem;
            padding: 10px 12px;
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            font-size: .92rem;
            font-weight: 600;
            color: #334155;
        }

        .field input[type="email"],
        .field input[type="password"] {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #f8fafc;
            color: #0f172a;
            font-size: .95rem;
            padding: 12px 14px;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .field input:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .16);
            background: #ffffff;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: .95rem;
            margin-bottom: 18px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--blue-700);
        }

        .submit-btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
            cursor: pointer;
            transition: transform .15s ease, box-shadow .2s ease, filter .2s ease;
            box-shadow: 0 10px 24px rgba(30, 58, 138, .30);
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .help-note {
            margin: 14px 0 0;
            color: var(--text-muted);
            font-size: .91rem;
            line-height: 1.55;
        }

        @media (max-width: 640px) {
            body { padding: 20px 14px; }
            .card { border-radius: 18px; padding: 22px 18px; }
            .card-title { font-size: 1.55rem; }
            .brand-title { font-size: 1.45rem; }
            .brand-subtitle { font-size: .9rem; }
            .brand-meta { font-size: .8rem; }
        }
    </style>
</head>
<body>
@php
    $mainLogoSvgPath = public_path('assets/logo/logo-main.svg');
    $mainLogoUrl = asset('assets/logo/logo-main.svg');
    $hasMainLogo = file_exists($mainLogoSvgPath);
@endphp

<div class="shell">
    <header class="brand-block">
        <div class="logo" aria-label="Logo Tirta Sejahtera">
            @if($hasMainLogo)
                <img src="{{ $mainLogoUrl }}" alt="Logo Tirta Sejahtera" loading="lazy">
            @else
                <span class="logo-fallback">TS</span>
            @endif
        </div>

        <h1 class="brand-title">Tirta Sejahtera</h1>
        <p class="brand-subtitle">Sistem Pengelolaan Air Bersih Desa dan Kecamatan</p>
        <p class="brand-meta">BUM Desa Bersama Tirta Sejahtera Kecamatan Karanganyar</p>
    </header>

    <main class="card">
        <h2 class="card-title">Login</h2>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.perform') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <label class="remember-row">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span>Remember me</span>
            </label>

            <button type="submit" class="submit-btn">Sign In</button>
        </form>

        <p class="help-note">Akun dibuat oleh admin sistem. Hubungi root/admin desa untuk pembuatan akun.</p>
    </main>
</div>
</body>
</html>
