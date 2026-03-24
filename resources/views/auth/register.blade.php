<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Kemasify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d0f; --bg2: #131316; --bg3: #1a1a1f;
            --border: rgba(255,255,255,0.08); --border-focus: rgba(200,245,66,0.5);
            --text: #f0f0f2; --text-muted: #7a7a85;
            --accent: #c8f542; --danger: #ff4757;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            -webkit-font-smoothing: antialiased; padding: 40px 20px;
        }
        body::before {
            content: ''; position: fixed; top: -30%; left: 50%; transform: translateX(-50%);
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(200,245,66,0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        .auth-wrap { width: 100%; max-width: 420px; }
        .auth-logo {
            display: flex; align-items: center; gap: 10px; justify-content: center;
            margin-bottom: 40px; text-decoration: none;
        }
        .auth-logo-icon { width: 36px; height: 36px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .auth-logo-text { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 20px; color: var(--text); }
        .auth-card { background: var(--bg2); border: 1px solid var(--border); border-radius: 20px; padding: 40px; }
        h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; margin-bottom: 8px; }
        .auth-subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 8px; }
        .token-banner {
            display: flex; align-items: center; gap: 10px;
            background: rgba(200,245,66,0.08); border: 1px solid rgba(200,245,66,0.2);
            border-radius: 9px; padding: 10px 14px;
            font-size: 13px; color: var(--accent);
            margin-bottom: 24px;
        }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; padding: 11px 14px;
            background: var(--bg3); border: 1px solid var(--border); border-radius: 9px;
            color: var(--text); font-size: 14px; font-family: 'DM Sans', sans-serif; outline: none;
            transition: border-color 0.15s;
        }
        input:focus { border-color: var(--border-focus); }
        .form-error { font-size: 12px; color: var(--danger); margin-top: 5px; }
        .btn-submit {
            width: 100%; padding: 13px; background: var(--accent); border: none; border-radius: 9px;
            color: #0d0d0f; font-size: 15px; font-weight: 700; font-family: 'Syne', sans-serif;
            cursor: pointer; transition: all 0.2s; margin-top: 8px;
        }
        .btn-submit:hover { background: #d4f755; transform: translateY(-1px); }
        .auth-footer { text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-muted); }
        .auth-footer a { color: var(--accent); text-decoration: none; }
        .alert-error {
            background: rgba(255,71,87,0.1); border: 1px solid rgba(255,71,87,0.3);
            color: var(--danger); padding: 12px 14px; border-radius: 9px; font-size: 13px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <a href="{{ route('home') }}" class="auth-logo">
        <div class="auth-logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0d0d0f" stroke-width="2.5">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <span class="auth-logo-text">Kemasify</span>
    </a>

    <div class="auth-card">
        <h1>Buat Akun Gratis ✦</h1>
        <p class="auth-subtitle">Desain packaging produkmu dengan AI</p>
        <div class="token-banner">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Dapat <strong>50 token gratis</strong> langsung saat daftar!
        </div>

        @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kamu" required autocomplete="name">
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="kamu@email.com" required autocomplete="email">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 8 karakter, huruf & angka" required>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn-submit">Daftar Sekarang →</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Login</a>
        </div>
    </div>
</div>
</body>
</html>