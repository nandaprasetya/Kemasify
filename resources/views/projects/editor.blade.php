@extends('layouts.app')

@section('title', isset($project) ? $project->name . ' — 3D Editor' : '3D Editor')

@section('breadcrumb')
@isset($project)
<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;">Dashboard</a>
    <span class="text-muted">/</span>
    <span style="font-weight:600;">{{ $project->name }}</span>
    @if($project->productModel)
    <span class="badge badge-free" style="font-size:10px;">{{ $project->productModel->name }}</span>
    @endif
</div>
@endisset
@endsection

@section('topbar-actions')
@isset($project)
<div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-muted);">
    <div id="rotate-hint" style="display:flex;align-items:center;gap:4px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Drag to rotate · Scroll to zoom
    </div>
</div>
<button id="btn-save" class="btn btn-ghost btn-sm" onclick="saveSnapshot()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
    Export
</button>
<form method="POST" action="{{ route('projects.destroy', $project->slug) }}"
    onsubmit="return confirm('Hapus proyek ini?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
</form>
@endisset
@endsection

@push('styles')
<style>
/* ─── Reset overflow ───────────────────────────────────────────────── */
.main-content { overflow: hidden; }
.page-content { padding: 0; max-width: 100%; }

/* ═══════════════════════════════════════════════════════════════════
   DESKTOP LAYOUT  (≥ 769px)
═══════════════════════════════════════════════════════════════════ */
.editor-3d-layout {
    display: grid;
    grid-template-columns: 280px 1fr 260px;
    grid-template-rows: 1fr;
    height: calc(100vh - 64px);
    overflow: hidden;
}

/* ─── Panels ──────────────────────────────────────────────────────── */
.panel-left, .panel-right {
    background: var(--bg2);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    transition: transform 0.28s cubic-bezier(.4,0,.2,1);
}
.panel-left  { border-right: 1px solid var(--border); }
.panel-right { border-left:  1px solid var(--border); }

.panel-sec {
    border-bottom: 1px solid var(--border);
    padding: 14px;
    flex-shrink: 0;
}
.panel-sec:last-child { border-bottom: none; }

.psec-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    margin-bottom: 10px;
}

/* ─── 3D Canvas ───────────────────────────────────────────────────── */
.canvas-3d-area {
    position: relative;
    background: #0a0a0d;
    overflow: hidden;
}
#three-canvas {
    display: block;
    width: 100%;
    height: 100%;
    cursor: grab;
}
#three-canvas:active { cursor: grabbing; }

/* ─── Canvas overlay controls ─────────────────────────────────────── */
.canvas-controls {
    position: absolute;
    bottom: 16px;
    right: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    z-index: 10;
}
.ctrl-btn {
    width: 36px;
    height: 36px;
    background: rgba(13,13,15,0.85);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--text-muted);
    backdrop-filter: blur(8px);
    transition: all 0.15s;
}
.ctrl-btn:hover { border-color: var(--accent); color: var(--accent); }
.ctrl-btn svg   { width: 16px; height: 16px; }

/* ─── Model shape selector ────────────────────────────────────────── */
.shape-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
}
.shape-btn {
    padding: 10px 8px;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 600;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}
.shape-btn:hover  { border-color: var(--border-hover); color: var(--text); }
.shape-btn.active { border-color: var(--accent); background: var(--accent-dim); color: var(--accent); }
.shape-btn svg    { width: 24px; height: 24px; }

/* ─── Upload zone ─────────────────────────────────────────────────── */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: 9px;
    padding: 18px 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s;
}
.upload-zone:hover { border-color: var(--accent); background: var(--accent-dim); }

/* ─── Style chips ─────────────────────────────────────────────────── */
.style-chips { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 10px; }
.chip {
    padding: 3px 9px;
    border-radius: 99px;
    font-size: 11px;
    border: 1px solid var(--border);
    cursor: pointer;
    background: none;
    color: var(--text-muted);
    font-family: 'DM Sans', sans-serif;
    transition: all 0.12s;
}
.chip.sel { border-color: var(--accent); background: var(--accent-dim); color: var(--accent); }

/* ─── Status cards ────────────────────────────────────────────────── */
.status-card {
    border-radius: 8px;
    padding: 10px;
    font-size: 12px;
    margin-bottom: 10px;
}
.s-pending    { background: rgba(255,165,2,0.1);  border: 1px solid rgba(255,165,2,0.3); }
.s-processing { background: rgba(200,245,66,0.07); border: 1px solid rgba(200,245,66,0.2); }
.s-completed  { background: rgba(200,245,66,0.1);  border: 1px solid rgba(200,245,66,0.3); }
.s-failed     { background: rgba(255,71,87,0.1);   border: 1px solid rgba(255,71,87,0.3); }

/* ─── Sliders ─────────────────────────────────────────────────────── */
.slider-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 12px;
}
.slider-row label  { width: 70px; color: var(--text-muted); flex-shrink: 0; }
.slider-row span   { width: 28px; text-align: right; color: var(--text-muted); font-size: 11px; }
.slider-row input[type="range"] {
    flex: 1;
    height: 4px;
    background: var(--bg3);
    border-radius: 99px;
    outline: none;
    cursor: pointer;
    -webkit-appearance: none;
}
.slider-row input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 14px; height: 14px;
    background: var(--accent);
    border-radius: 50%;
    border: 2px solid var(--bg);
    cursor: pointer;
}

/* ─── Color picker ────────────────────────────────────────────────── */
.color-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.color-row label { font-size: 12px; color: var(--text-muted); flex: 1; }
input[type="color"] {
    width: 32px; height: 24px;
    border: 1px solid var(--border);
    border-radius: 5px;
    cursor: pointer;
    padding: 2px;
    background: var(--bg3);
}

/* ─── Environment presets ─────────────────────────────────────────── */
.env-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 5px; }
.env-btn {
    padding: 7px 4px;
    border-radius: 7px;
    border: 1px solid var(--border);
    background: var(--bg3);
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    color: var(--text-muted);
    text-align: center;
    transition: all 0.12s;
    font-family: 'DM Sans', sans-serif;
}
.env-btn:hover  { border-color: var(--border-hover); color: var(--text); }
.env-btn.active { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); }

/* ─── Canvas hint ─────────────────────────────────────────────────── */
.canvas-info {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(13,13,15,0.8);
    border: 1px solid var(--border);
    border-radius: 99px;
    padding: 5px 14px;
    font-size: 11px;
    color: var(--text-muted);
    pointer-events: none;
    backdrop-filter: blur(8px);
    opacity: 1;
    transition: opacity 1s;
    white-space: nowrap;
    z-index: 5;
}

/* ─── Loading overlay ─────────────────────────────────────────────── */
#loading-overlay {
    position: absolute;
    inset: 0;
    background: #0a0a0d;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 20;
    gap: 14px;
}
.loading-spinner {
    width: 40px; height: 40px;
    border: 3px solid rgba(200,245,66,0.15);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Screenshot flash ────────────────────────────────────────────── */
@keyframes flash { 0%{opacity:1} 100%{opacity:0} }
.flash { animation: flash 0.4s ease forwards; }

/* ─── Tab mini ────────────────────────────────────────────────────── */
.tab-mini { transition: all 0.15s; }

/* ═══════════════════════════════════════════════════════════════════
   MOBILE LAYOUT  (≤ 768px)
═══════════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {

    /* Stack vertically: canvas → tabbar → panel */
    .editor-3d-layout {
        grid-template-columns: 1fr;
        grid-template-rows: auto auto 1fr;
        height: calc(100vh - 64px);
        overflow: hidden;
    }

    /* Canvas fills ~45% of viewport height */
    .canvas-3d-area {
        grid-row: 1;
        height: 42vh;
        min-height: 240px;
    }

    /* Mobile tab bar sits between canvas and panel */
    .mobile-tab-bar {
        grid-row: 2;
        display: flex !important;
        background: var(--bg2);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        z-index: 15;
        flex-shrink: 0;
    }

    /* Panels: only one visible at a time, scrollable */
    .panel-left {
        grid-row: 3;
        border-right: none;
        border-top: none;
        overflow-y: auto;
        display: none;
        height: 100%;
    }
    .panel-right {
        grid-row: 3;
        border-left: none;
        border-top: none;
        overflow-y: auto;
        display: none;
        height: 100%;
    }
    .panel-left.mob-active,
    .panel-right.mob-active {
        display: flex;
    }

    /* Panel section padding compact */
    .panel-sec { padding: 11px 12px; }

    /* Shape grid: 3 columns on mobile */
    .shape-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 5px;
    }
    .shape-btn         { padding: 8px 5px; font-size: 10px; gap: 3px; }
    .shape-btn svg     { width: 20px; height: 20px; }

    /* Slider label narrower */
    .slider-row label  { width: 60px; font-size: 11px; }
    .slider-row        { margin-bottom: 7px; }

    /* Env grid: 3 cols already fine */
    .env-btn { font-size: 10px; padding: 6px 3px; }

    /* Canvas controls smaller */
    .canvas-controls   { bottom: 10px; right: 10px; gap: 5px; }
    .ctrl-btn          { width: 32px; height: 32px; border-radius: 7px; }
    .ctrl-btn svg      { width: 14px; height: 14px; }

    /* Canvas hint hidden on tiny screens */
    .canvas-info       { display: none; }

    /* Rotate hint hidden */
    #rotate-hint       { display: none !important; }

    /* Upload zone compact */
    .upload-zone       { padding: 14px 10px; }

    /* AI generate textarea */
    #ai-prompt         { font-size: 12px; }
}

/* ─── Mobile tab bar (hidden desktop) ────────────────────────────── */
.mobile-tab-bar { display: none; }

.mob-tab-btn {
    flex: 1;
    padding: 9px 4px 8px;
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 10px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: color 0.15s;
    position: relative;
}
.mob-tab-btn svg { width: 17px; height: 17px; }
.mob-tab-btn.active { color: var(--accent); }
.mob-tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: 0; left: 20%; right: 20%;
    height: 2px;
    background: var(--accent);
    border-radius: 2px 2px 0 0;
}

/* ─── Very small screens (≤ 380px) ───────────────────────────────── */
@media (max-width: 380px) {
    .canvas-3d-area    { height: 38vh; min-height: 200px; }
    .shape-grid        { grid-template-columns: repeat(3, 1fr); gap: 4px; }
    .shape-btn         { padding: 6px 4px; font-size: 9px; }
    .shape-btn svg     { width: 16px; height: 16px; }
    .slider-row label  { width: 52px; font-size: 10px; }
    .env-btn           { font-size: 9px; padding: 5px 2px; }
    .ctrl-btn          { width: 28px; height: 28px; border-radius: 6px; }
    .ctrl-btn svg      { width: 12px; height: 12px; }
}
</style>
@endpush

@section('content')

@isset($project)

<div class="editor-3d-layout" id="editor-layout">

    {{-- ═══════════ LEFT PANEL ═══════════ --}}
    <div class="panel-left mob-active" id="panel-left">

        {{-- Model Shape --}}
        <div class="panel-sec">
            <div class="psec-title">Model 3D</div>
            <div class="shape-grid">
                <button class="shape-btn active" onclick="changeModel('box', this)" title="Box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    Box
                </button>
                <button class="shape-btn" onclick="changeModel('bottle', this)" title="Bottle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M8 2h8M9 2v3L6 8v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8l-3-3V2"/>
                    </svg>
                    Bottle
                </button>
                <button class="shape-btn" onclick="changeModel('can', this)" title="Can">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="4" y="4" width="16" height="16" rx="8"/>
                        <line x1="4" y1="4" x2="20" y2="4"/><line x1="4" y1="20" x2="20" y2="20"/>
                    </svg>
                    Can
                </button>
                <button class="shape-btn" onclick="changeModel('pouch', this)" title="Pouch">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M6 3h12l2 4v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7z"/>
                        <line x1="3" y1="7" x2="21" y2="7"/>
                    </svg>
                    Pouch
                </button>
                <button class="shape-btn" onclick="changeModel('tube', this)" title="Tube">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="8" y="2" width="8" height="20" rx="4"/>
                    </svg>
                    Tube
                </button>
                <button class="shape-btn" onclick="changeModel('pillow', this)" title="Pillow Bag">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 8c0-3 2-5 9-5s9 2 9 5v8c0 3-2 5-9 5s-9-2-9-5z"/>
                    </svg>
                    Pillow
                </button>
            </div>
        </div>

        {{-- Dimensi --}}
        <div class="panel-sec">
            <div class="psec-title">Dimensi Model</div>
            <div class="slider-row">
                <label>Lebar</label>
                <input type="range" min="0.5" max="3" step="0.1" value="1"
                    oninput="updateScale('x', this.value); this.nextElementSibling.textContent=this.value">
                <span>1.0</span>
            </div>
            <div class="slider-row">
                <label>Tinggi</label>
                <input type="range" min="0.5" max="3" step="0.1" value="1.5"
                    oninput="updateScale('y', this.value); this.nextElementSibling.textContent=this.value">
                <span>1.5</span>
            </div>
            <div class="slider-row">
                <label>Kedalaman</label>
                <input type="range" min="0.2" max="2" step="0.1" value="0.8"
                    oninput="updateScale('z', this.value); this.nextElementSibling.textContent=this.value">
                <span>0.8</span>
            </div>
        </div>

        {{-- Sumber Desain --}}
        <div class="panel-sec">
            <div class="psec-title">Desain Texture</div>

            {{-- Tab --}}
            <div style="display:flex;gap:4px;margin-bottom:10px;background:var(--bg3);border-radius:8px;padding:3px;">
                <button class="tab-mini active" onclick="switchDesignTab(this,'dt-upload')"
                    style="flex:1;padding:5px;border:none;background:var(--bg2);border-radius:6px;color:var(--text);font-size:11px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;">Upload</button>
                <button class="tab-mini" onclick="switchDesignTab(this,'dt-ai')"
                    style="flex:1;padding:5px;border:none;background:transparent;border-radius:6px;color:var(--text-muted);font-size:11px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;">AI Generate</button>
            </div>

            {{-- Upload --}}
            <div id="dt-upload">
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                    <input type="file" id="file-input" accept=".png,.jpg,.jpeg,.svg,.webp"
                        style="display:none;" onchange="handleTextureUpload(this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        style="display:block;margin:0 auto 8px;opacity:0.4;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    <div style="font-size:12px;font-weight:600;margin-bottom:3px;">Upload Desain</div>
                    <div style="font-size:10px;color:var(--text-muted);">PNG, JPG, WEBP · max 10MB</div>
                </div>
                <div id="upload-status" style="display:none;font-size:11px;margin-top:7px;"></div>
                @if($project->design_file_path)
                <div style="margin-top:8px;font-size:11px;color:var(--accent);">✓ Desain tersedia</div>
                @endif
            </div>

            {{-- AI --}}
            <div id="dt-ai" style="display:none;">
                @if($project->latestAiJob)
                @php $aj = $project->latestAiJob; @endphp
                <div class="status-card s-{{ $aj->status }}" id="ai-status-card">
                    @if($aj->status==='completed')  <span style="color:var(--accent);font-weight:600;">✓ Generate selesai</span>
                    @elseif($aj->status==='pending') <div style="display:flex;gap:6px;align-items:center;color:var(--warning);font-weight:600;"><div class="spinner" style="width:12px;height:12px;border-width:2px;"></div>Antrian #{{ $aj->queue_position }}</div>
                    @elseif($aj->status==='processing') <div style="display:flex;gap:6px;align-items:center;color:var(--accent);font-weight:600;"><div class="spinner" style="width:12px;height:12px;border-width:2px;"></div>Sedang dibuat...</div>
                    @elseif($aj->status==='failed') <span style="color:var(--danger);font-weight:600;">✗ Gagal — {{ $aj->error_message }}</span>
                    @endif
                </div>
                @endif

                <textarea id="ai-prompt" class="form-textarea" rows="3" style="font-size:12px;"
                    placeholder="Deskripsikan desain packaging-mu...&#10;&#10;mis: Kemasan teh hijau premium, gaya minimalis Jepang, warna earthy green dan emas"></textarea>

                <div style="margin:8px 0 4px;font-size:10px;color:var(--text-muted);font-weight:600;">STYLE</div>
                <div class="style-chips">
                    @foreach(['minimalist','bold','elegant','playful','modern','vintage','eco'] as $s)
                    <button class="chip" onclick="toggleChip(this,'{{ $s }}')">{{ ucfirst($s) }}</button>
                    @endforeach
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:8px;">
                    <span>Biaya: <strong style="color:var(--text)">10 token</strong></span>
                    <span>Saldo: <strong id="ai-bal" style="color:var(--accent)">{{ $user->token_balance }}</strong></span>
                </div>

                <button class="btn btn-primary w-full" id="btn-generate" onclick="doGenerate()" style="justify-content:center;font-size:13px;">
                    ✦ Generate AI
                </button>
                @if($user->isFree())
                <div style="font-size:10px;color:var(--text-muted);text-align:center;margin-top:5px;">Free: masuk antrian</div>
                @endif
            </div>
        </div>

        {{-- Texture mapping --}}
        <div class="panel-sec">
            <div class="psec-title">Texture Mapping</div>
            <div class="slider-row">
                <label>Rotasi</label>
                <input type="range" min="0" max="360" step="1" value="0"
                    oninput="updateTexRot(+this.value); this.nextElementSibling.textContent=this.value+'°'">
                <span>0°</span>
            </div>
            <div class="slider-row">
                <label>Skala X</label>
                <input type="range" min="0.1" max="3" step="0.05" value="1"
                    oninput="updateTexScale(+this.value, null); this.nextElementSibling.textContent=this.value">
                <span>1</span>
            </div>
            <div class="slider-row">
                <label>Skala Y</label>
                <input type="range" min="0.1" max="3" step="0.05" value="1"
                    oninput="updateTexScale(null, +this.value); this.nextElementSibling.textContent=this.value">
                <span>1</span>
            </div>
            <div class="color-row">
                <label>Warna Background</label>
                <input type="color" value="#ffffff" oninput="updateBgColor(this.value)">
            </div>
        </div>

    </div>{{-- /panel-left --}}

    {{-- ═══════════ 3D CANVAS ═══════════ --}}
    <div class="canvas-3d-area" id="canvas-wrap">

        <div id="loading-overlay">
            <div class="loading-spinner"></div>
            <div style="font-size:13px;color:var(--text-muted);">Memuat 3D Engine...</div>
        </div>

        <div class="canvas-info" id="canvas-hint">
            🖱 Drag → Rotate &nbsp;·&nbsp; Scroll → Zoom &nbsp;·&nbsp; Right-drag → Pan
        </div>

        <canvas id="three-canvas"></canvas>

        <div class="canvas-controls">
            <div class="ctrl-btn" onclick="resetCamera()" title="Reset View">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
            </div>
            <div class="ctrl-btn" onclick="toggleAutoRotate()" id="btn-autorot" title="Auto Rotate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </div>
            <div class="ctrl-btn" onclick="toggleWireframe()" title="Wireframe">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/>
                    <line x1="12" y1="2" x2="12" y2="22"/>
                    <line x1="2" y1="8.5" x2="22" y2="8.5"/>
                    <line x1="2" y1="15.5" x2="22" y2="15.5"/>
                </svg>
            </div>
            <div class="ctrl-btn" onclick="saveSnapshot()" title="Screenshot">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
            </div>
        </div>
    </div>{{-- /canvas --}}

    {{-- ═══════════ MOBILE TAB BAR ═══════════ --}}
    <div class="mobile-tab-bar" id="mobile-tab-bar">
        <button class="mob-tab-btn active" id="mtab-left" onclick="showMobilePanel('left')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8"/>
            </svg>
            Model
        </button>
        <button class="mob-tab-btn" id="mtab-right" onclick="showMobilePanel('right')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
            </svg>
            Material
        </button>
    </div>

    {{-- ═══════════ RIGHT PANEL ═══════════ --}}
    <div class="panel-right" id="panel-right">

        {{-- Material --}}
        <div class="panel-sec">
            <div class="psec-title">Material</div>
            <div class="slider-row">
                <label>Metalness</label>
                <input type="range" min="0" max="1" step="0.01" value="0.1"
                    oninput="updateMaterial('metalness', +this.value); this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span>0.10</span>
            </div>
            <div class="slider-row">
                <label>Roughness</label>
                <input type="range" min="0" max="1" step="0.01" value="0.4"
                    oninput="updateMaterial('roughness', +this.value); this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span>0.40</span>
            </div>
            <div class="slider-row">
                <label>Glossy</label>
                <input type="range" min="0" max="1" step="0.01" value="0.5"
                    oninput="updateMaterial('clearcoat', +this.value); this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span>0.50</span>
            </div>
            <div class="slider-row">
                <label>Opacity</label>
                <input type="range" min="0.1" max="1" step="0.01" value="1"
                    oninput="updateMaterial('opacity', +this.value); this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span>1.00</span>
            </div>
        </div>

        {{-- Environment / Lighting --}}
        <div class="panel-sec">
            <div class="psec-title">Environment</div>
            <div class="env-grid">
                @foreach([
                    ['studio',  'Studio'],
                    ['outdoor', 'Outdoor'],
                    ['neon',    'Neon'],
                    ['warm',    'Warm'],
                    ['cold',    'Cold'],
                    ['dark',    'Dark'],
                ] as $e)
                <button class="env-btn {{ $e[0]==='studio' ? 'active' : '' }}"
                    onclick="setEnvironment('{{ $e[0] }}', this)">{{ $e[1] }}</button>
                @endforeach
            </div>

            <div style="margin-top:10px;">
                <div class="psec-title" style="margin-bottom:6px;">Warna Bg Scene</div>
                <div class="color-row">
                    <label>Background</label>
                    <input type="color" value="#0a0a0d" oninput="setSceneBg(this.value)">
                </div>
            </div>
        </div>

        {{-- Camera --}}
        <div class="panel-sec">
            <div class="psec-title">Kamera</div>
            <div class="slider-row">
                <label>FOV</label>
                <input type="range" min="20" max="100" step="1" value="50"
                    oninput="updateFov(+this.value); this.nextElementSibling.textContent=this.value+'°'">
                <span>50°</span>
            </div>
            <div class="slider-row">
                <label>Near</label>
                <input type="range" min="0.01" max="1" step="0.01" value="0.1"
                    oninput="camera.near=+this.value; camera.updateProjectionMatrix(); this.nextElementSibling.textContent=this.value">
                <span>0.1</span>
            </div>
        </div>

        {{-- Info Proyek --}}
        <div class="panel-sec">
            <div class="psec-title">Info Proyek</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                <div class="flex items-center justify-between">
                    <span class="text-muted">Status</span>
                    @php $sm=['draft'=>'badge-free','completed'=>'badge-success','rendering'=>'badge-pending','failed'=>'badge-failed']; @endphp
                    <span class="badge {{ $sm[$project->status]??'' }}" style="font-size:10px;">{{ ucfirst($project->status) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-muted">Model</span>
                    <span>{{ $project->productModel?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-muted">Sumber</span>
                    <span>{{ $project->design_source ? ucfirst(str_replace('_',' ',$project->design_source)) : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Render 3D export --}}
        <div class="panel-sec">
            <div class="psec-title">Export Render</div>
            @if($user->isPremium())
            <button class="btn btn-primary w-full btn-sm" onclick="saveSnapshot()" style="justify-content:center;margin-bottom:6px;">
                📷 Screenshot (PNG)
            </button>
            @else
            <div style="background:rgba(255,165,2,0.07);border:1px solid rgba(255,165,2,0.2);border-radius:9px;padding:12px;text-align:center;font-size:12px;">
                <div style="font-weight:700;margin-bottom:4px;">Premium Feature</div>
                <div class="text-muted" style="margin-bottom:10px;">Export render HD</div>
                <a href="{{ route('payment.pricing') }}" class="btn btn-primary btn-sm" style="justify-content:center;display:inline-flex;">Upgrade</a>
            </div>
            @endif
        </div>

    </div>{{-- /panel-right --}}

</div>{{-- /editor-3d-layout --}}

@else
<div style="text-align:center;padding:80px;color:var(--text-muted);">
    <p>Proyek tidak ditemukan.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-top:16px;">← Dashboard</a>
</div>
@endisset

@endsection

@push('scripts')
@isset($project)
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════
// GLOBAL STATE
// ═══════════════════════════════════════════════════════
const SLUG  = '{{ $project->slug }}';
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
const BAL   = {{ $user->token_balance }};

let scene, camera, renderer, mesh, material;
let isAutoRotate = false;
let isWireframe  = false;
let texScaleX = 1, texScaleY = 1, texRot = 0;
let modelScale = { x: 1, y: 1.5, z: 0.8 };
let currentModel  = 'box';
let texture       = null;
let selectedStyle = null;
let bgColor       = '#0a0a0d';

let isDragging  = false;
let isRightDrag = false;
let prevMouse   = { x: 0, y: 0 };
let spherical   = { theta: 0.5, phi: 1.0, r: 3.5 };
let panOffset   = { x: 0, y: 0 };

// ═══════════════════════════════════════════════════════
// MOBILE PANEL SWITCHING
// ═══════════════════════════════════════════════════════
function showMobilePanel(side) {
    const left  = document.getElementById('panel-left');
    const right = document.getElementById('panel-right');
    const tLeft  = document.getElementById('mtab-left');
    const tRight = document.getElementById('mtab-right');

    if (side === 'left') {
        left.classList.add('mob-active');
        right.classList.remove('mob-active');
        tLeft.classList.add('active');
        tRight.classList.remove('active');
    } else {
        right.classList.add('mob-active');
        left.classList.remove('mob-active');
        tRight.classList.add('active');
        tLeft.classList.remove('active');
    }
}

// On desktop always show both panels (reset mobile classes)
function handleLayoutResize() {
    if (window.innerWidth > 768) {
        document.getElementById('panel-left').classList.remove('mob-active');
        document.getElementById('panel-right').classList.remove('mob-active');
    } else {
        // Restore last active tab or default to left
        const hasActive = document.querySelector('.panel-left.mob-active, .panel-right.mob-active');
        if (!hasActive) {
            document.getElementById('panel-left').classList.add('mob-active');
            document.getElementById('mtab-left').classList.add('active');
        }
    }
}
window.addEventListener('resize', () => { handleLayoutResize(); onResize(); });
handleLayoutResize();

// ═══════════════════════════════════════════════════════
// THREE.JS INIT
// ═══════════════════════════════════════════════════════
function initThree() {
    const wrap   = document.getElementById('canvas-wrap');
    const canvas = document.getElementById('three-canvas');
    const W = wrap.clientWidth;
    const H = wrap.clientHeight;

    scene = new THREE.Scene();
    scene.background = new THREE.Color(bgColor);

    camera = new THREE.PerspectiveCamera(50, W / H, 0.01, 100);
    updateCameraPosition();

    renderer = new THREE.WebGLRenderer({ canvas, antialias: true, preserveDrawingBuffer: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(W, H);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type    = THREE.PCFSoftShadowMap;
    renderer.toneMapping       = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.2;
    renderer.outputEncoding    = THREE.sRGBEncoding;

    setupLights('studio');

    const groundGeo  = new THREE.PlaneGeometry(10, 10);
    const groundMat  = new THREE.ShadowMaterial({ opacity: 0.25 });
    const groundMesh = new THREE.Mesh(groundGeo, groundMat);
    groundMesh.rotation.x  = -Math.PI / 2;
    groundMesh.position.y  = -1.2;
    groundMesh.receiveShadow = true;
    scene.add(groundMesh);

    buildModel('box');

    @if($project->design_file_path)
    loadTextureFromUrl('{{ asset("storage/" . $project->design_file_path) }}');
    @endif

    setupMouseEvents(canvas);
    setupTouchEvents(canvas);

    document.getElementById('loading-overlay').style.display = 'none';

    setTimeout(() => {
        const h = document.getElementById('canvas-hint');
        if (h) h.style.opacity = '0';
    }, 4000);

    animate();
}

// ═══════════════════════════════════════════════════════
// LIGHTING
// ═══════════════════════════════════════════════════════
const lights = [];
function setupLights(preset) {
    lights.forEach(l => scene.remove(l));
    lights.length = 0;

    const envs = {
        studio: [
            { type:'ambient', color:0xffffff, intensity:0.6 },
            { type:'directional', color:0xffffff, intensity:2.5, pos:[3,4,3], shadow:true },
            { type:'directional', color:0xc8e0ff, intensity:0.8, pos:[-3,2,-2] },
            { type:'point', color:0xffffff, intensity:0.5, pos:[0,3,2] },
        ],
        outdoor: [
            { type:'ambient', color:0x87ceeb, intensity:0.8 },
            { type:'directional', color:0xfff3e0, intensity:3, pos:[5,8,3], shadow:true },
            { type:'hemisphere', sky:0x87ceeb, ground:0x8b7355, intensity:0.6 },
        ],
        neon: [
            { type:'ambient', color:0x111122, intensity:0.3 },
            { type:'point', color:0xff00ff, intensity:3, pos:[-3,2,1] },
            { type:'point', color:0x00ffff, intensity:3, pos:[3,2,-1] },
            { type:'point', color:0xc8f542, intensity:1.5, pos:[0,3,3] },
        ],
        warm: [
            { type:'ambient', color:0xfff0d0, intensity:0.7 },
            { type:'directional', color:0xffaa44, intensity:2.5, pos:[2,5,2], shadow:true },
            { type:'point', color:0xff8800, intensity:1, pos:[-2,1,0] },
        ],
        cold: [
            { type:'ambient', color:0xd0e8ff, intensity:0.7 },
            { type:'directional', color:0xaaddff, intensity:2.5, pos:[-3,5,2], shadow:true },
            { type:'point', color:0x4488ff, intensity:1, pos:[2,2,0] },
        ],
        dark: [
            { type:'ambient', color:0x111111, intensity:0.2 },
            { type:'spot', color:0xffffff, intensity:4, pos:[0,5,3], shadow:true },
            { type:'point', color:0xc8f542, intensity:0.8, pos:[3,0,0] },
        ],
    };

    const cfg = envs[preset] || envs.studio;
    cfg.forEach(l => {
        let light;
        if (l.type === 'ambient') {
            light = new THREE.AmbientLight(l.color, l.intensity);
        } else if (l.type === 'directional') {
            light = new THREE.DirectionalLight(l.color, l.intensity);
            light.position.set(...l.pos);
            if (l.shadow) {
                light.castShadow = true;
                light.shadow.mapSize.width  = 2048;
                light.shadow.mapSize.height = 2048;
                light.shadow.camera.near    = 0.1;
                light.shadow.camera.far     = 20;
                light.shadow.camera.left    = -5;
                light.shadow.camera.right   =  5;
                light.shadow.camera.top     =  5;
                light.shadow.camera.bottom  = -5;
            }
        } else if (l.type === 'point') {
            light = new THREE.PointLight(l.color, l.intensity, 20);
            light.position.set(...l.pos);
        } else if (l.type === 'spot') {
            light = new THREE.SpotLight(l.color, l.intensity);
            light.position.set(...l.pos);
            light.angle   = Math.PI / 6;
            light.penumbra = 0.3;
            if (l.shadow) { light.castShadow = true; light.shadow.mapSize.set(2048, 2048); }
        } else if (l.type === 'hemisphere') {
            light = new THREE.HemisphereLight(l.sky, l.ground, l.intensity);
        }
        if (light) { scene.add(light); lights.push(light); }
    });
}

// ═══════════════════════════════════════════════════════
// MODEL BUILDER
// ═══════════════════════════════════════════════════════
function buildModel(type) {
    if (mesh) { scene.remove(mesh); mesh.geometry.dispose(); }

    let geo;
    const { x, y, z } = modelScale;

    switch (type) {
        case 'box':    geo = new THREE.BoxGeometry(x, y, z, 2, 2, 2); break;
        case 'bottle': geo = buildBottleGeometry(x, y, z); break;
        case 'can':    geo = new THREE.CylinderGeometry(x*0.5, x*0.5, y, 32, 4); break;
        case 'pouch':  geo = buildPouchGeometry(x, y, z); break;
        case 'tube':   geo = buildTubeGeometry(x * 0.35, y, 32); break;
        case 'pillow': geo = buildPillowGeometry(x, y, z); break;
        default:       geo = new THREE.BoxGeometry(x, y, z);
    }

    material = new THREE.MeshPhysicalMaterial({
        color: 0xffffff,
        metalness: 0.1,
        roughness: 0.4,
        clearcoat: 0.5,
        clearcoatRoughness: 0.1,
        side: THREE.FrontSide,
    });

    if (texture) { material.map = texture; material.needsUpdate = true; }

    mesh = new THREE.Mesh(geo, material);
    mesh.castShadow    = true;
    mesh.receiveShadow = true;
    scene.add(mesh);
}

function buildBottleGeometry(sx, sy, sz) {
    const pts = [
        new THREE.Vector2(0.12, 0),   new THREE.Vector2(0.15, 0.05),
        new THREE.Vector2(0.20, 0.15), new THREE.Vector2(0.22, 0.30),
        new THREE.Vector2(0.22, 0.65), new THREE.Vector2(0.18, 0.78),
        new THREE.Vector2(0.14, 0.88), new THREE.Vector2(0.10, 0.92),
        new THREE.Vector2(0.10, 1.0),
    ].map(p => new THREE.Vector2(p.x * sx, p.y * sy));
    return new THREE.LatheGeometry(pts, 32);
}

function buildPouchGeometry(sx, sy, sz) {
    const shape = new THREE.Shape();
    const r = 0.1;
    shape.moveTo(-sx/2 + r, -sy/2);
    shape.lineTo( sx/2 - r, -sy/2);
    shape.quadraticCurveTo( sx/2, -sy/2,  sx/2, -sy/2 + r);
    shape.lineTo( sx/2, sy/2 - r);
    shape.quadraticCurveTo( sx/2, sy/2,  sx/2 - r, sy/2);
    shape.lineTo(-sx/2 + r, sy/2);
    shape.quadraticCurveTo(-sx/2, sy/2, -sx/2, sy/2 - r);
    shape.lineTo(-sx/2, -sy/2 + r);
    shape.quadraticCurveTo(-sx/2, -sy/2, -sx/2 + r, -sy/2);
    return new THREE.ExtrudeGeometry(shape, { depth: sz*0.3, bevelEnabled:true, bevelThickness:0.06, bevelSize:0.06, bevelSegments:4 });
}

function buildTubeGeometry(r, h, segs) {
    return new THREE.CylinderGeometry(r, r * 0.85, h, segs, 4, false);
}

function buildPillowGeometry(sx, sy, sz) {
    const geo = new THREE.SphereGeometry(0.55, 32, 20);
    const pos = geo.attributes.position;
    for (let i = 0; i < pos.count; i++) {
        pos.setXYZ(i, pos.getX(i)*sx, pos.getY(i)*sy*0.8, pos.getZ(i)*sz*0.5);
    }
    pos.needsUpdate = true;
    geo.computeVertexNormals();
    return geo;
}

// ═══════════════════════════════════════════════════════
// TEXTURE
// ═══════════════════════════════════════════════════════
function loadTextureFromUrl(url) {
    new THREE.TextureLoader().load(url, applyTexture, undefined, e => console.warn('Texture error:', e));
}

function applyTexture(tex) {
    texture = tex;
    texture.encoding = THREE.sRGBEncoding;
    texture.wrapS = texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(texScaleX, texScaleY);
    texture.rotation = (texRot * Math.PI) / 180;
    texture.center.set(0.5, 0.5);
    if (material) { material.map = texture; material.color.set(0xffffff); material.needsUpdate = true; }
}

function loadTextureFromBase64(dataUrl) {
    const img = new Image();
    img.onload = () => { const t = new THREE.Texture(img); t.needsUpdate = true; applyTexture(t); };
    img.src = dataUrl;
}

function updateTexRot(deg)       { texRot = deg; if (texture) texture.rotation = (deg * Math.PI) / 180; }
function updateTexScale(sx, sy)  { if (sx !== null) texScaleX = sx; if (sy !== null) texScaleY = sy; if (texture) texture.repeat.set(texScaleX, texScaleY); }
function updateBgColor(hex)      { if (material) material.color.set(hex); }

// ═══════════════════════════════════════════════════════
// MOUSE & TOUCH
// ═══════════════════════════════════════════════════════
function setupMouseEvents(canvas) {
    canvas.addEventListener('mousedown', e => {
        isDragging = true; isRightDrag = e.button === 2;
        prevMouse = { x: e.clientX, y: e.clientY };
        canvas.style.cursor = 'grabbing';
        e.preventDefault();
    });
    canvas.addEventListener('contextmenu', e => e.preventDefault());

    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        const dx = e.clientX - prevMouse.x;
        const dy = e.clientY - prevMouse.y;
        prevMouse = { x: e.clientX, y: e.clientY };
        if (isRightDrag) { panOffset.x -= dx * 0.005; panOffset.y += dy * 0.005; }
        else { spherical.theta -= dx * 0.008; spherical.phi = Math.max(0.1, Math.min(Math.PI - 0.1, spherical.phi + dy * 0.008)); }
        updateCameraPosition();
    });

    window.addEventListener('mouseup', () => {
        isDragging = false;
        document.getElementById('three-canvas').style.cursor = 'grab';
    });

    canvas.addEventListener('wheel', e => {
        e.preventDefault();
        spherical.r = Math.max(1, Math.min(12, spherical.r + e.deltaY * 0.005));
        updateCameraPosition();
    }, { passive: false });
}

function setupTouchEvents(canvas) {
    let lastPinchDist = null;

    canvas.addEventListener('touchstart', e => {
        if (e.touches.length === 1) { isDragging = true; prevMouse = { x: e.touches[0].clientX, y: e.touches[0].clientY }; }
        else if (e.touches.length === 2) {
            const dx = e.touches[0].clientX - e.touches[1].clientX;
            const dy = e.touches[0].clientY - e.touches[1].clientY;
            lastPinchDist = Math.sqrt(dx*dx + dy*dy);
        }
        e.preventDefault();
    }, { passive: false });

    canvas.addEventListener('touchmove', e => {
        if (e.touches.length === 1 && isDragging) {
            const dx = e.touches[0].clientX - prevMouse.x;
            const dy = e.touches[0].clientY - prevMouse.y;
            prevMouse = { x: e.touches[0].clientX, y: e.touches[0].clientY };
            spherical.theta -= dx * 0.01;
            spherical.phi = Math.max(0.1, Math.min(Math.PI - 0.1, spherical.phi + dy * 0.01));
            updateCameraPosition();
        } else if (e.touches.length === 2 && lastPinchDist) {
            const dx = e.touches[0].clientX - e.touches[1].clientX;
            const dy = e.touches[0].clientY - e.touches[1].clientY;
            const dist = Math.sqrt(dx*dx + dy*dy);
            spherical.r = Math.max(1, Math.min(12, spherical.r - (dist - lastPinchDist) * 0.01));
            lastPinchDist = dist;
            updateCameraPosition();
        }
        e.preventDefault();
    }, { passive: false });

    canvas.addEventListener('touchend', () => { isDragging = false; lastPinchDist = null; });
}

function updateCameraPosition() {
    const { theta, phi, r } = spherical;
    camera.position.set(
        r * Math.sin(phi) * Math.sin(theta) + panOffset.x,
        r * Math.cos(phi) + panOffset.y,
        r * Math.sin(phi) * Math.cos(theta)
    );
    camera.lookAt(panOffset.x, panOffset.y, 0);
}

// ═══════════════════════════════════════════════════════
// ANIMATE
// ═══════════════════════════════════════════════════════
function animate() {
    requestAnimationFrame(animate);
    if (isAutoRotate && mesh) mesh.rotation.y += 0.006;
    renderer.render(scene, camera);
}

// ═══════════════════════════════════════════════════════
// CONTROLS
// ═══════════════════════════════════════════════════════
function changeModel(type, btn) {
    document.querySelectorAll('.shape-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentModel = type;
    buildModel(type);
}

function updateScale(axis, val)        { modelScale[axis] = +val; buildModel(currentModel); }
function updateMaterial(prop, val)     { if (!material) return; material[prop] = val; material.needsUpdate = true; }
function updateFov(val)                { camera.fov = val; camera.updateProjectionMatrix(); }
function resetCamera()                 { spherical = { theta: 0.5, phi: 1.0, r: 3.5 }; panOffset = { x:0, y:0 }; if (mesh) mesh.rotation.set(0,0,0); updateCameraPosition(); }
function toggleAutoRotate()            { isAutoRotate = !isAutoRotate; const b = document.getElementById('btn-autorot'); b.style.color = isAutoRotate ? 'var(--accent)' : ''; b.style.borderColor = isAutoRotate ? 'var(--accent)' : ''; }
function toggleWireframe()             { isWireframe = !isWireframe; if (material) material.wireframe = isWireframe; }
function setEnvironment(preset, btn)   { document.querySelectorAll('.env-btn').forEach(b => b.classList.remove('active')); btn.classList.add('active'); setupLights(preset); }
function setSceneBg(hex)               { bgColor = hex; scene.background = new THREE.Color(hex); }
function saveSnapshot()                { renderer.render(scene, camera); const a = document.createElement('a'); a.download = 'packaging-3d-{{ $project->slug }}.png'; a.href = renderer.domElement.toDataURL('image/png'); a.click(); }

function onResize() {
    const wrap = document.getElementById('canvas-wrap');
    const W = wrap.clientWidth, H = wrap.clientHeight;
    camera.aspect = W / H;
    camera.updateProjectionMatrix();
    renderer.setSize(W, H);
}

// ═══════════════════════════════════════════════════════
// DESIGN TABS
// ═══════════════════════════════════════════════════════
function switchDesignTab(btn, tabId) {
    document.querySelectorAll('.tab-mini').forEach(b => { b.style.background = 'transparent'; b.style.color = 'var(--text-muted)'; });
    document.getElementById('dt-upload').style.display = 'none';
    document.getElementById('dt-ai').style.display     = 'none';
    btn.style.background = 'var(--bg2)';
    btn.style.color      = 'var(--text)';
    document.getElementById(tabId).style.display = 'block';
}

// ═══════════════════════════════════════════════════════
// UPLOAD
// ═══════════════════════════════════════════════════════
async function handleTextureUpload(input) {
    if (!input.files[0]) return;
    const statusEl = document.getElementById('upload-status');
    statusEl.style.display = 'block';
    statusEl.innerHTML = '<span style="color:var(--text-muted)">⏳ Uploading...</span>';

    const fd = new FormData();
    fd.append('design_file', input.files[0]);
    fd.append('_token', CSRF);

    try {
        const res  = await fetch(`/projects/${SLUG}/upload-design`, { method:'POST', headers:{'Accept':'application/json'}, body:fd });
        const data = JSON.parse(await res.text());
        if (data.success) {
            statusEl.innerHTML = '<span style="color:var(--accent)">✓ Upload berhasil! Texture diterapkan.</span>';
            loadTextureFromUrl(data.design_url);
        } else {
            statusEl.innerHTML = '<span style="color:var(--danger)">✗ Upload gagal</span>';
        }
    } catch(e) {
        statusEl.innerHTML = `<span style="color:var(--danger)">✗ Error: ${e.message}</span>`;
    }
}

const zone = document.getElementById('upload-zone');
if (zone) {
    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.style.borderColor = 'var(--accent)'; });
    zone.addEventListener('dragleave', ()  => { zone.style.borderColor = 'var(--border)'; });
    zone.addEventListener('drop', e => {
        e.preventDefault(); zone.style.borderColor = 'var(--border)';
        const file = e.dataTransfer.files[0];
        if (file) { const dt = new DataTransfer(); dt.items.add(file); const inp = document.getElementById('file-input'); inp.files = dt.files; handleTextureUpload(inp); }
    });
}

// ═══════════════════════════════════════════════════════
// AI GENERATE
// ═══════════════════════════════════════════════════════
function toggleChip(btn, style) {
    const isSel = btn.classList.contains('sel');
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('sel'));
    if (!isSel) { btn.classList.add('sel'); selectedStyle = style; }
    else selectedStyle = null;
}

async function doGenerate() {
    const prompt = document.getElementById('ai-prompt').value.trim();
    if (!prompt) { alert('Masukkan deskripsi desain terlebih dahulu.'); return; }

    const btn = document.getElementById('btn-generate');
    btn.disabled = true; btn.textContent = '⏳ Memproses...';
    setAiCard('processing', '<div style="display:flex;gap:6px;align-items:center;color:var(--accent);font-weight:600;"><div class="spinner" style="width:12px;height:12px;border-width:2px;"></div>Gemini sedang membuat desain...</div>');

    try {
        const res  = await fetch(`/ai/direct/${SLUG}`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body: JSON.stringify({ prompt, style: selectedStyle }) });
        let data;
        try { data = JSON.parse(await res.text()); }
        catch(e) { setAiCard('failed', `<span style="color:var(--danger)">✗ HTTP ${res.status}</span>`); return; }

        if (data.success) {
            document.getElementById('ai-bal').textContent = data.token_balance ?? '—';
            if (data.image_base64 && data.mime_type) loadTextureFromBase64('data:' + data.mime_type + ';base64,' + data.image_base64);
            else if (data.image_url) loadTextureFromUrl(data.image_url);
            setAiCard('completed', '<span style="color:var(--accent);font-weight:600;">✓ Texture diterapkan ke model 3D!</span>');
        } else {
            setAiCard('failed', `<span style="color:var(--danger);font-weight:600;">✗ ${data.error || 'Gagal'}</span>`);
        }
    } catch(e) {
        setAiCard('failed', `<span style="color:var(--danger)">✗ ${e.message}</span>`);
    } finally {
        btn.disabled = false; btn.textContent = '✦ Generate AI';
    }
}

function setAiCard(type, html) {
    let el = document.getElementById('ai-status-card');
    if (el) { el.className = 'status-card s-' + type; el.innerHTML = html; }
    else {
        const tab = document.getElementById('dt-ai');
        el = document.createElement('div');
        el.id = 'ai-status-card'; el.className = 'status-card s-' + type; el.innerHTML = html;
        tab.insertBefore(el, tab.firstChild);
    }
}

// ═══════════════════════════════════════════════════════
// BOOT
// ═══════════════════════════════════════════════════════
window.addEventListener('load', initThree);
</script>
@endisset
@endpush