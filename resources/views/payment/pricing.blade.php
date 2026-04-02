@extends('layouts.app')

@section('title', 'Upgrade & Beli Token')

@section('breadcrumb')
<span style="font-size:15px;font-weight:600;">Upgrade & Beli Token</span>
@endsection

@push('styles')
<style>
/* ─── Fonts & Base ─────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap');

/* ─── Layout ───────────────────────────────────────────────────────── */
.page-content{
    max-width: 100%;
}

.pricing-root {
    position: relative;
    max-width: 100%;
    padding-bottom: 80px;
}

/* ─── Ambient glow background ──────────────────────────────────────── */
.pricing-root::before {
    content: '';
    position: fixed;
    top: -20%; left: 30%;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(200,245,66,0.06) 0%, transparent 65%);
    pointer-events: none; z-index: 0;
    animation: drift 8s ease-in-out infinite alternate;
}
@keyframes drift {
    from { transform: translate(0,0) scale(1); }
    to   { transform: translate(-60px, 40px) scale(1.1); }
}

/* ─── Hero header ──────────────────────────────────────────────────── */
.pricing-hero {
    text-align: center;
    padding: 48px 20px 56px;
    position: relative;
    z-index: 1;
}
.hero-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 16px; border-radius: 99px;
    border: 1px solid rgba(200,245,66,0.25);
    background: rgba(200,245,66,0.07);
    color: var(--accent); font-size: 12px; font-weight: 600;
    letter-spacing: 0.08em; text-transform: uppercase;
    margin-bottom: 24px;
}
.hero-eyebrow::before { content:'●'; animation: blink 2s infinite; font-size:8px; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

.pricing-hero h1 {
    font-family: 'Syne', sans-serif;
    font-size: clamp(36px, 5vw, 60px);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -0.02em;
    margin-bottom: 16px;
}
.pricing-hero h1 em {
    font-style: normal;
    background: linear-gradient(135deg, #c8f542 0%, #8ef000 60%, #c8f542 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.pricing-hero p {
    color: var(--text-muted);
    font-size: 16px; max-width: 460px; margin: 0 auto 32px;
    line-height: 1.7;
}

/* ─── Current plan badge ───────────────────────────────────────────── */
.current-plan-strip {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 10px 18px;
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 99px;
    font-size: 13px;
}

/* ─── Mode toggle ──────────────────────────────────────────────────── */
.mode-toggle {
    display: flex; align-items: center; gap: 0;
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 5px;
    width: fit-content;
    margin: 0 auto 48px;
}
.mode-btn {
    padding: 10px 28px;
    border-radius: calc(var(--radius) - 2px);
    border: none; cursor: pointer;
    font-size: 14px; font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.2s;
    color: var(--text-muted);
    background: transparent;
    position: relative;
}
.mode-btn.active {
    background: var(--bg3);
    color: var(--text);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.mode-btn .mode-icon { margin-right: 6px; }

/* ─── Panels ───────────────────────────────────────────────────────── */
.mode-panel { display: none; animation: fadeUp 0.3s ease; }
.mode-panel.active { display: block; }
@keyframes fadeUp {
    from { opacity:0; transform:translateY(12px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ─── Premium plan cards ───────────────────────────────────────────── */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    margin-bottom: 40px;
}
.plan-card {
    position: relative;
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px 22px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
    text-align: center;
}
.plan-card::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(200,245,66,0.04), transparent);
    opacity: 0; transition: opacity 0.2s;
}
.plan-card:hover { border-color: rgba(200,245,66,0.3); transform: translateY(-3px); }
.plan-card:hover::before { opacity: 1; }
.plan-card.selected {
    border-color: var(--accent);
    background: linear-gradient(135deg, rgba(200,245,66,0.08), var(--bg2));
    box-shadow: 0 0 0 1px rgba(200,245,66,0.3), 0 20px 40px rgba(0,0,0,0.3);
    transform: translateY(-4px);
}
.plan-card.popular {
    border-color: rgba(200,245,66,0.35);
}
.popular-pill {
    position: absolute; top: 14px; right: 14px;
    background: var(--accent); color: #0d0d0f;
    font-size: 9px; font-weight: 800; padding: 3px 8px;
    border-radius: 99px; letter-spacing: 0.06em; text-transform: uppercase;
}
.plan-duration {
    font-size: 12px; color: var(--text-muted);
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;
    margin-bottom: 14px;
}
.plan-price-num {
    font-family: 'Syne', sans-serif;
    font-size: 32px; font-weight: 800; line-height: 1;
    margin-bottom: 2px;
}
.plan-price-label { font-size: 11px; color: var(--text-muted); }
.plan-save {
    display: inline-flex; margin-top: 10px;
    background: rgba(200,245,66,0.12);
    color: var(--accent); font-size: 11px; font-weight: 700;
    padding: 3px 9px; border-radius: 99px;
}
.plan-check-wrap {
    margin-top: 16px;
    width: 28px; height: 28px;
    border: 2px solid var(--border);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin-left: auto; margin-right: auto;
    transition: all 0.2s;
}
.plan-card.selected .plan-check-wrap {
    background: var(--accent); border-color: var(--accent);
}
.plan-check-wrap svg { opacity: 0; transition: opacity 0.15s; }
.plan-card.selected .plan-check-wrap svg { opacity: 1; }

/* ─── Feature columns ──────────────────────────────────────────────── */
.features-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 32px;
}
.features-col {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
}
.features-col-title {
    font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--text-muted); margin-bottom: 16px;
}
.feat-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 13px;
}
.feat-item:last-child { border-bottom: none; padding-bottom: 0; }
.feat-icon {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 14px;
}
.feat-icon.green { background: rgba(200,245,66,0.12); }
.feat-icon.orange { background: rgba(255,165,2,0.12); }
.feat-icon.blue { background: rgba(61,156,245,0.12); }
.feat-title { font-weight: 600; }
.feat-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* ─── Order summary card ───────────────────────────────────────────── */
.order-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px;
    position: sticky; top: 80px;
}
.order-card-title {
    font-family: 'Syne', sans-serif;
    font-size: 15px; font-weight: 800; margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between;
}
.order-line {
    display: flex; justify-content: space-between;
    padding: 10px 0; font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.order-line:last-of-type { border-bottom: none; }
.order-total-line {
    display: flex; justify-content: space-between;
    padding: 16px 0 0; font-size: 18px; font-weight: 800;
    font-family: 'Syne', sans-serif; color: var(--accent);
    margin-top: 8px;
    border-top: 1px solid rgba(200,245,66,0.2);
}
.pay-btn {
    width: 100%; padding: 15px;
    background: var(--accent);
    border: none; border-radius: 12px;
    color: #0d0d0f; font-size: 15px; font-weight: 800;
    font-family: 'Syne', sans-serif;
    cursor: pointer; margin-top: 18px;
    transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.pay-btn:hover:not(:disabled) {
    background: #d4f755;
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(200,245,66,0.25);
}
.pay-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

.trust-badges {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; margin-top: 14px; flex-wrap: wrap;
}
.trust-badge {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; color: var(--text-muted);
}

/* ─── Two column layout ────────────────────────────────────────────── */
.two-col {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 28px;
    align-items: start;
}

/* ─── Token panel ──────────────────────────────────────────────────── */
.token-hero-card {
    background: linear-gradient(135deg, rgba(200,245,66,0.07), rgba(200,245,66,0.02));
    border: 1px solid rgba(200,245,66,0.2);
    border-radius: 18px;
    padding: 36px 32px;
    text-align: center;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}
.token-hero-card::after {
    content: '';
    position: absolute;
    top: -50%; right: -20%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(200,245,66,0.08), transparent 60%);
    pointer-events: none;
}
.token-big-num {
    font-family: 'Syne', sans-serif;
    font-size: 88px; font-weight: 800;
    color: var(--accent); line-height: 1;
    margin-bottom: 4px;
    transition: all 0.1s;
}
.token-big-label {
    font-size: 16px; color: var(--text-muted);
    margin-bottom: 28px;
}
/* Custom range */
.range-wrap { position: relative; padding: 0 4px; }
input[type="range"] {
    -webkit-appearance: none;
    width: 100%; height: 6px;
    background: transparent;
    outline: none; cursor: pointer;
    position: absolute;
    top: 0px;
    left: 0px;
}
.range-track {
    position: relative; height: 6px;
    background: var(--bg3); border-radius: 99px; margin-bottom: 8px;
}
.range-fill {
    position: absolute; left: 0; top: 0; height: 6px;
    background: linear-gradient(90deg, #8ef000, #c8f542);
    border-radius: 99px; pointer-events: none;
    transition: width 0.05s;
}
input[type="range"]::-webkit-slider-runnable-track {
    height: 6px; border-radius: 99px; background: transparent;
}
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 24px; height: 24px;
    background: var(--accent);
    border-radius: 50%;
    border: 3px solid var(--bg);
    box-shadow: 0 0 0 2px rgba(200,245,66,0.3);
    cursor: pointer;
    margin-top: -9px;
    transition: box-shadow 0.15s;
}
input[type="range"]::-webkit-slider-thumb:hover {
    box-shadow: 0 0 0 5px rgba(200,245,66,0.2);
}
.range-labels {
    display: flex; justify-content: space-between;
    font-size: 11px; color: var(--text-muted);
}

/* ─── Token stats ──────────────────────────────────────────────────── */
.token-stats {
    display: grid; grid-template-columns: 1fr 1fr 1fr;
    gap: 10px; margin: 20px 0;
}
.token-stat {
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 12px; padding: 14px 12px; text-align: center;
}
.token-stat-val {
    font-family: 'Syne', sans-serif;
    font-size: 22px; font-weight: 800;
    transition: all 0.1s;
}
.token-stat-label { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

/* ─── Preset chips ─────────────────────────────────────────────────── */
.preset-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
.preset-chip {
    flex: 1; min-width: 80px;
    padding: 10px 8px; border-radius: 10px;
    border: 1px solid var(--border);
    background: transparent; cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-muted); transition: all 0.15s;
    text-align: center;
}
.preset-chip:hover { border-color: var(--border-hover); color: var(--text); }
.preset-chip.active {
    border-color: var(--accent);
    background: rgba(200,245,66,0.08);
    color: var(--accent);
}
.preset-chip-amount { font-weight: 700; font-size: 14px; display: block; }
.preset-chip-price { font-size: 11px; color: inherit; opacity: 0.7; }

/* ─── Usage guide ──────────────────────────────────────────────────── */
.usage-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.usage-card {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: 12px; padding: 18px 16px;
    display: flex; align-items: center; gap: 14px;
}
.usage-num {
    font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 800;
    line-height: 1; flex-shrink: 0;
}
.usage-desc { font-size: 13px; }
.usage-desc strong { display: block; margin-bottom: 2px; }
.usage-desc span { color: var(--text-muted); font-size: 12px; }

/* ─── Recent orders mini ───────────────────────────────────────────── */
.recent-orders {
    margin-top: 48px;
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: 18px; overflow: hidden;
}
.recent-orders-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px; border-bottom: 1px solid var(--border);
}
.order-row-item {
    display: grid;
    grid-template-columns: 1fr 120px 100px 100px 120px;
    gap: 12px;
    padding: 14px 24px;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    font-size: 13px;
    transition: background 0.1s;
}
.order-row-item:last-child { border-bottom: none; }
.order-row-item:hover { background: rgba(255,255,255,0.015); }
.order-head {
    display: grid;
    grid-template-columns: 1fr 120px 100px 100px 120px;
    gap: 12px;
    padding: 10px 24px;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.07em;
    color: var(--text-muted);
}

/* ─── Responsive ───────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .plans-grid { grid-template-columns: repeat(2, 1fr); }
    .two-col { grid-template-columns: 1fr; }
    .order-card { position: static; }
    .features-columns { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .plans-grid { grid-template-columns: 1fr 1fr; }
    .token-stats { grid-template-columns: 1fr 1fr; }
    .usage-grid { grid-template-columns: 1fr; }
    .pricing-hero h1 { font-size: 32px; }
    .token-big-num { font-size: 64px; }
    .order-row-item, .order-head { grid-template-columns: 1fr 80px 80px; }
    .order-row-item > *:nth-child(4),
    .order-row-item > *:nth-child(5),
    .order-head > *:nth-child(4),
    .order-head > *:nth-child(5) { display: none; }
    .preset-chips { gap: 6px; }
}
@media (max-width: 420px) {
    .plans-grid { grid-template-columns: 1fr; }
    .mode-btn { padding: 10px 18px; font-size: 13px; }
}
</style>
@endpush

@section('content')

<div class="pricing-root">

    {{-- ─── HERO ─────────────────────────────────────────────────────────── --}}
    <div class="pricing-hero">
        <div class="hero-eyebrow">Kemasify Pro</div>
        <h1>Desain tanpa batas,<br><em>hasil yang maksimal.</em></h1>
        <p>Pilih premium untuk akses penuh atau beli token sesuai kebutuhan. Mulai sekarang.</p>

        {{-- Status badge --}}
        <div class="current-plan-strip">
            @if($user->isPremium())
                <span class="badge badge-premium" style="font-size:12px;padding:4px 12px;">✦ Premium Aktif</span>
                <span style="color:var(--text-muted);font-size:13px;">
                    s/d {{ $user->plan_expires_at?->format('d M Y') ?? '∞' }}
                </span>
            @else
                <span style="width:8px;height:8px;background:var(--text-muted);border-radius:50%;flex-shrink:0;"></span>
                <span style="font-size:13px;color:var(--text-muted);">Free Plan</span>
                <span style="width:1px;height:14px;background:var(--border);"></span>
                <span style="font-size:13px;">
                    Token: <strong style="color:var(--accent);">{{ $user->token_balance }}</strong>
                </span>
            @endif
        </div>
    </div>

    {{-- ─── MODE TOGGLE ──────────────────────────────────────────────────── --}}
    <div style="display:flex;justify-content:center;">
        <div class="mode-toggle">
            <button class="mode-btn active" onclick="switchMode(this,'panel-premium')">
                <span class="mode-icon">✦</span> Premium
            </button>
            <button class="mode-btn" onclick="switchMode(this,'panel-token')">
                <span class="mode-icon">🪙</span> Beli Token
            </button>
        </div>
    </div>

    {{-- ─── PANEL PREMIUM ────────────────────────────────────────────────── --}}
    <div id="panel-premium" class="mode-panel active">
        <div class="two-col">

            {{-- Left: plan selector + features --}}
            <div>
                <p style="font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px;">Pilih Durasi</p>

                <div class="plans-grid">
                    @php
                    $plans = [
                        ['months'=>1,  'discount'=>0,    'label'=>'1 Bulan'],
                        ['months'=>3,  'discount'=>0.05, 'label'=>'3 Bulan',  'popular'=>false],
                        ['months'=>6,  'discount'=>0.10, 'label'=>'6 Bulan',  'popular'=>true],
                        ['months'=>12, 'discount'=>0.20, 'label'=>'1 Tahun'],
                    ];
                    @endphp

                    @foreach($plans as $plan)
                    @php
                        $base  = 50000;
                        $total = (int)($base * $plan['months'] * (1 - $plan['discount']));
                        $per   = (int)($total / $plan['months']);
                    @endphp
                    <div class="plan-card {{ !empty($plan['popular']) ? 'popular' : '' }}"
                        data-months="{{ $plan['months'] }}"
                        data-amount="{{ $total }}"
                        data-per="{{ $per }}"
                        data-discount="{{ $plan['discount'] }}"
                        onclick="selectPlan(this)">

                        @if(!empty($plan['popular']))
                        <div class="popular-pill">POPULER</div>
                        @endif

                        <div class="plan-duration">{{ $plan['label'] }}</div>
                        <div class="plan-price-num">
                            Rp{{ number_format($per,0,',','.') }}
                        </div>
                        <div class="plan-price-label">/bulan</div>

                        @if($plan['discount'] > 0)
                        <div class="plan-save">Hemat {{ ($plan['discount']*100) }}%</div>
                        @endif

                        <div class="plan-check-wrap">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0d0d0f" stroke-width="3">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Features --}}
                <div class="features-columns">
                    <div class="features-col">
                        <div class="features-col-title">✦ Fitur Premium</div>
                        @foreach([
                            ['🪙','Token Unlimited','Tidak ada batas harian','green'],
                            ['⚡','Priority Queue','Langsung diproses, no antrian','green'],
                            ['📦','Semua Model 3D','Termasuk model premium eksklusif','orange'],
                            ['🎨','Download Render HD','Ekspor resolusi tinggi siap cetak','blue'],
                        ] as $f)
                        <div class="feat-item">
                            <div class="feat-icon {{ $f[3] }}">{{ $f[0] }}</div>
                            <div>
                                <div class="feat-title">{{ $f[1] }}</div>
                                <div class="feat-sub">{{ $f[2] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="features-col">
                        <div class="features-col-title">Free vs Premium</div>
                        @foreach([
                            ['Generate AI', '10 token', '10 token'],
                            ['Render 3D', '✗', '50 token'],
                            ['Queue', 'Ya', 'Tidak'],
                            ['Download Render', '✗', '✓'],
                            ['Token Refill', '50/24 jam', 'Unlimited'],
                            ['Model Premium', '✗', '✓'],
                        ] as $row)
                        <div style="display:grid;grid-template-columns:1fr 60px 60px;gap:8px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);font-size:12px;align-items:center;">
                            <span class="text-muted">{{ $row[0] }}</span>
                            <span style="text-align:center;color:{{ $row[1]==='✗' ? 'var(--danger)' : 'var(--text-muted)' }}">{{ $row[1] }}</span>
                            <span style="text-align:center;color:{{ $row[2]==='✓' ? 'var(--accent)' : 'var(--text)' }};font-weight:600;">{{ $row[2] }}</span>
                        </div>
                        @endforeach
                        <div style="display:grid;grid-template-columns:1fr 60px 60px;gap:8px;padding-top:10px;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;">
                            <span></span><span style="text-align:center;">Free</span><span style="text-align:center;color:var(--accent);">Pro</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: order summary --}}
            <div>
                <div class="order-card">
                    <div class="order-card-title">
                        Ringkasan Order
                        <span style="font-size:11px;color:var(--text-muted);font-weight:400;font-family:'DM Sans',sans-serif;">Premium</span>
                    </div>

                    <div id="premium-empty" style="text-align:center;padding:24px 0;color:var(--text-muted);font-size:13px;">
                        ← Pilih durasi berlangganan
                    </div>

                    <div id="premium-summary" style="display:none;">
                        <div class="order-line">
                            <span style="color:var(--text-muted);">Paket</span>
                            <span id="os-plan" style="font-weight:600;"></span>
                        </div>
                        <div class="order-line">
                            <span style="color:var(--text-muted);">Harga/bulan</span>
                            <span id="os-per"></span>
                        </div>
                        <div class="order-line">
                            <span style="color:var(--text-muted);">Hemat</span>
                            <span id="os-save" style="color:var(--accent);font-weight:600;"></span>
                        </div>
                        <div class="order-total-line">
                            <span>Total</span>
                            <span id="os-total"></span>
                        </div>
                    </div>

                    <button id="btn-pay-premium" class="pay-btn" disabled onclick="payPremium()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
                        </svg>
                        Bayar Sekarang
                    </button>

                    <div class="trust-badges">
                        <div class="trust-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            SSL Secure
                        </div>
                        <div class="trust-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Midtrans
                        </div>
                        <div class="trust-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            Instan
                        </div>
                    </div>

                    <div style="margin-top:16px;padding:12px;background:var(--bg3);border-radius:10px;">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;font-weight:600;">METODE PEMBAYARAN</div>
                        <div style="font-size:12px;color:var(--text-muted);line-height:1.8;">
                            Transfer Bank BCA, BRI, BNI, Mandiri<br>
                            GoPay · OVO · DANA · QRIS · ShopeePay
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── PANEL TOKEN ───────────────────────────────────────────────────── --}}
    <div id="panel-token" class="mode-panel">
        <div class="two-col">

            {{-- Left: slider + presets --}}
            <div>
                <div class="token-hero-card">
                    <div class="token-big-num" id="tok-display">100</div>
                    <div class="token-big-label">token</div>

                    <div class="range-wrap">
                        <div class="range-track">
                            <div class="range-fill" id="range-fill" style="width:9.18%"></div>
                        </div>
                        <input type="range" id="tok-slider"
                            min="10" max="1000" step="10" value="100"
                            oninput="updateToken(+this.value)">
                    </div>
                    <div class="range-labels">
                        <span>10</span><span>250</span><span>500</span><span>750</span><span>1.000</span>
                    </div>

                    <div class="token-stats">
                        <div class="token-stat">
                            <div class="token-stat-val text-accent" id="ts-amount">100</div>
                            <div class="token-stat-label">Token</div>
                        </div>
                        <div class="token-stat">
                            <div class="token-stat-val" id="ts-price">Rp 10.000</div>
                            <div class="token-stat-label">Total</div>
                        </div>
                        <div class="token-stat">
                            <div class="token-stat-val" id="ts-after">{{ $user->token_balance + 100 }}</div>
                            <div class="token-stat-label">Saldo Baru</div>
                        </div>
                    </div>
                </div>

                {{-- Quick presets --}}
                <p style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">Pilih Cepat</p>
                <div class="preset-chips">
                    @foreach([
                        ['val'=>50,  'label'=>'50'],
                        ['val'=>100, 'label'=>'100'],
                        ['val'=>200, 'label'=>'200'],
                        ['val'=>500, 'label'=>'500'],
                        ['val'=>1000,'label'=>'1.000'],
                    ] as $p)
                    <button class="preset-chip {{ $p['val']==100 ? 'active' : '' }}"
                        onclick="setPreset({{ $p['val'] }}, this)">
                        <span class="preset-chip-amount">{{ $p['label'] }}</span>
                        <span class="preset-chip-price">Rp {{ number_format($p['val']*100, 0, ',', '.') }}</span>
                    </button>
                    @endforeach
                </div>

                {{-- Usage guide --}}
                <p style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">Berapa yang kamu butuhkan?</p>
                <div class="usage-grid">
                    <div class="usage-card">
                        <div class="usage-num" style="color:var(--accent);">10</div>
                        <div class="usage-desc">
                            <strong>Generate AI</strong>
                            <span>Per 1x generate desain</span>
                        </div>
                    </div>
                    <div class="usage-card">
                        <div class="usage-num" style="color:var(--warning);">50</div>
                        <div class="usage-desc">
                            <strong>Render 3D</strong>
                            <span>Per 1x render packaging</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top:12px;padding:14px 16px;background:var(--bg2);border:1px solid var(--border);border-radius:12px;font-size:13px;color:var(--text-muted);line-height:1.6;">
                    💡 <strong style="color:var(--text);">100 token</strong> = 10x generate AI atau 2x render 3D.
                    Token tidak kadaluarsa — dipakai kapanpun kamu butuh.
                </div>
            </div>

            {{-- Right: order summary --}}
            <div>
                <div class="order-card">
                    <div class="order-card-title">
                        Ringkasan Order
                        <span style="font-size:11px;color:var(--text-muted);font-weight:400;font-family:'DM Sans',sans-serif;">Token</span>
                    </div>
                    <div class="order-line">
                        <span style="color:var(--text-muted);">Jumlah</span>
                        <span id="tok-os-amount" style="font-weight:600;color:var(--accent);">100 token</span>
                    </div>
                    <div class="order-line">
                        <span style="color:var(--text-muted);">Harga/token</span>
                        <span>Rp 100</span>
                    </div>
                    <div class="order-line">
                        <span style="color:var(--text-muted);">Saldo setelah</span>
                        <span id="tok-os-after">{{ $user->token_balance + 100 }}</span>
                    </div>
                    <div class="order-total-line">
                        <span>Total</span>
                        <span id="tok-os-total">Rp 10.000</span>
                    </div>

                    <button id="btn-pay-token" class="pay-btn" onclick="payToken()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
                        </svg>
                        Bayar Sekarang
                    </button>

                    <div class="trust-badges">
                        <div class="trust-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            SSL Secure
                        </div>
                        <div class="trust-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            Instan
                        </div>
                        <div class="trust-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            No Expire
                        </div>
                    </div>

                    <div style="margin-top:16px;padding:12px;background:var(--bg3);border-radius:10px;font-size:12px;color:var(--text-muted);line-height:1.8;">
                        Transfer Bank · GoPay · OVO<br>
                        DANA · QRIS · ShopeePay
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── RECENT ORDERS ────────────────────────────────────────────────── --}}
    @if($orders->isNotEmpty())
    <div class="recent-orders">
        <div class="recent-orders-header">
            <h3 style="font-size:15px;font-weight:700;">Order Terbaru</h3>
            <a href="{{ route('payment.history') }}" class="btn btn-ghost btn-sm">Lihat semua →</a>
        </div>
        <div class="order-head">
            <span>Order ID</span>
            <span>Paket</span>
            <span>Total</span>
            <span>Status</span>
            <span>Tanggal</span>
        </div>
        @foreach($orders as $order)
        <div class="order-row-item">
            <span style="font-family:monospace;font-size:11px;color:var(--text-muted);">{{ $order->order_id }}</span>
            <span style="font-weight:600;font-size:13px;">
                @if($order->isPremiumOrder())
                    <span style="color:var(--accent);">✦</span> {{ $order->getTypeLabel() }}
                @else
                    <span>🪙</span> {{ $order->getTypeLabel() }}
                @endif
            </span>
            <span style="font-weight:700;">Rp{{ number_format($order->amount,0,',','.') }}</span>
            <span style="color:{{ $order->getStatusColor() }};font-size:12px;font-weight:700;">{{ $order->getStatusLabel() }}</span>
            <span style="font-size:12px;color:var(--text-muted);">{{ $order->created_at->format('d M Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif

</div>

@push('scripts')
<script src="{{ app(\App\Services\MidtransService::class)->getSnapJsUrl() }}"
    data-client-key="{{ app(\App\Services\MidtransService::class)->getClientKey() }}"></script>

<script>
const CSRF          = document.querySelector('meta[name="csrf-token"]').content;
const USER_BALANCE  = {{ $user->token_balance }};
let premiumMonths   = 0;
let premiumAmount   = 0;
let tokenAmount     = 100;

// ─── Mode Switch ──────────────────────────────────────────────────────────────
function switchMode(btn, panelId) {
    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.mode-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(panelId).classList.add('active');
}

// ─── Plan Selector ────────────────────────────────────────────────────────────
function selectPlan(card) {
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    premiumMonths  = +card.dataset.months;
    premiumAmount  = +card.dataset.amount;
    const per      = +card.dataset.per;
    const discount = +card.dataset.discount;
    const base     = 50000;
    const saved    = base * premiumMonths - premiumAmount;

    document.getElementById('premium-empty').style.display   = 'none';
    document.getElementById('premium-summary').style.display = 'block';
    document.getElementById('os-plan').textContent  = premiumMonths + ' Bulan Premium';
    document.getElementById('os-per').textContent   = 'Rp ' + per.toLocaleString('id-ID') + '/bln';
    document.getElementById('os-save').textContent  = saved > 0 ? '− Rp ' + saved.toLocaleString('id-ID') : 'Tidak ada';
    document.getElementById('os-total').textContent = 'Rp ' + premiumAmount.toLocaleString('id-ID');

    const btn = document.getElementById('btn-pay-premium');
    btn.disabled = false;
}

// ─── Token Slider ─────────────────────────────────────────────────────────────
function updateToken(val) {
    tokenAmount = val;
    const price = val * 100;
    const pct   = ((val - 10) / (1000 - 10) * 100).toFixed(2);

    document.getElementById('tok-display').textContent  = val.toLocaleString('id-ID');
    document.getElementById('range-fill').style.width   = pct + '%';
    document.getElementById('ts-amount').textContent    = val;
    document.getElementById('ts-price').textContent     = 'Rp ' + price.toLocaleString('id-ID');
    document.getElementById('ts-after').textContent     = USER_BALANCE + val;
    document.getElementById('tok-os-amount').textContent= val.toLocaleString('id-ID') + ' token';
    document.getElementById('tok-os-after').textContent = (USER_BALANCE + val).toLocaleString('id-ID');
    document.getElementById('tok-os-total').textContent = 'Rp ' + price.toLocaleString('id-ID');
}

function setPreset(val, el) {
    document.querySelectorAll('.preset-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tok-slider').value = val;
    updateToken(val);
}

// ─── Pay Premium ──────────────────────────────────────────────────────────────
async function payPremium() {
    if (!premiumMonths) return;
    const btn = document.getElementById('btn-pay-premium');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:16px;height:16px;border-width:2px;"></div> Memproses...';

    try {
        const res  = await fetch('{{ route("payment.buy-premium") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ months: premiumMonths })
        });
        const raw  = await res.text();
        let data;
        try { data = JSON.parse(raw); } catch(e) { alert('Server error (HTTP ' + res.status + ')'); return; }

        if (!data.success) { alert(data.error || 'Gagal'); return; }

        window.snap.pay(data.snap_token, {
            onSuccess: () => location.href = '{{ route("payment.finish") }}?order_id=' + data.order_id,
            onPending: () => location.href = '{{ route("payment.finish") }}?order_id=' + data.order_id,
            onError:   () => alert('Pembayaran gagal.'),
            onClose:   () => { btn.disabled = false; btn.innerHTML = resetBtnHtml(); }
        });
    } catch(e) { alert('Error: ' + e.message); }
    finally { btn.disabled = false; btn.innerHTML = resetBtnHtml(); }
}

// ─── Pay Token ────────────────────────────────────────────────────────────────
async function payToken() {
    const btn = document.getElementById('btn-pay-token');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width:16px;height:16px;border-width:2px;"></div> Memproses...';

    try {
        const res  = await fetch('{{ route("payment.buy-tokens") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ token_amount: tokenAmount })
        });
        const raw  = await res.text();
        let data;
        try { data = JSON.parse(raw); } catch(e) { alert('Server error (HTTP ' + res.status + ')'); return; }

        if (!data.success) { alert(data.error || 'Gagal'); return; }

        window.snap.pay(data.snap_token, {
            onSuccess: () => location.href = '{{ route("payment.finish") }}?order_id=' + data.order_id,
            onPending: () => location.href = '{{ route("payment.finish") }}?order_id=' + data.order_id,
            onError:   () => alert('Pembayaran gagal.'),
            onClose:   () => { btn.disabled = false; btn.innerHTML = resetBtnHtml(); }
        });
    } catch(e) { alert('Error: ' + e.message); }
    finally { btn.disabled = false; btn.innerHTML = resetBtnHtml(); }
}

function resetBtnHtml() {
    return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg> Bayar Sekarang`;
}

// Init
updateToken(100);
</script>
@endpush

@endsection