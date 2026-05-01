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
            --line:#e5edf6;
            --text:#0f172a;
            --muted:#64748b;
            --primary:#0061FF;
            --primary-soft:rgba(0,97,255,.1);
            --sidebar:#0f172a;
            --sidebar-panel:#111c2f;
            --sidebar-muted:#9ca3af;
            --danger:#dc2626;
            --radius:16px;
            --shadow:0 10px 30px rgba(15,23,42,.06);
            --shadow-soft:0 4px 14px rgba(15,23,42,.05);
        }
        *{box-sizing:border-box;}
        body{margin:0;font-family:'Inter',sans-serif;color:var(--text);background:var(--bg);}
        .admin-shell{display:grid;grid-template-columns:280px 1fr;min-height:100vh;}
        .sidebar{background:linear-gradient(180deg,var(--sidebar),#101827);color:#e5e7eb;padding:28px 18px;box-shadow:10px 0 30px rgba(15,23,42,.12);}
        .brand{font-size:1.05rem;font-weight:700;display:flex;gap:12px;align-items:center;margin-bottom:28px;padding:0 6px;}
        .brand-logo{width:42px;height:42px;border-radius:14px;object-fit:cover;background:#fff;}
        .brand-fallback-mark{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,var(--primary),#0ea5e9);font-size:.8rem;font-weight:800;color:#f8fafc;}
        .brand-text{line-height:1.2;}
        .menu{display:grid;gap:7px;}
        .menu a,.menu button{position:relative;color:#cbd5e1;text-decoration:none;padding:12px 14px;border-radius:14px;display:flex;gap:11px;align-items:center;font-size:.92rem;background:none;border:none;text-align:left;width:100%;cursor:pointer;transition:background .2s ease,color .2s ease,transform .2s ease;}
        .menu a i,.menu button i{width:20px;text-align:center;color:#94a3b8;}
        .menu a:hover,.menu button:hover{background:rgba(255,255,255,.07);color:#fff;transform:translateX(2px);}
        .menu a:hover i,.menu button:hover i,.menu a.active i,.menu button.active i{color:#93c5fd;}
        .menu a.active,.menu button.active{background:var(--primary-soft);color:#fff;}
        .menu a.active::before,.menu button.active::before{content:"";position:absolute;left:-8px;top:10px;bottom:10px;width:4px;border-radius:999px;background:#60a5fa;}
        .menu-group{margin-top:16px;}
        .menu-group-title{padding:14px 14px 7px;font-size:.68rem;text-transform:uppercase;letter-spacing:.12em;color:var(--sidebar-muted);font-weight:800;}
        .submenu{display:grid;gap:7px;}
        .main{padding:28px 32px 38px;min-width:0;}
        .topbar,.card{background:var(--card);border-radius:16px;box-shadow:var(--shadow-soft);border:1px solid rgba(226,232,240,.78);}
        .topbar{padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;gap:16px;}
        .topbar-brand{display:flex;align-items:center;gap:12px;min-height:42px;}
        .topbar-logo{height:42px;width:auto;object-fit:contain;}
        .card{padding:26px;margin-bottom:18px;}
        .form-card{max-width:760px;}
        .form-card-narrow{max-width:500px;}
        .page-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px;}
        .page-header h1{margin:0;font-size:1.55rem;font-weight:800;letter-spacing:0;}
        .page-header p{margin:6px 0 0;color:var(--muted);}
        .grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;} .grid-2 .full{grid-column:1/-1;}
        label{display:block;font-size:.84rem;font-weight:700;color:#334155;margin-bottom:8px;}
        input,select,textarea{width:100%;border:1px solid #e2e8f0;border-radius:12px;padding:11px 13px;background:#fff;outline:none;transition:border-color .2s, box-shadow .2s;}
        input:focus,select:focus,textarea:focus{border-color:#93c5fd;box-shadow:0 0 0 3px rgba(0,97,255,.12);}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 15px;border-radius:12px;border:1px solid transparent;text-decoration:none;cursor:pointer;font-weight:700;line-height:1;transition:background .2s ease,color .2s ease,box-shadow .2s ease,transform .2s ease;}
        .btn:hover{transform:translateY(-1px);}
        .btn-primary{background:var(--primary);color:#fff;box-shadow:0 8px 18px rgba(0,97,255,.18);} .btn-primary:hover{background:#0056e0;}
        .btn-outline{border-color:#e2e8f0;color:var(--text);background:#fff;}
        .btn-outline:hover{background:#f8fafc;border-color:#cbd5e1;}
        .btn-danger{background:#fee2e2;color:#b91c1c;border-color:#fecaca;}
        .btn-danger:hover{background:#fecaca;}
        .btn-soft-primary{background:#eaf2ff;color:#0056e0;border-color:#d8e7ff;}
        .btn-soft-danger{background:#fff1f2;color:#be123c;border-color:#ffe4e6;}
        .btn-sm{padding:8px 11px;font-size:.82rem;border-radius:12px;}
        .btn-icon{width:36px;height:36px;justify-content:center;padding:0;}
        .toolbar{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end;}
        .table-wrap{overflow:auto;padding:0;} table{width:100%;border-collapse:separate;border-spacing:0;}
        thead th{padding:14px 16px;background:#f8fafc;font-weight:800;color:#334155;border-bottom:1px solid #e5e7eb;text-align:left;font-size:.82rem;}
        thead th:first-child{border-top-left-radius:16px;} thead th:last-child{border-top-right-radius:16px;}
        tbody td{padding:18px 16px;border-bottom:1px solid #eef2f7;text-align:left;font-size:.92rem;vertical-align:middle;}
        tbody tr:hover td{background:#fbfdff;}
        tbody tr:last-child td{border-bottom:none;}
        .muted{color:var(--muted);}
        .badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:.76rem;font-weight:700;} .badge-success{background:#dcfce7;color:#166534;} .badge-danger{background:#fee2e2;color:#991b1b;} .badge-warning{background:#fef3c7;color:#92400e;}
        .pagination{display:flex;gap:8px;flex-wrap:wrap;}
        @media(max-width:960px){.admin-shell{grid-template-columns:1fr;}.sidebar{position:static;}.main{padding:22px 16px 30px;}.grid-2,.toolbar{grid-template-columns:1fr;}.page-header,.topbar{flex-direction:column;align-items:flex-start;}}
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
