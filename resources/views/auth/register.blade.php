<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Kemasify</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d0f;
            --bg2: #131316;
            --bg3: #1a1a1f;
            --border: rgba(255,255,255,0.08);
            --border-focus: #6f37e7;
            --border-hover: rgba(111,55,231,0.36);
            --text: #f0f0f2;
            --text-muted: #7a7a85;
            --accent: #8952FF;
            --accent-hover: #783cf8;
            --danger: #ff4757;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
            padding: 24px;
        }

        .auth-card {
            display: flex;
            width: 100%;
            max-width: 860px;
            min-height: 600px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5);
        }

        /* ── Left: Image Panel ── */
        .auth-image {
            position: relative;
            width: 340px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 20px;
            margin: 8px;
        }

        .auth-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 16px;
        }

        .auth-image-overlay {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: linear-gradient(160deg, rgba(137,82,255,0.18) 0%, rgba(0,0,0,0.2) 100%);
        }

        .auth-image-logo {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-text {
            font-family: 'Clash Display', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #fff;
        }

        /* ── Right: Form Panel ── */
        .auth-form-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 44px 44px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 13px;
            text-decoration: none;
            margin-bottom: 24px;
            transition: color 0.15s;
        }
        .back-link:hover { color: var(--text); }

        h1 {
            font-family: 'Clash Display', sans-serif;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.15;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 13.5px;
            margin-bottom: 18px;
        }

        .auth-subtitle a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        .auth-subtitle a:hover { text-decoration: underline; }

        .token-banner {
            display: flex;
            align-items: center;
            gap: 9px;
            background: rgba(137,82,255,0.08);
            border: 1px solid rgba(137,82,255,0.22);
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--accent);
            margin-bottom: 22px;
        }

        .alert-error {
            background: rgba(255,71,87,0.1);
            border: 1px solid rgba(255,71,87,0.3);
            color: var(--danger);
            padding: 11px 14px;
            border-radius: 9px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-group { margin-bottom: 14px; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 7px;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 11px 14px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 9px;
            color: var(--text);
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.15s;
        }

        input:hover { border-color: var(--border-hover); }
        input:focus { border-color: var(--border-focus); }

        .form-error {
            font-size: 11.5px;
            color: var(--danger);
            margin-top: 5px;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--text);
            border: none;
            border-radius: 9px;
            color: #0d0d0f;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Clash Display', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 6px;
            letter-spacing: 0.01em;
        }

        .btn-submit:hover {
            background: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(255,255,255,0.1);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: var(--text-muted);
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .social-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 9px;
            color: var(--text);
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: border-color 0.15s, background 0.15s;
        }

        .btn-social:hover {
            border-color: var(--border-hover);
            background: #1f1f24;
        }

        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 14px;
        }

        .terms-row a {
            color: var(--text-muted);
            text-decoration: underline;
        }
        .terms-row a:hover { color: var(--text); }

        @media (max-width: 680px) {
            .auth-image { display: none; }
            .auth-form-wrap { padding: 36px 28px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    {{-- Left: Image Panel --}}
    <div class="auth-image">
        <img src="[register-hero-image]" alt="Kemasify visual">
        <div class="auth-image-overlay"></div>
        <a href="{{ route('home') }}" class="auth-image-logo">
            <div class="logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="logo-text">Kemasify</span>
        </a>
    </div>

    {{-- Right: Form Panel --}}
    <div class="auth-form-wrap">

        <a href="{{ route('home') }}" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Kembali
        </a>

        <h1>Create an Account</h1>
        <p class="auth-subtitle">
            Already have an account? <a href="{{ route('login') }}">Log in</a>
        </p>

        <div class="token-banner">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Dapat <strong>&nbsp;50 token gratis</strong>&nbsp;langsung saat daftar!
        </div>

        @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

                <div class="form-group">
    <label>Nama Lengkap</label>
    <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autocomplete="name">
    @error('name')
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="kamu@email.com" required autocomplete="email">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 8 karakter" required autocomplete="new-password">
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <div class="divider">or</div>

        <div class="social-btns">
            <a href="{{ route('google.redirect') }}" class="btn-social">
                <svg width="15" height="15" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Continue with Google
            </a>
            <a href="#" class="btn-social">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Continue with Facebook
            </a>
        </div>

        <div class="terms-row">
            <input type="checkbox" style="margin-top:2px; accent-color: var(--accent);">
            <span>Saya setuju dengan <a href="#">Syarat &amp; Ketentuan</a> yang berlaku</span>
        </div>

    </div>
</div>

</body>
</html>