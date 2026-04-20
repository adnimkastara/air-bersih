<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ $branding['app_name'] }}</title>
    <meta name="theme-color" content="{{ $branding['theme_color'] }}">
    <link rel="icon" href="{{ $branding['favicon_url'] }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary: {{ $branding['theme_color'] }}; --line:#dbe4f0; --muted:#64748b; --text:#0f172a; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:20px; font-family:'Inter',sans-serif; color:var(--text); background:#f1f5f9; }
        .card { width:min(430px, 100%); background:#fff; border:1px solid var(--line); border-radius:16px; padding:26px; box-shadow:0 18px 45px rgba(15,23,42,.08); }
        .brand { text-align:center; margin-bottom:18px; }
        .brand img { max-height:72px; width:auto; object-fit:contain; }
        .brand-mark { width:64px; height:64px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:800; background:var(--primary); }
        h1 { margin:12px 0 4px; font-size:1.4rem; }
        p { margin:0; color:var(--muted); font-size:.92rem; }
        label { display:block; font-size:.88rem; margin-bottom:6px; color:#334155; }
        input[type="email"], input[type="password"] { width:100%; border:1px solid var(--line); border-radius:10px; padding:11px 12px; margin-bottom:12px; }
        .remember { display:flex; align-items:center; gap:8px; font-size:.9rem; margin-bottom:14px; color:#475569; }
        button { width:100%; border:none; border-radius:10px; padding:11px 12px; color:#fff; font-weight:700; background:var(--primary); cursor:pointer; }
        .error { border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; border-radius:10px; padding:10px; margin-bottom:12px; font-size:.9rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            @if(!empty($branding['logo_url']))
                <img src="{{ $branding['logo_url'] }}" alt="Logo {{ $branding['app_name'] }}" loading="lazy">
            @else
                <span class="brand-mark">{{ $branding['initials'] }}</span>
            @endif
            <h1>{{ $branding['app_name'] }}</h1>
            <p>{{ $branding['subtitle'] }}</p>
        </div>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.perform') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label class="remember" for="remember">
                <input id="remember" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya
            </label>

            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>
