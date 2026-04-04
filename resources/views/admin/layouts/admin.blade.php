<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Kemasify Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #07070a;
            --bg2: #0e0e12;
            --bg3: #15151a;
            --bg4: #1c1c23;
            --border: rgba(255,255,255,0.06);
            --border-hover: rgba(255,255,255,0.12);
            --text: #eeeef0;
            --text-muted: #5e5e6e;
            --text-dim: #8f8fa0;
            --accent: rgb(130,80,255);
            --accent-bright: rgb(160,110,255);
            --accent-dim: rgba(130,80,255,0.1);
            --accent-glow: rgba(130,80,255,0.25);
            --danger: #ff4055;
            --danger-dim: rgba(255,64,85,0.1);
            --warning: #ffaa00;
            --warning-dim: rgba(255,170,0,0.1);
            --info: #3b9cf5;
            --info-dim: rgba(59,156,245,0.1);
            --success: #22d4a0;
            --success-dim: rgba(34,212,160,0.1);
            --radius: 11px;
            --radius-sm: 7px;
            --radius-xs: 5px;
            --sidebar-w: 228px;
            --topbar-h: 58px;
            --grid-color: rgba(130,80,255,0.04);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        h1, h2, h3, h4, h5 { font-family: 'Syne', sans-serif; line-height: 1.2; }

        /* ═══════════════════════════════════════
           SCROLLBAR
        ═══════════════════════════════════════ */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--bg4); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* ═══════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════ */
        .admin-layout { display: flex; min-height: 100vh; }

        .admin-layout::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--grid-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* ═══════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════ */
        .admin-sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 200;
            padding: 18px 10px 16px;
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .admin-sidebar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0.6;
        }

        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-w);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* ═══════════════════════════════════════
           TOPBAR
        ═══════════════════════════════════════ */
        .admin-topbar {
            height: var(--topbar-h);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            background: rgba(7,7,10,0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-content {
            flex: 1;
            padding: 28px 24px;
            max-width: 1400px;
            width: 100%;
        }

        /* ═══════════════════════════════════════
           LOGO
        ═══════════════════════════════════════ */
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 9px;
            text-decoration: none;
            margin-bottom: 24px;
            padding: 8px 8px;
            border-radius: var(--radius-sm);
            transition: background 0.15s;
        }
        .admin-logo:hover { background: var(--bg3); }

        .admin-logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--danger) 0%, #c0283a 100%);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800; font-size: 13px; color: #fff;
            box-shadow: 0 0 16px rgba(255,64,85,0.35);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        .admin-logo-icon::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 30px; height: 30px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
        }

        .admin-logo-name { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 14px; letter-spacing: -0.01em; }
        .admin-logo-badge {
            display: inline-block;
            font-size: 8px;
            background: rgba(255,64,85,0.15);
            color: var(--danger);
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 700;
            letter-spacing: 0.08em;
            border: 1px solid rgba(255,64,85,0.25);
            margin-top: 2px;
        }

        /* ═══════════════════════════════════════
           NAV
        ═══════════════════════════════════════ */
        .nav-group-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 10px;
            margin: 20px 0 5px;
        }
        .nav-group-label:first-of-type { margin-top: 4px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            transition: all 0.12s ease;
            position: relative;
            white-space: nowrap;
        }
        .nav-item:hover {
            background: var(--bg3);
            color: var(--text);
        }
        .nav-item.active {
            background: rgba(255,64,85,0.08);
            color: var(--danger);
            border: 1px solid rgba(255,64,85,0.15);
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 25%; bottom: 25%;
            width: 2px;
            background: var(--danger);
            border-radius: 0 2px 2px 0;
        }
        .nav-item svg { flex-shrink: 0; width: 14px; height: 14px; opacity: 0.9; }

        .nav-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 99px;
            min-width: 17px;
            text-align: center;
            line-height: 1.6;
        }
        .nav-badge.warning { background: var(--warning); color: #0d0d0f; }

        .nav-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 12px 0;
        }

        .ai-dot {
            width: 6px; height: 6px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
            flex-shrink: 0;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34,212,160,0.4); }
            50% { opacity: 0.8; box-shadow: 0 0 0 4px rgba(34,212,160,0); }
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--border);
        }

        /* ═══════════════════════════════════════
           TOPBAR ELEMENTS
        ═══════════════════════════════════════ */
        .topbar-title {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 14px;
            flex: 1;
            color: var(--text);
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--text-muted);
        }
        .topbar-breadcrumb span { color: var(--text-dim); }
        .topbar-breadcrumb .sep { color: var(--border-hover); }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            color: var(--text-dim);
        }
        .admin-user-name { display: none; }

        .admin-avatar {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--danger) 0%, #a0203a 100%);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800; font-size: 11px; color: #fff;
            flex-shrink: 0;
            box-shadow: 0 0 12px rgba(255,64,85,0.25);
        }

        .sidebar-toggle {
            display: none;
            width: 36px; height: 36px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-toggle svg { width: 16px; height: 16px; color: var(--text-muted); }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 150;
        }

        /* ═══════════════════════════════════════
           BUTTONS
        ═══════════════════════════════════════ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 15px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.14s ease;
            white-space: nowrap;
            letter-spacing: -0.01em;
            line-height: 1;
        }
        .btn svg { flex-shrink: 0; }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 0 20px var(--accent-glow);
        }
        .btn-primary:hover {
            background: var(--accent-bright);
            box-shadow: 0 0 28px rgba(130,80,255,0.4);
            transform: translateY(-1px);
        }
        .btn-danger {
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid rgba(255,64,85,0.2);
        }
        .btn-danger:hover { background: rgba(255,64,85,0.18); }
        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { border-color: var(--border-hover); color: var(--text); background: var(--bg3); }
        .btn-warning {
            background: var(--warning-dim);
            color: var(--warning);
            border: 1px solid rgba(255,170,0,0.2);
        }
        .btn-warning:hover { background: rgba(255,170,0,0.18); }
        .btn-info {
            background: var(--info-dim);
            color: var(--info);
            border: 1px solid rgba(59,156,245,0.2);
        }
        .btn-info:hover { background: rgba(59,156,245,0.18); }
        .btn-success {
            background: var(--success-dim);
            color: var(--success);
            border: 1px solid rgba(34,212,160,0.2);
        }
        .btn-success:hover { background: rgba(34,212,160,0.18); }
        .btn-sm { padding: 6px 11px; font-size: 12px; }
        .btn-xs { padding: 4px 9px; font-size: 11px; border-radius: var(--radius-xs); }
        .btn:active { transform: translateY(0) scale(0.98); }

        /* ═══════════════════════════════════════
           CARDS
        ═══════════════════════════════════════ */
        .card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        /* ═══════════════════════════════════════
           STATS GRID — Responsive
        ═══════════════════════════════════════ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-box {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.15s, transform 0.15s;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .stat-box:hover {
            border-color: var(--border-hover);
            transform: translateY(-2px);
        }
        .stat-box::after {
            content: '';
            position: absolute;
            bottom: -16px; right: -16px;
            width: 72px; height: 72px;
            border-radius: 50%;
            background: radial-gradient(ellipse, var(--accent-dim), transparent 70%);
            pointer-events: none;
        }

        .stat-box-label {
            font-size: 9px;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .stat-box-value {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        .stat-box-sub { font-size: 11px; color: var(--text-muted); margin-top: 6px; }

        /* ═══════════════════════════════════════
           TABLE
        ═══════════════════════════════════════ */
        .table-wrap {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        table { width: 100%; border-collapse: collapse; min-width: 500px; }

        th {
            text-align: left;
            padding: 10px 16px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            background: var(--bg3);
            white-space: nowrap;
        }
        td {
            padding: 12px 16px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255,255,255,0.025);
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.012); }

        /* ═══════════════════════════════════════
           BADGES
        ═══════════════════════════════════════ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .badge-green   { background: rgba(34,212,160,0.1); color: var(--success); border: 1px solid rgba(34,212,160,0.2); }
        .badge-red     { background: var(--danger-dim); color: var(--danger); border: 1px solid rgba(255,64,85,0.2); }
        .badge-yellow  { background: var(--warning-dim); color: var(--warning); border: 1px solid rgba(255,170,0,0.2); }
        .badge-blue    { background: var(--info-dim); color: var(--info); border: 1px solid rgba(59,156,245,0.2); }
        .badge-gray    { background: var(--bg3); border: 1px solid var(--border); color: var(--text-muted); }
        .badge-purple  { background: var(--accent-dim); color: var(--accent-bright); border: 1px solid rgba(130,80,255,0.2); }
        .badge-premium { background: linear-gradient(135deg, #f8b803, #ff6b35); color: #08080a; border: none; font-weight: 800; }

        /* ═══════════════════════════════════════
           ALERTS
        ═══════════════════════════════════════ */
        .alert {
            padding: 11px 14px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }
        .alert svg { flex-shrink: 0; margin-top: 1px; }
        .alert-success { background: rgba(34,212,160,0.07); border: 1px solid rgba(34,212,160,0.2); color: var(--success); }
        .alert-error   { background: var(--danger-dim); border: 1px solid rgba(255,64,85,0.2); color: var(--danger); }
        .alert-warning { background: var(--warning-dim); border: 1px solid rgba(255,170,0,0.2); color: var(--warning); }
        .alert-info    { background: var(--info-dim); border: 1px solid rgba(59,156,245,0.2); color: var(--info); }

        /* ═══════════════════════════════════════
           FORMS
        ═══════════════════════════════════════ */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-dim);
            margin-bottom: 7px;
            letter-spacing: 0.03em;
        }
        .form-label .required { color: var(--danger); margin-left: 2px; }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 9px 12px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.14s, box-shadow 0.14s;
            appearance: none;
            -webkit-appearance: none;
        }
        .form-input::placeholder, .form-textarea::placeholder { color: var(--text-muted); }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: rgba(130,80,255,0.45);
            box-shadow: 0 0 0 3px rgba(130,80,255,0.08);
            background: var(--bg4);
        }
        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235e5e6e' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
        }
        .form-select option { background: var(--bg3); color: var(--text); }
        .form-textarea { resize: vertical; min-height: 90px; line-height: 1.5; }
        .form-error { font-size: 11px; color: var(--danger); margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        .form-hint  { font-size: 11px; color: var(--text-muted); margin-top: 5px; line-height: 1.4; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

        /* ═══════════════════════════════════════
           TOGGLE
        ═══════════════════════════════════════ */
        .toggle-wrap { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
        .toggle { position: relative; width: 36px; height: 20px; flex-shrink: 0; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 20px;
            transition: 0.18s ease;
        }
        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 14px; height: 14px;
            left: 2px; top: 2px;
            background: var(--text-muted);
            border-radius: 50%;
            transition: 0.18s ease;
        }
        .toggle input:checked + .toggle-slider {
            background: var(--accent-dim);
            border-color: rgba(130,80,255,0.4);
        }
        .toggle input:checked + .toggle-slider:before {
            transform: translateX(16px);
            background: var(--accent-bright);
            box-shadow: 0 0 8px var(--accent-glow);
        }

        /* ═══════════════════════════════════════
           FILTERS BAR
        ═══════════════════════════════════════ */
        .filters-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .filters-bar .form-input,
        .filters-bar .form-select { width: auto; min-width: 0; }

        /* ═══════════════════════════════════════
           PAGINATION
        ═══════════════════════════════════════ */
        .pagination { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; }
        .page-link {
            padding: 6px 11px;
            border-radius: var(--radius-xs);
            font-size: 12px;
            text-decoration: none;
            color: var(--text-muted);
            border: 1px solid var(--border);
            transition: all 0.12s;
            font-weight: 500;
        }
        .page-link:hover { border-color: var(--border-hover); color: var(--text); background: var(--bg3); }
        .page-link.active { background: var(--accent); color: #fff; border-color: var(--accent); font-weight: 700; box-shadow: 0 0 16px var(--accent-glow); }

        /* ═══════════════════════════════════════
           AI CHIP
        ═══════════════════════════════════════ */
        .ai-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            background: var(--accent-dim);
            border: 1px solid rgba(130,80,255,0.2);
            border-radius: 99px;
            font-size: 10px;
            font-weight: 600;
            color: var(--accent-bright);
            letter-spacing: 0.04em;
        }
        .ai-chip-dot {
            width: 5px; height: 5px;
            background: var(--accent-bright);
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }

        /* ═══════════════════════════════════════
           UTILITIES
        ═══════════════════════════════════════ */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .justify-end { justify-content: flex-end; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-1 { gap: 4px; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .mt-1{margin-top:4px;} .mt-2{margin-top:8px;} .mt-3{margin-top:12px;}
        .mt-4{margin-top:16px;} .mt-6{margin-top:24px;}
        .mb-2{margin-bottom:8px;} .mb-3{margin-bottom:12px;}
        .mb-4{margin-bottom:16px;} .mb-6{margin-bottom:24px;}
        .text-xs{font-size:11px;} .text-sm{font-size:12px;} .text-base{font-size:13px;}
        .text-muted{color:var(--text-muted);} .text-dim{color:var(--text-dim);}
        .text-accent{color:var(--accent-bright);}
        .text-danger{color:var(--danger);}
        .text-warning{color:var(--warning);}
        .text-success{color:var(--success);}
        .font-bold{font-weight:700;} .font-semibold{font-weight:600;}
        .w-full{width:100%;}
        .truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .divider{border:none;border-top:1px solid var(--border);margin:20px 0;}
        .rounded{border-radius:var(--radius-sm);}
        .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0;}

        @keyframes spin{to{transform:rotate(360deg);}}
        .spinner{width:16px;height:16px;border:2px solid var(--bg3);border-top-color:var(--accent);border-radius:50%;animation:spin 0.6s linear infinite;}

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .section-title .count {
            font-size: 13px;
            font-weight: 400;
            color: var(--text-muted);
            margin-left: 6px;
            font-family: 'DM Sans', sans-serif;
        }

        /* ═══════════════════════════════════════
           RESPONSIVE
        ═══════════════════════════════════════ */

        /* Tablet — sidebar collapse */
        @media (max-width: 900px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.open {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(0,0,0,0.6);
            }
            .sidebar-overlay.open { display: block; }
            .admin-main { margin-left: 0; }
            .sidebar-toggle { display: flex; }
            .admin-user-name { display: block; }
            .admin-content { padding: 20px 16px; }
            .admin-topbar { padding: 0 16px; }
            .form-grid-2 { grid-template-columns: 1fr; gap: 12px; }
            .form-grid-3 { grid-template-columns: 1fr 1fr; gap: 12px; }
        }

        /* Stats grid responsive */
        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 860px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        /* Mobile */
        @media (max-width: 580px) {
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-box-value { font-size: 20px; }
            .stat-box { padding: 14px 16px; }
            .filters-bar { gap: 6px; }
            .filters-bar .form-input,
            .filters-bar .form-select { min-width: 100%; }
            .form-grid-3 { grid-template-columns: 1fr; }
            .section-header { gap: 8px; }
            .topbar-title { font-size: 13px; }
            .admin-content { padding: 16px 12px; }
            .admin-topbar { padding: 0 12px; gap: 8px; }
            .col-hide-mobile { display: none; }
        }

        @media (max-width: 400px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        @stack('styles')
    </style>
</head>
<body>

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="admin-layout">

    {{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
    <aside class="admin-sidebar" id="adminSidebar">

        <a href="{{ route('admin.dashboard') }}" class="admin-logo">
            <div class="admin-logo-icon">K</div>
            <div>
                <div class="admin-logo-name">Kemasify</div>
                <div class="admin-logo-badge">ADMIN</div>
            </div>
        </a>

        <div style="padding: 0 4px; margin-bottom: 16px;">
            <div class="ai-chip">
                <span class="ai-chip-dot"></span>
                AI Engine Online
            </div>
        </div>

        <div class="nav-group-label">Overview</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        <div class="nav-group-label">Manajemen</div>
        <a href="{{ route('admin.users.index') }}"
           class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Users
        </a>
        <a href="{{ route('admin.product-models.index') }}"
           class="nav-item {{ request()->routeIs('admin.product-models.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Product Models
        </a>

        <div class="nav-group-label">Jobs</div>
        <a href="{{ route('admin.ai-jobs.index') }}"
           class="nav-item {{ request()->routeIs('admin.ai-jobs.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            AI Jobs
            @php $pendingAi = \App\Models\AiGenerationJob::where('status','pending')->count(); @endphp
            @if($pendingAi > 0)
                <span class="nav-badge warning">{{ $pendingAi }}</span>
            @endif
        </a>
        <a href="{{ route('admin.render-jobs.index') }}"
           class="nav-item {{ request()->routeIs('admin.render-jobs.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            Render Jobs
            @php $pendingRender = \App\Models\RenderJob::where('status','pending')->count(); @endphp
            @if($pendingRender > 0)
                <span class="nav-badge">{{ $pendingRender }}</span>
            @endif
        </a>

        <div class="nav-group-label">Revenue</div>
        <a href="{{ route('admin.orders.index') }}"
           class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <path d="M1 10h22"/>
            </svg>
            Orders & Payments
            @php $pendingOrders = \App\Models\Order::where('status','pending')->count(); @endphp
            @if($pendingOrders > 0)
                <span class="nav-badge warning">{{ $pendingOrders }}</span>
            @endif
        </a>

        <div class="sidebar-footer">
            <a href="{{ route('dashboard') }}" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Kembali ke App
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item w-full"
                    style="background:none;border:none;cursor:pointer;text-align:left;color:var(--text-muted);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════════════════════ MAIN ═══════════════════════ --}}
    <div class="admin-main">

        <header class="admin-topbar">
            <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

            <div class="topbar-title">@yield('page-title', 'Admin Panel')</div>

            <div class="admin-user">
                <div class="admin-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <span class="admin-user-name">{{ auth()->user()->name }}</span>
            </div>
        </header>

        <main class="admin-content">
            @if(session('success'))
            <div class="alert alert-success">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    }
    function closeSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) closeSidebar();
    });
</script>

@stack('scripts')
</body>
</html>`