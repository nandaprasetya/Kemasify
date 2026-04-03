<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kemasify') — AI Packaging Design</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0d0d0f;
            --bg2: #131316;
            --bg3: #1a1a1f;
            --border: rgba(255,255,255,0.08);
            --border-hover: rgba(255,255,255,0.15);
            --text: #f0f0f2;
            --text-muted: #7a7a85;
            --accent: rgb(137,82,255);
            --accent-dim: rgba(137,82,255,0.12);
            --accent-hover: rgb(155,107,255);
            --danger: #ff4757;
            --warning: #ffa502;
            --radius: 12px;
            --radius-sm: 8px;
            --radius-lg: 20px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        h1,h2,h3,h4,h5,h6 { font-family: 'Syne', sans-serif; line-height: 1.2; }

        /* Layout */
        .app-layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 240px;
            flex-shrink: 0;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .main-content {
            flex: 1;
            margin-left: 240px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 32px;
            gap: 16px;
            background: var(--bg);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .page-content {
            flex: 1;
            padding: 40px 32px;
            max-width: 1200px;
            width: 100%;
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-icon svg { color: #0d0d0f; }
        .logo-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 18px;
            color: var(--text);
        }

        /* Nav */
        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 8px;
            margin-bottom: 8px;
            margin-top: 24px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
        }
        .nav-link:hover { background: var(--bg3); color: var(--text); }
        .nav-link.active { background: var(--accent-dim); color: var(--accent); }
        .nav-link svg { flex-shrink: 0; }

        /* Token widget */
        .token-widget {
            margin-top: auto;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
        }
        .token-label { font-size: 11px; color: var(--text-muted); margin-bottom: 6px; }
        .token-balance {
            font-family: 'Syne', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
        }
        .token-bar-bg {
            height: 4px;
            background: var(--border);
            border-radius: 99px;
            margin: 10px 0;
        }
        .token-bar-fill {
            height: 4px;
            background: var(--accent);
            border-radius: 99px;
            transition: width 0.5s;
        }
        .token-meta { font-size: 12px; color: var(--text-muted); }
        .btn-refill {
            display: block;
            width: 100%;
            margin-top: 12px;
            padding: 8px;
            background: transparent;
            border: 1px solid var(--border-hover);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-refill:hover { background: var(--bg); border-color: var(--accent); color: var(--accent); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--accent);
            color: #0d0d0f;
        }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); }
        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { border-color: var(--border-hover); color: var(--text); }
        .btn-danger {
            background: transparent;
            color: var(--danger);
            border: 1px solid rgba(255,71,87,0.3);
        }
        .btn-danger:hover { background: rgba(255,71,87,0.1); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-lg { padding: 14px 28px; font-size: 16px; }

        /* Cards */
        .card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
        }
        .card-hover { transition: border-color 0.15s, transform 0.15s; }
        .card-hover:hover { border-color: var(--border-hover); transform: translateY(-2px); }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-success { background: rgba(137,82,255,0.1); border: 1px solid rgba(137,82,255,0.3); color: var(--accent); }
        .alert-error   { background: rgba(255,71,87,0.1); border: 1px solid rgba(255,71,87,0.3); color: var(--danger); }
        .alert-warning  { background: rgba(255,165,2,0.1); border: 1px solid rgba(255,165,2,0.3); color: var(--warning); }

        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.15s;
            outline: none;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--accent);
        }
        .form-textarea { resize: vertical; min-height: 100px; }
        .form-error { font-size: 12px; color: var(--danger); margin-top: 6px; }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-premium { background: linear-gradient(135deg, #f8b803, #ff6b35); color: #0d0d0f; }
        .badge-free    { background: var(--bg3); border: 1px solid var(--border); color: var(--text-muted); }
        .badge-success { background: rgba(137,82,255,0.15); color: var(--accent); }
        .badge-pending { background: rgba(255,165,2,0.15); color: var(--warning); }
        .badge-failed  { background: rgba(255,71,87,0.15); color: var(--danger); }

        /* Utilities */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .ml-auto { margin-left: auto; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .text-sm { font-size: 13px; }
        .text-muted { color: var(--text-muted); }
        .text-accent { color: var(--accent); }
        .text-danger { color: var(--danger); }
        .font-semibold { font-weight: 600; }
        .w-full { width: 100%; }
        .grid { display: grid; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-4 { grid-template-columns: repeat(4, 1fr); gap: 16px; }

        /* Spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 24px 0;
        }

        /* Dropdown */
        .dropdown { position: relative; }
        .dropdown-menu {
            position: absolute;
            right: 0; top: calc(100% + 8px);
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            min-width: 160px;
            padding: 6px;
            z-index: 200;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            display: none;
        }
        .dropdown.open .dropdown-menu { display: block; }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.1s;
            border: none;
            background: none;
            width: 100%;
        }
        .dropdown-item:hover { background: var(--bg3); color: var(--text); }
        .dropdown-item.danger:hover { color: var(--danger); }
    </style>

    @stack('styles')
</head>
<body>
<div class="app-layout">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <a href="{{ route('home') }}" class="logo">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="logo-text">Kemasify</span>
        </a>

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('projects.select-model') }}" class="nav-link {{ request()->routeIs('projects.select-model') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Proyek Baru
        </a>

        <div class="nav-section-label">Akun</div>

        <a href="{{ route('tokens.index') }}" class="nav-link {{ request()->routeIs('tokens.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
            Token & Riwayat
        </a>
        <a href="{{ route('payment.pricing') }}" class="nav-link {{ request()->routeIs('payment.*') ? 'active' : '' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 7a2 2 0 0 1 2-2h14a1 1 0 0 1 1 1v3H5a2 2 0 0 0-2 2V7z"/>
                <path d="M3 11a2 2 0 0 1 2-2h15a1 1 0 0 1 1 1v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5z"/>
                <circle cx="16" cy="13" r="1"/>
            </svg>
            Upgrade Akun & Top up
        </a>

        {{-- Token Widget --}}
        @auth
        <div class="token-widget" id="token-widget">
            <div class="token-label">Token Tersisa</div>
            <div class="token-balance" id="sidebar-token-balance">{{ auth()->user()->token_balance }}</div>
            <div class="token-bar-bg">
                <div class="token-bar-fill" id="sidebar-token-bar" style="width: {{ min(100, auth()->user()->token_balance / 50 * 100) }}%"></div>
            </div>
            <div class="token-meta" id="sidebar-token-meta">
                @if(auth()->user()->isPremium())
                    <span class="badge badge-premium">✦ Premium</span>
                @else
                    dari 50 token gratis
                @endif
            </div>
            @if(auth()->user()->isFree())
            <button class="btn-refill" id="btn-sidebar-refill" onclick="doRefill()">
                ↺ Refill Token
            </button>
            @endif
        </div>
        @endauth

        {{-- User --}}
        <div class="dropdown mt-2" id="user-dropdown">
            <button onclick="document.getElementById('user-dropdown').classList.toggle('open')"
                style="display:flex;align-items:center;gap:10px;width:100%;padding:8px 10px;background:none;border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;color:var(--text);font-family:'DM Sans',sans-serif;font-size:13px;">
                <div style="width:28px;height:28px;background:var(--accent);border-radius:6px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:12px;color:#0d0d0f;flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span style="flex:1;text-align:left;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ auth()->user()->name }}
                </span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="dropdown-menu">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-content">
        <header class="topbar">
            <div style="flex:1">
                @yield('breadcrumb')
            </div>
            <div class="flex items-center gap-3">
                @yield('topbar-actions')
            </div>
        </header>

        <main class="page-content">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dd = document.getElementById('user-dropdown');
    if (dd && !dd.contains(e.target)) dd.classList.remove('open');
});

// Refill token
async function doRefill() {
    const btn = document.getElementById('btn-sidebar-refill');
    if (!btn) return;
    btn.textContent = '⏳ Memproses...';
    btn.disabled = true;

    try {
        const res = await fetch('{{ route("tokens.refill") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('sidebar-token-balance').textContent = data.token_balance;
            const pct = Math.min(100, data.token_balance / 50 * 100);
            document.getElementById('sidebar-token-bar').style.width = pct + '%';
            btn.textContent = '✓ Berhasil!';
            setTimeout(() => { btn.textContent = '↺ Refill Token'; btn.disabled = false; }, 2000);
        } else {
            btn.textContent = data.error || 'Belum bisa refill';
            setTimeout(() => { btn.textContent = '↺ Refill Token'; btn.disabled = false; }, 3000);
        }
    } catch(e) {
        btn.textContent = 'Error';
        btn.disabled = false;
    }
}

// Poll token status every 30s
setInterval(async () => {
    try {
        const res = await fetch('{{ route("tokens.status") }}');
        const data = await res.json();
        const el = document.getElementById('sidebar-token-balance');
        if (el) el.textContent = data.token_balance;
        const bar = document.getElementById('sidebar-token-bar');
        if (bar) bar.style.width = Math.min(100, data.token_balance / 50 * 100) + '%';
    } catch(e) {}
}, 30000);
</script>

@stack('scripts')
</body>
</html>