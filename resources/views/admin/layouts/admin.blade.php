<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Kemasify Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #08080a; --bg2: #0f0f12; --bg3: #161619;
            --border: rgba(255,255,255,0.07);
            --border-hover: rgba(255,255,255,0.13);
            --text: #ededef; --text-muted: #6b6b75;
            --accent: rgb(137,82,255); --accent-dim: rgba(137,82,255,0.1);
            --danger: #ff4757; --warning: #ffa502; --info: #3d9cf5;
            --radius: 10px; --radius-sm: 7px;
            --sidebar-w: 220px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html,body { height:100%; }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); -webkit-font-smoothing:antialiased; }
        h1,h2,h3,h4 { font-family:'Syne',sans-serif; line-height:1.2; }

        /* Layout */
        .admin-layout { display:flex; min-height:100vh; }
        .admin-sidebar {
            width:var(--sidebar-w); flex-shrink:0;
            background:var(--bg2); border-right:1px solid var(--border);
            display:flex; flex-direction:column;
            position:fixed; top:0; left:0; bottom:0; z-index:100;
            padding:20px 12px;
        }
        .admin-main { flex:1; margin-left:var(--sidebar-w); display:flex; flex-direction:column; min-height:100vh; }
        .admin-topbar {
            height:56px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; padding:0 28px; gap:12px;
            background:var(--bg); position:sticky; top:0; z-index:50;
        }
        .admin-content { flex:1; padding:32px 28px; }

        /* Sidebar logo */
        .admin-logo { display:flex; align-items:center; gap:8px; text-decoration:none; margin-bottom:28px; padding:0 4px; }
        .admin-logo-icon { width:30px; height:30px; background:var(--danger); border-radius:7px; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:13px; color:#fff; }
        .admin-logo-text { font-family:'Syne',sans-serif; font-weight:800; font-size:15px; }
        .admin-logo-badge { font-size:9px; background:rgba(255,71,87,0.2); color:var(--danger); padding:2px 6px; border-radius:4px; font-weight:700; letter-spacing:0.05em; }

        /* Nav */
        .nav-group-label { font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); padding:0 8px; margin:20px 0 6px; }
        .nav-group-label:first-of-type { margin-top:0; }
        .nav-item { display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:var(--radius-sm); text-decoration:none; color:var(--text-muted); font-size:13px; font-weight:500; transition:all 0.12s; }
        .nav-item:hover { background:var(--bg3); color:var(--text); }
        .nav-item.active { background:rgba(255,71,87,0.1); color:var(--danger); }
        .nav-item svg { flex-shrink:0; width:15px; height:15px; }
        .nav-badge { margin-left:auto; background:var(--danger); color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:99px; min-width:18px; text-align:center; }
        .nav-badge.warning { background:var(--warning); color:#0d0d0f; }

        /* Topbar */
        .topbar-title { font-family:'Syne',sans-serif; font-weight:700; font-size:15px; flex:1; }
        .admin-user { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted); }
        .admin-avatar { width:28px; height:28px; background:var(--danger); border-radius:6px; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:11px; color:#fff; }

        /* Buttons */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:var(--radius-sm); font-size:13px; font-weight:500; font-family:'DM Sans',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all 0.12s; white-space:nowrap; }
        .btn-primary { background:var(--accent); color:#fff; }
        .btn-primary:hover { background:rgb(155,107,255); }
        .btn-danger { background:rgba(255,71,87,0.12); color:var(--danger); border:1px solid rgba(255,71,87,0.25); }
        .btn-danger:hover { background:rgba(255,71,87,0.2); }
        .btn-ghost { background:transparent; color:var(--text-muted); border:1px solid var(--border); }
        .btn-ghost:hover { border-color:var(--border-hover); color:var(--text); }
        .btn-warning { background:rgba(255,165,2,0.12); color:var(--warning); border:1px solid rgba(255,165,2,0.25); }
        .btn-warning:hover { background:rgba(255,165,2,0.2); }
        .btn-info { background:rgba(61,156,245,0.12); color:var(--info); border:1px solid rgba(61,156,245,0.25); }
        .btn-info:hover { background:rgba(61,156,245,0.2); }
        .btn-sm { padding:5px 10px; font-size:12px; }
        .btn-xs { padding:3px 8px; font-size:11px; }

        /* Cards */
        .card { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius); padding:20px; }

        /* Stats grid */
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:14px; margin-bottom:28px; }
        .stat-box { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px; }
        .stat-box-label { font-size:11px; color:var(--text-muted); font-weight:500; margin-bottom:8px; }
        .stat-box-value { font-family:'Syne',sans-serif; font-size:30px; font-weight:800; line-height:1; }
        .stat-box-sub { font-size:11px; color:var(--text-muted); margin-top:5px; }

        /* Table */
        .table-wrap { background:var(--bg2); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; padding:10px 16px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:var(--text-muted); border-bottom:1px solid var(--border); background:var(--bg3); white-space:nowrap; }
        td { padding:12px 16px; font-size:13px; border-bottom:1px solid rgba(255,255,255,0.03); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:rgba(255,255,255,0.015); }

        /* Badges */
        .badge { display:inline-flex; align-items:center; gap:3px; padding:2px 8px; border-radius:99px; font-size:11px; font-weight:600; }
        .badge-green   { background:rgba(137,82,255,0.12); color:var(--accent); }
        .badge-red     { background:rgba(255,71,87,0.12); color:var(--danger); }
        .badge-yellow  { background:rgba(255,165,2,0.12); color:var(--warning); }
        .badge-blue    { background:rgba(61,156,245,0.12); color:var(--info); }
        .badge-gray    { background:var(--bg3); border:1px solid var(--border); color:var(--text-muted); }
        .badge-premium { background:linear-gradient(135deg,#f8b803,#ff6b35); color:#08080a; }

        /* Alerts */
        .alert { padding:10px 14px; border-radius:var(--radius-sm); font-size:13px; margin-bottom:16px; display:flex; align-items:flex-start; gap:8px; }
        .alert-success { background:rgba(137,82,255,0.08); border:1px solid rgba(137,82,255,0.25); color:var(--accent); }
        .alert-error   { background:rgba(255,71,87,0.08); border:1px solid rgba(255,71,87,0.25); color:var(--danger); }
        .alert-warning { background:rgba(255,165,2,0.08); border:1px solid rgba(255,165,2,0.25); color:var(--warning); }

        /* Forms */
        .form-group { margin-bottom:18px; }
        .form-label { display:block; font-size:12px; font-weight:500; color:var(--text-muted); margin-bottom:7px; }
        .form-input, .form-select, .form-textarea {
            width:100%; padding:9px 12px;
            background:var(--bg3); border:1px solid var(--border);
            border-radius:var(--radius-sm); color:var(--text);
            font-size:13px; font-family:'DM Sans',sans-serif; outline:none;
            transition:border-color 0.12s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:rgba(137,82,255,0.4); }
        .form-textarea { resize:vertical; min-height:90px; }
        .form-error { font-size:11px; color:var(--danger); margin-top:5px; }
        .form-hint  { font-size:11px; color:var(--text-muted); margin-top:5px; }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }

        /* Toggle */
        .toggle-wrap { display:flex; align-items:center; gap:10px; cursor:pointer; }
        .toggle { position:relative; width:36px; height:20px; }
        .toggle input { opacity:0; width:0; height:0; }
        .toggle-slider { position:absolute; inset:0; background:var(--bg3); border:1px solid var(--border); border-radius:20px; transition:0.15s; }
        .toggle-slider:before { content:''; position:absolute; width:14px; height:14px; left:2px; top:2px; background:var(--text-muted); border-radius:50%; transition:0.15s; }
        .toggle input:checked + .toggle-slider { background:var(--accent-dim); border-color:var(--accent); }
        .toggle input:checked + .toggle-slider:before { transform:translateX(16px); background:var(--accent); }

        /* Filters bar */
        .filters-bar { display:flex; align-items:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
        .filters-bar .form-input, .filters-bar .form-select { width:auto; min-width:140px; }

        /* Pagination */
        .pagination { display:flex; gap:4px; justify-content:center; margin-top:20px; }
        .page-link { padding:6px 12px; border-radius:var(--radius-sm); font-size:13px; text-decoration:none; color:var(--text-muted); border:1px solid var(--border); transition:all 0.12s; }
        .page-link:hover { border-color:var(--border-hover); color:var(--text); }
        .page-link.active { background:var(--accent); color:#fff; border-color:var(--accent); font-weight:700; }

        /* Misc */
        .flex { display:flex; } .items-center { align-items:center; } .justify-between { justify-content:space-between; }
        .gap-2 { gap:8px; } .gap-3 { gap:12px; }
        .mt-1{margin-top:4px;} .mt-2{margin-top:8px;} .mt-4{margin-top:16px;} .mt-6{margin-top:24px;}
        .mb-2{margin-bottom:8px;} .mb-4{margin-bottom:16px;} .mb-6{margin-bottom:24px;}
        .text-sm{font-size:12px;} .text-muted{color:var(--text-muted);} .text-accent{color:var(--accent);}
        .text-danger{color:var(--danger);} .text-warning{color:var(--warning);}
        .font-bold{font-weight:700;} .w-full{width:100%;}
        .truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .divider{border:none;border-top:1px solid var(--border);margin:20px 0;}
        @keyframes spin{to{transform:rotate(360deg);}}
        .spinner{width:16px;height:16px;border:2px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin 0.6s linear infinite;}
    </style>
    @stack('styles')
</head>
<body>
<div class="admin-layout">

    {{-- Sidebar --}}
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-logo">
            <div class="admin-logo-icon">A</div>
            <div>
                <div class="admin-logo-text">Kemasify</div>
                <div class="admin-logo-badge">ADMIN</div>
            </div>
        </a>

        <div class="nav-group-label">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>

        <div class="nav-group-label">Manajemen</div>
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Users
        </a>
        <a href="{{ route('admin.product-models.index') }}" class="nav-item {{ request()->routeIs('admin.product-models.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Product Models
        </a>

        <div class="nav-group-label">Jobs</div>
        <a href="{{ route('admin.ai-jobs.index') }}" class="nav-item {{ request()->routeIs('admin.ai-jobs.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            AI Jobs
            @php $pendingAi = \App\Models\AiGenerationJob::where('status','pending')->count(); @endphp
            @if($pendingAi > 0)
            <span class="nav-badge warning">{{ $pendingAi }}</span>
            @endif
        </a>
        <a href="{{ route('admin.render-jobs.index') }}" class="nav-item {{ request()->routeIs('admin.render-jobs.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Render Jobs
            @php $pendingRender = \App\Models\RenderJob::where('status','pending')->count(); @endphp
            @if($pendingRender > 0)
            <span class="nav-badge">{{ $pendingRender }}</span>
            @endif
        </a>

        <div class="nav-group-label">Revenue</div>
        <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
            Orders & Payments
            @php $pendingOrders = \App\Models\Order::where('status','pending')->count(); @endphp
            @if($pendingOrders > 0)
            <span class="nav-badge warning">{{ $pendingOrders }}</span>
            @endif
        </a>

        <div style="margin-top:auto; padding-top:16px; border-top:1px solid var(--border);">
            <a href="{{ route('dashboard') }}" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Kembali ke App
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item w-full" style="background:none;border:none;cursor:pointer;text-align:left;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-title">@yield('page-title', 'Admin Panel')</div>
            <div class="admin-user">
                <div class="admin-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                {{ auth()->user()->name }}
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))
            <div class="alert alert-success">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-error">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>