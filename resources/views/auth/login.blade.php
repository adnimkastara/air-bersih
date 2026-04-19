<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body { margin:0; min-height:100vh; font-family:'Instrument Sans',sans-serif; background:#f8fafc; display:flex; align-items:center; justify-content:center; }
        .card { width:min(420px,90vw); background:#fff; border:1px solid #e2e8f0; border-radius:18px; box-shadow:0 18px 55px rgba(15,23,42,.08); padding:32px; }
        .title { margin:0 0 20px; font-size:1.75rem; color:#0f172a; }
        .field { margin-bottom:18px; }
        .field label { display:block; margin-bottom:8px; color:#475569; }
        .field input { width:100%; border:1px solid #cbd5e1; border-radius:12px; padding:12px 14px; font-size:0.95rem; }
        .button { width:100%; padding:14px; border:none; border-radius:12px; background:#0f172a; color:#fff; font-weight:600; cursor:pointer; }
        .error { margin-bottom:18px; color:#b91c1c; font-size:.95rem; }
        .footer { margin-top:20px; color:#475569; font-size:.95rem; }
        .footer a { color:#0f172a; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
    <main class="card">
        <h1 class="title">Login</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.perform') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <div class="field">
                <label><input type="checkbox" name="remember"> Remember me</label>
            </div>
            <button type="submit" class="button">Sign in</button>
        </form>

        <p class="footer">Akun dibuat oleh admin sistem. Hubungi root/admin desa untuk pembuatan akun.</p>
    </main>
</body>
</html>
