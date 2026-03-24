<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kemasify</title>
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
            font-family: 'DM Sans', sans-serif;
            background: var(--bg); color: var(--text);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            -webkit-font-smoothing: antialiased;
        }
        body::before {
            content: '';
            position: fixed; top: -30%; left: 50%; transform: translateX(-50%);
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(200,245,66,0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        .auth-wrap {
            width: 100%; max-width: 420px;
            padding: 20px;
        }
        .auth-logo {
            display: flex; align-items: center; gap: 10px; justify-content: center;
            margin-bottom: 40px; text-decoration: none;
        }
        .auth-logo-icon {
            width: 36px; height: 36px; background: var(--accent); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .auth-logo-text { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 20px; color: var(--text); }
        .auth-card {
            background: var(--bg2); border: 1px solid var(--border); border-radius: 20px; padding: 40px;
        }
        h1 { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; margin-bottom: 8px; }
        .auth-subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 32px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; padding: 11px 14px;
            background: var(--bg3); border: 1px solid var(--border);
            border-radius: 9px; color: var(--text); font-size: 14px;
            font-family: 'DM Sans', sans-serif; outline: none;
            transition: border-color 0.15s;
        }
        input:focus { border-color: var(--border-focus); }
        .form-error { font-size: 12px; color: var(--danger); margin-top: 6px; }
        .btn-submit {
            width: 100%; padding: 13px;
            background: var(--accent); border: none; border-radius: 9px;
            color: #0d0d0f; font-size: 15px; font-weight: 700;
            font-family: 'Syne', sans-serif; cursor: pointer;
            transition: all 0.2s; margin-top: 8px;
        }
        .btn-submit:hover { background: #d4f755; transform: translateY(-1px); }
        .auth-footer {
            text-align: center; margin-top: 24px;
            font-size: 14px; color: var(--text-muted);
        }
        .auth-footer a { color: var(--accent); text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
        .checkbox-row {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; color: var(--text-muted);
        }
        input[type="checkbox"] { accent-color: var(--accent); }
        .alert-error {
            background: rgba(255,71,87,0.1); border: 1px solid rgba(255,71,87,0.3);
            color: var(--danger); padding: 12px 14px; border-radius: 9px;
            font-size: 13px; margin-bottom: 20px;
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
        <h1>Selamat Datang 👋</h1>
        <p class="auth-subtitle">Masuk untuk mulai desain packaging-mu</p>

        @if($errors->any())
        <div class="alert-error">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    placeholder="kamu@email.com" required autocomplete="email">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                    placeholder="••••••••" required autocomplete="current-password">
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="checkbox-row">
                    <input type="checkbox" name="remember">
                    Ingat saya
                </label>
            </div>
            <button type="submit" class="btn-submit">Masuk →</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar gratis</a>
        </div>
    </div>
</div>
</body>
</html>