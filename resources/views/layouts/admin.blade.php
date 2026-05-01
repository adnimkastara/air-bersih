<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $brandingData = $branding ?? [];
        $pageTitle = trim($__env->yieldContent('title', 'Admin'));
        $appName = data_get($brandingData, 'app_name', 'Tirta Sejahtera');
        $themeColor = data_get($brandingData, 'primary_color', '#1d4ed8');
        $favicon = data_get($brandingData, 'favicon_url');
    @endphp
    <title>{{ $pageTitle }} | {{ $appName }}</title>
    <meta name="theme-color" content="{{ $themeColor }}">
    @if($favicon)
        <link rel="icon" href="{{ $favicon }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg:#F8FAFC;
            --card:#ffffff;
            --line:#dbe4f0;
            --text:#0f172a;
            --muted:#64748b;
            --primary:#0061FF;
            --sidebar:#111827;
            --sidebar-muted:#9ca3af;
            --danger:#dc2626;
            --radius:14px;
        }
        *{box-sizing:border-box;}
        body{margin:0;font-family:'Inter',sans-serif;color:var(--text);background:var(--bg);}
        .admin-shell{display:grid;grid-template-columns:280px 1fr;min-height:100vh;}
        .sidebar{background:var(--sidebar);color:#e5e7eb;padding:28px 20px;box-shadow:0 8px 24px rgba(15,23,42,.16);}
        .brand{font-size:1.05rem;font-weight:700;display:flex;gap:12px;align-items:center;margin-bottom:24px;}
        .brand-logo{width:38px;height:38px;border-radius:12px;object-fit:cover;background:#fff;}
        .brand-fallback-mark{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,var(--primary),#0ea5e9);font-size:.8rem;font-weight:800;color:#f8fafc;}
        .brand-text{line-height:1.2;}
        .menu{display:grid;gap:6px;}
        .menu a,.menu button{position:relative;color:#d1d5db;text-decoration:none;padding:11px 14px;border-radius:12px;display:flex;gap:10px;align-items:center;font-size:.92rem;background:none;border:none;text-align:left;width:100%;cursor:pointer;transition:all .2s ease;}
        .menu a:hover,.menu button:hover{background:rgba(255,255,255,.06);color:#fff;}
        .menu a.active,.menu button.active{background:rgba(0,97,255,.12);color:#fff;}
        .menu a.active::before,.menu button.active::before{content:"";position:absolute;left:-10px;top:8px;bottom:8px;width:3px;border-radius:99px;background:#60a5fa;}
        .menu-group{margin-top:10px;}
        .menu-group-title{padding:12px 14px 6px;font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sidebar-muted);font-weight:700;}
        .submenu{display:grid;gap:6px;padding-left:8px;}
        .main{padding:24px 28px 34px;}
        .topbar,.card{background:var(--card);border-radius:16px;box-shadow:0 4px 16px rgba(15,23,42,.08);border:none;}
        .topbar{padding:14px 18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;gap:12px;}
        .topbar-brand{display:flex;align-items:center;gap:12px;min-height:42px;}
        .topbar-logo{height:42px;width:auto;object-fit:contain;}
        .card{padding:24px;margin-bottom:16px;}
        .page-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:16px;}
        .page-header h1{margin:0;font-size:1.5rem;font-weight:800;}
        .page-header p{margin:6px 0 0;color:var(--muted);}
        .grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;} .grid-2 .full{grid-column:1/-1;}
        label{display:block;font-size:.84rem;font-weight:600;color:#334155;margin-bottom:8px;}
        input,select,textarea{width:100%;border:1px solid #e2e8f0;border-radius:12px;padding:11px 13px;background:#fff;outline:none;transition:border-color .2s, box-shadow .2s;}
        input:focus,select:focus,textarea:focus{border-color:#93c5fd;box-shadow:0 0 0 3px rgba(0,97,255,.12);}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;border-radius:12px;border:1px solid transparent;text-decoration:none;cursor:pointer;font-weight:600;}
        .btn-primary{background:var(--primary);color:#fff;} .btn-primary:hover{background:#0056e0;}
        .btn-outline{border-color:#e2e8f0;color:var(--text);background:#fff;}
        .btn-danger{background:#fee2e2;color:#b91c1c;border-color:#fecaca;}
        .btn-sm{padding:7px 10px;font-size:.82rem;border-radius:10px;}
        .btn-icon{width:34px;height:34px;justify-content:center;padding:0;}
        .toolbar{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end;}
        .table-wrap{overflow:auto;} table{width:100%;border-collapse:separate;border-spacing:0;}
        thead th{padding:12px 14px;background:#f8fafc;font-weight:700;color:#334155;border-bottom:1px solid #e5e7eb;}
        tbody td{padding:14px;border-bottom:1px solid #eef2f7;text-align:left;font-size:.92rem;vertical-align:middle;}
        .badge{display:inline-flex;padding:5px 10px;border-radius:999px;font-size:.76rem;font-weight:600;} .badge-success{background:#dcfce7;color:#166534;} .badge-danger{background:#fee2e2;color:#991b1b;}
        @media(max-width:960px){.admin-shell{grid-template-columns:1fr;}.grid-2,.toolbar{grid-template-columns:1fr;}}
    </style>
    @yield('head')
</head>
<body>
@php($user = auth()->user())
<div class="admin-shell">
    @include('layouts.partials.sidebar')
    <main class="main">
        @include('layouts.partials.navbar')
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
