<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body { margin:0; min-height:100vh; font-family:'Instrument Sans',sans-serif; background:#f8fafc; display:flex; align-items:center; justify-content:center; }
        .card { width:min(720px,95vw); background:#fff; border:1px solid #e2e8f0; border-radius:18px; box-shadow:0 18px 55px rgba(15,23,42,.08); padding:36px; }
        h1 { margin-top:0; color:#0f172a; }
        p { color:#475569; line-height:1.75; }
        .button { display:inline-flex; align-items:center; justify-content:center; padding:14px 20px; border-radius:12px; background:#0f172a; color:#fff; text-decoration:none; font-weight:600; margin-top:20px; }
        .meta { display:grid; gap:12px; margin-top:24px; }
        .meta div { padding:18px 20px; border-radius:14px; background:#f8fafc; border:1px solid #e2e8f0; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <strong>{{ $user->name }}</strong>. This page is only visible to users with administrative privileges.</p>
        <div class="meta">
            <div>
                <strong>Email</strong>
                <p>{{ $user->email }}</p>
            </div>
            <div>
                <strong>Role</strong>
                <p>{{ $user->role?->name ?? 'No role assigned' }}</p>
            </div>
        </div>

        @if ($user->isAdmin())
            <a href="{{ route('admin.users') }}" class="button">Manage Users</a>
        @endif

        <a href="{{ route('dashboard') }}" class="button">Back to Dashboard</a>
    </main>
</body>
</html>
