<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kemasify — AI Packaging Design Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d0f; --bg2: #131316; --bg3: #1a1a1f;
            --border: rgba(255,255,255,0.08);
            --text: #f0f0f2; --text-muted: #7a7a85;
            --accent: #c8f542; --accent-hover: #d4f755;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }
        h1,h2,h3 { font-family: 'Syne', sans-serif; line-height: 1.15; }

        /* Nav */
        nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 48px;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; background: rgba(13,13,15,0.85);
            backdrop-filter: blur(16px); z-index: 100;
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo-icon {
            width: 32px; height: 32px; background: var(--accent); border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-logo-text { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 17px; color: var(--text); }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .btn-outline {
            padding: 8px 18px; border: 1px solid var(--border); border-radius: 8px;
            color: var(--text-muted); text-decoration: none; font-size: 14px;
            transition: all 0.15s;
        }
        .btn-outline:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }
        .btn-accent {
            padding: 8px 18px; background: var(--accent); border-radius: 8px;
            color: #0d0d0f; text-decoration: none; font-size: 14px; font-weight: 600;
            transition: all 0.15s;
        }
        .btn-accent:hover { background: var(--accent-hover); }

        /* Hero */
        .hero {
            padding: 120px 48px 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -200px; left: 50%;
            transform: translateX(-50%);
            width: 800px; height: 600px;
            background: radial-gradient(circle, rgba(200,245,66,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 99px;
            border: 1px solid rgba(200,245,66,0.3);
            background: rgba(200,245,66,0.08);
            color: var(--accent); font-size: 13px; margin-bottom: 32px;
        }
        .hero-badge::before { content: '●'; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
        .hero h1 {
            font-size: clamp(52px, 8vw, 96px);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 24px;
        }
        .hero h1 span { color: var(--accent); }
        .hero p {
            font-size: 18px; color: var(--text-muted);
            max-width: 560px; margin: 0 auto 48px;
            line-height: 1.7;
        }
        .hero-cta { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .btn-hero {
            padding: 14px 32px; background: var(--accent); border-radius: 10px;
            color: #0d0d0f; text-decoration: none; font-size: 16px; font-weight: 700;
            font-family: 'Syne', sans-serif;
            transition: all 0.2s;
        }
        .btn-hero:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(200,245,66,0.3); }
        .btn-hero-ghost {
            padding: 14px 32px; border: 1px solid var(--border); border-radius: 10px;
            color: var(--text); text-decoration: none; font-size: 16px;
            transition: all 0.2s;
        }
        .btn-hero-ghost:hover { border-color: rgba(255,255,255,0.2); }

        /* Mockup preview */
        .hero-visual {
            margin: 80px auto 0;
            max-width: 900px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            position: relative;
        }
        .mockup-dots {
            display: flex; gap: 6px; margin-bottom: 20px;
        }
        .mockup-dot { width: 10px; height: 10px; border-radius: 50%; }
        .mockup-dot:nth-child(1) { background: #ff5f56; }
        .mockup-dot:nth-child(2) { background: #ffbd2e; }
        .mockup-dot:nth-child(3) { background: #27c93f; }
        .mockup-screen {
            background: var(--bg3);
            border-radius: 12px;
            height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            overflow: hidden;
        }
        .mockup-card {
            flex: 1;
            height: 320px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            color: var(--text-muted);
            flex-direction: column;
            gap: 12px;
        }
        .mockup-card svg { opacity: 0.3; }

        /* Features */
        .section { padding: 80px 48px; }
        .section-label {
            font-size: 12px; font-weight: 600; letter-spacing: 0.1em;
            text-transform: uppercase; color: var(--accent);
            margin-bottom: 12px;
        }
        .section-title { font-size: clamp(32px, 4vw, 48px); font-weight: 800; margin-bottom: 60px; }
        .features-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
            max-width: 1100px; margin: 0 auto;
        }
        .feature-card {
            background: var(--bg2); border: 1px solid var(--border);
            border-radius: 16px; padding: 28px;
        }
        .feature-icon {
            width: 44px; height: 44px; background: var(--accent-dim);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px;
        }
        .feature-icon svg { color: var(--accent); }
        :root { --accent-dim: rgba(200,245,66,0.12); }
        .feature-card h3 { font-size: 17px; font-weight: 700; margin-bottom: 10px; }
        .feature-card p { font-size: 14px; color: var(--text-muted); line-height: 1.7; }

        /* Pricing */
        .pricing-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
            max-width: 740px; margin: 0 auto;
        }
        .pricing-card {
            background: var(--bg2); border: 1px solid var(--border);
            border-radius: 20px; padding: 36px;
        }
        .pricing-card.featured {
            border-color: var(--accent);
            background: linear-gradient(135deg, rgba(200,245,66,0.05), var(--bg2));
        }
        .pricing-plan { font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 8px; }
        .pricing-price {
            font-family: 'Syne', sans-serif;
            font-size: 44px; font-weight: 800; margin-bottom: 4px;
        }
        .pricing-price span { font-size: 16px; color: var(--text-muted); font-weight: 400; }
        .pricing-desc { font-size: 14px; color: var(--text-muted); margin-bottom: 28px; }
        .pricing-features { list-style: none; margin-bottom: 28px; }
        .pricing-features li {
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; padding: 7px 0;
            border-bottom: 1px solid var(--border);
        }
        .pricing-features li:last-child { border-bottom: none; }
        .pricing-features li::before { content: '✓'; color: var(--accent); font-weight: 700; }
        .btn-pricing-free {
            display: block; text-align: center; padding: 12px;
            border: 1px solid var(--border); border-radius: 10px;
            color: var(--text); text-decoration: none; font-weight: 600; transition: all 0.15s;
        }
        .btn-pricing-free:hover { border-color: rgba(255,255,255,0.2); }
        .btn-pricing-premium {
            display: block; text-align: center; padding: 12px;
            background: var(--accent); border-radius: 10px;
            color: #0d0d0f; text-decoration: none; font-weight: 700; transition: all 0.2s;
        }
        .btn-pricing-premium:hover { background: var(--accent-hover); }

        /* CTA */
        .cta-section {
            padding: 100px 48px;
            text-align: center;
            background: linear-gradient(180deg, transparent, rgba(200,245,66,0.04));
        }
        .cta-section h2 { font-size: clamp(36px, 5vw, 60px); font-weight: 800; margin-bottom: 16px; }
        .cta-section p { font-size: 17px; color: var(--text-muted); margin-bottom: 40px; }

        /* Footer */
        footer {
            border-top: 1px solid var(--border);
            padding: 32px 48px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }
    </style>
</head>
<body>

<nav>
    <a href="#" class="nav-logo">
        <div class="nav-logo-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0d0d0f" stroke-width="2.5">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <span class="nav-logo-text">Kemasify</span>
    </a>
    <div class="nav-links">
        <a href="{{ route('login') }}" class="btn-outline">Masuk</a>
        <a href="{{ route('register') }}" class="btn-accent">Mulai Gratis</a>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-badge">AI Packaging Design Platform</div>
    <h1>Desain Packaging<br><span>Produkmu</span> dengan AI</h1>
    <p>Upload desain atau generate dengan AI, lalu render menjadi mockup 3D profesional. Dalam hitungan menit.</p>
    <div class="hero-cta">
        <a href="{{ route('register') }}" class="btn-hero">Coba Gratis — 50 Token</a>
        <a href="{{ route('login') }}" class="btn-hero-ghost">Login</a>
    </div>

    <div class="hero-visual">
        <div class="mockup-dots">
            <div class="mockup-dot"></div>
            <div class="mockup-dot"></div>
            <div class="mockup-dot"></div>
        </div>
        <div class="mockup-screen">
            <div class="mockup-card">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Pilih Model Produk</span>
            </div>
            <div style="font-size:24px; color: var(--text-muted);">→</div>
            <div class="mockup-card">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
                <span>Generate AI Design</span>
            </div>
            <div style="font-size:24px; color: var(--text-muted);">→</div>
            <div class="mockup-card" style="background: linear-gradient(135deg, rgba(200,245,66,0.1), rgba(200,245,66,0.02)); border-color: rgba(200,245,66,0.2);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#c8f542" stroke-width="1.5">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
                <span style="color: #c8f542;">Render 3D Mockup</span>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section" style="text-align:center;">
    <div class="section-label">Fitur Utama</div>
    <div class="section-title">Semua yang kamu butuhkan</div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <h3>AI Design Generator</h3>
            <p>Describe desain packaging impianmu, Gemini AI akan membuatnya dalam detik. Style minimalis hingga bold.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
            </div>
            <h3>3D Packaging Render</h3>
            <p>Wrap desainmu ke model 3D box, botol, pouch, atau kaleng. Hasil render photorealistic langsung bisa diunduh.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
                </svg>
            </div>
            <h3>Canvas Editor</h3>
            <p>Edit teks, warna, dan layout langsung di browser. Upload desainmu atau mulai dari hasil AI generation.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <h3>Token System</h3>
            <p>50 token gratis setiap 24 jam. Generate desain = 10 token, render 3D = 50 token. Upgrade untuk unlimited.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
            </div>
            <h3>Priority Queue</h3>
            <p>User premium langsung diproses, user gratis masuk antrian. Semua mendapat hasil yang sama berkualitas.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                </svg>
            </div>
            <h3>Export & Download</h3>
            <p>Download desain flat gratis. Hasil render 3D tersedia untuk user premium dalam format PNG resolusi tinggi.</p>
        </div>
    </div>
</section>

<!-- Pricing -->
<section class="section" style="text-align:center;">
    <div class="section-label">Harga</div>
    <div class="section-title">Mulai gratis, upgrade kapanpun</div>
    <div class="pricing-grid">
        <div class="pricing-card">
            <div class="pricing-plan">FREE</div>
            <div class="pricing-price">Rp0 <span>/bulan</span></div>
            <div class="pricing-desc">Sempurna untuk dicoba</div>
            <ul class="pricing-features">
                <li>50 token per 24 jam</li>
                <li>Generate desain AI (10 token)</li>
                <li>Model produk standar</li>
                <li>Download desain flat</li>
                <li>Queue processing</li>
            </ul>
            <a href="{{ route('register') }}" class="btn-pricing-free">Mulai Gratis</a>
        </div>
        <div class="pricing-card featured">
            <div class="pricing-plan" style="color:var(--accent)">✦ PREMIUM</div>
            <div class="pricing-price">Rp99K <span>/bulan</span></div>
            <div class="pricing-desc">Untuk profesional</div>
            <ul class="pricing-features">
                <li>Token unlimited</li>
                <li>Render 3D packaging (50 token)</li>
                <li>Semua model produk</li>
                <li>Download render 3D HD</li>
                <li>Priority processing (no queue)</li>
            </ul>
            <a href="{{ route('register') }}" class="btn-pricing-premium">Upgrade Premium</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2>Mulai desain<br>packaging-mu sekarang</h2>
    <p>Gratis. Tanpa kartu kredit. 50 token langsung tersedia.</p>
    <a href="{{ route('register') }}" class="btn-hero">Daftar Gratis →</a>
</section>

<footer>
    <p>© 2026 Kemasify · AI Packaging Design Platform</p>
</footer>

</body>
</html>