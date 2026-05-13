@extends('layouts.app')

@section('title', isset($project) ? $project->name . ' — 3D Editor' : '3D Editor')

@section('breadcrumb')
@isset($project)
<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;">Dashboard</a>
    <span class="text-muted">/</span>
    <span style="font-weight:600;">{{ $project->name }}</span>
    @if($project->productModel)
    <span class="badge badge-free" style="font-size:10px;">
        {{ $project->productModel->name }}
    </span>
    @endif
</div>
@endisset
@endsection

@section('topbar-actions')
@isset($project)
<span style="font-size:11px;color:var(--text-muted);">
    🖱 Drag=Rotate &nbsp;·&nbsp; Scroll=Zoom &nbsp;·&nbsp; Right=Pan &nbsp;·&nbsp; Click=Pilih Mesh
</span>
<button class="btn btn-ghost btn-sm" onclick="resetCamera()">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
    </svg>
    Reset View
</button>
<button class="btn btn-ghost btn-sm" id="btn-autorot" onclick="toggleAutoRotate()">⟳ Auto</button>
<button class="btn btn-primary btn-sm" onclick="saveSnapshot()">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
    </svg>
    Export PNG
</button>
<form method="POST" action="{{ route('projects.destroy', $project->slug) }}"
    onsubmit="return confirm('Hapus proyek?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
</form>
@endisset
@endsection

@push('styles')
<style>
.main-content { overflow: hidden; }
.page-content { padding: 0; max-width: 100%; }

/* ─── Layout 3 kolom ──────────────────────────────────── */
.ed-layout {
    display: grid;
    grid-template-columns: 290px 1fr 290px;
    height: calc(100vh - 64px);
    overflow: hidden;
}

/* ─── Panels ──────────────────────────────────────────── */
.ed-panel {
    background: var(--bg2);
    display: flex; flex-direction: column;
    overflow-y: auto; overflow-x: hidden;
    scrollbar-width: thin; scrollbar-color: var(--bg3) transparent;
}
.ed-panel::-webkit-scrollbar { width: 4px; }
.ed-panel::-webkit-scrollbar-thumb { background: var(--bg3); border-radius: 2px; }
.ed-panel-l { border-right: 1px solid var(--border); }
.ed-panel-r { border-left:  1px solid var(--border); }

.psec { border-bottom: 1px solid var(--border); padding: 14px; }
.psec:last-child { border-bottom: none; }
.psec-title {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: var(--text-muted); margin-bottom: 10px;
}

/* ─── 3D Canvas ───────────────────────────────────────── */
.canvas-3d {
    position: relative;
    background: radial-gradient(ellipse at 50% 60%, #0f0f1a 0%, #06060a 100%);
    overflow: hidden;
}
#three-canvas { display: block; width: 100%; height: 100%; cursor: grab; }
#three-canvas:active { cursor: grabbing; }

/* ─── Loading overlay ─────────────────────────────────── */
#loading-overlay {
    position: absolute; inset: 0; background: #06060a;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    z-index: 20; gap: 14px;
}
.spin-big {
    width: 44px; height: 44px;
    border: 3px solid rgba(200,245,66,0.12);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Mode tabs (top of left panel) ──────────────────── */
.mode-tabs {
    display: flex; background: var(--bg3);
    border-radius: 9px; padding: 3px; gap: 3px;
}
.mode-tab {
    flex: 1; padding: 6px 4px; border: none; background: transparent;
    color: var(--text-muted); font-size: 11px; font-weight: 700;
    cursor: pointer; border-radius: 7px; font-family: 'DM Sans', sans-serif;
    transition: all 0.15s; letter-spacing: 0.02em;
}
.mode-tab.on { background: var(--bg2); color: var(--text); box-shadow: 0 1px 4px rgba(0,0,0,0.4); }

/* ─── Model upload zone ───────────────────────────────── */
.model-upload-zone {
    border: 2px dashed var(--border);
    border-radius: 10px; padding: 16px 12px;
    text-align: center; cursor: pointer; transition: all 0.2s;
}
.model-upload-zone:hover,
.model-upload-zone.drag-over { border-color: var(--accent); background: var(--accent-dim); }
.model-upload-zone .muz-icon  { font-size: 22px; margin-bottom: 6px; }
.model-upload-zone .muz-label { font-size: 12px; font-weight: 700; margin-bottom: 2px; }
.model-upload-zone .muz-sub   { font-size: 10px; color: var(--text-muted); }

/* ─── Progress bar ────────────────────────────────────── */
.prog-wrap { display:none; padding:10px; background:var(--bg3); border-radius:8px; border:1px solid var(--border); margin-top:8px; }
.prog-wrap.show { display:block; }
.prog-bar-bg   { height:3px; background:var(--bg); border-radius:99px; margin-top:6px; overflow:hidden; }
.prog-bar-fill { height:100%; background:var(--accent); border-radius:99px; transition:width 0.3s; }
.prog-label    { font-size:10px; color:var(--text-muted); }

/* ─── Face selector (mode builtin) ───────────────────── */
.face-grid { display: grid; gap: 6px; }
.face-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 10px;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer; transition: all 0.15s;
    position: relative;
}
.face-btn:hover { border-color: var(--border-hover); }
.face-btn.active { border-color: var(--accent); background: var(--accent-dim); }
.face-btn.has-tex::after {
    content: '✓';
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    color: var(--accent); font-size: 11px; font-weight: 700;
}
.face-icon {
    width: 32px; height: 32px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 15px;
}
.face-name { font-size: 12px; font-weight: 600; }
.face-sub  { font-size: 10px; color: var(--text-muted); margin-top: 1px; }

/* ─── Mesh cards (mode custom) ────────────────────────── */
.mesh-list { display:flex; flex-direction:column; gap:6px; }
.mesh-card {
    background: var(--bg3); border: 1px solid var(--border);
    border-radius: 10px; overflow: hidden; transition: border-color 0.15s;
}
.mesh-card:hover   { border-color: var(--border-hover); }
.mesh-card.active  { border-color: var(--accent); }
.mesh-card.has-tex { border-color: rgba(200,245,66,0.5); }

.mesh-header {
    display:flex; align-items:center; gap:9px;
    padding:9px 11px; cursor:pointer;
}
.mesh-dot {
    width:9px; height:9px; border-radius:50%; flex-shrink:0; box-shadow:0 0 5px currentColor;
}
.mesh-info { flex:1; min-width:0; }
.mesh-name { font-size:12px; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mesh-meta { font-size:10px; color:var(--text-muted); margin-top:1px; }
.mesh-badge {
    font-size:9px; font-weight:700; padding:2px 7px; border-radius:99px;
    background:rgba(200,245,66,0.12); color:var(--accent);
    flex-shrink:0; display:none;
}
.mesh-card.has-tex .mesh-badge { display:inline; }

.mesh-controls {
    border-top:1px solid var(--border); padding:10px;
    display:none; flex-direction:column; gap:7px;
}
.mesh-card.active .mesh-controls { display:flex; }

/* ─── Tex preview ─────────────────────────────────────── */
.tex-prev-wrap { position:relative; display:none; }
.tex-prev-img  {
    width:100%; height:56px; object-fit:cover;
    border-radius:7px; border:1px solid var(--border); display:block;
}
.tex-clear-btn {
    position:absolute; top:4px; right:4px;
    width:20px; height:20px; border-radius:50%;
    background:rgba(10,10,15,0.9); border:1px solid var(--border);
    color:var(--danger); font-size:10px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all 0.12s;
}
.tex-clear-btn:hover { background:var(--danger); color:#fff; }

/* ─── Upload zones ────────────────────────────────────── */
.upload-zone, .tex-upload-zone {
    border: 2px dashed var(--border);
    border-radius: 8px; padding: 14px 8px; text-align: center;
    cursor: pointer; transition: all 0.15s;
}
.upload-zone:hover, .tex-upload-zone:hover,
.upload-zone.drag-over, .tex-upload-zone.drag-over {
    border-color: var(--accent); background: var(--accent-dim);
}
.upload-status { font-size:10px; text-align:center; padding:4px 0; display:none; }

/* ─── Design tabs ─────────────────────────────────────── */
.tab-mini-row {
    display:flex; background:var(--bg3); border-radius:8px; padding:3px; gap:3px;
    margin-bottom:10px;
}
.tab-mini {
    flex:1; padding:5px; border:none; background:transparent;
    color:var(--text-muted); font-size:11px; font-weight:600;
    cursor:pointer; border-radius:6px; font-family:'DM Sans',sans-serif;
    transition:all 0.12s;
}
.tab-mini.on { background:var(--bg2); color:var(--text); }

/* ─── AI chips ────────────────────────────────────────── */
.chips { display:flex; flex-wrap:wrap; gap:5px; }
.chip {
    padding:3px 9px; border-radius:99px; font-size:11px;
    border:1px solid var(--border); cursor:pointer;
    background:none; color:var(--text-muted);
    font-family:'DM Sans',sans-serif; transition:all 0.12s;
}
.chip.on { border-color:var(--accent); background:var(--accent-dim); color:var(--accent); }

/* ─── Status cards ────────────────────────────────────── */
.st-card { border-radius:8px; padding:9px; font-size:12px; margin-bottom:8px; }
.st-processing { background:rgba(200,245,66,.07); border:1px solid rgba(200,245,66,.2); }
.st-completed  { background:rgba(200,245,66,.1);  border:1px solid rgba(200,245,66,.3); }
.st-failed     { background:rgba(255,71,87,.1);   border:1px solid rgba(255,71,87,.3);  }

/* ─── Sliders ─────────────────────────────────────────── */
.sl-row { display:flex; align-items:center; gap:8px; margin-bottom:7px; font-size:11px; }
.sl-row label { width:64px; color:var(--text-muted); flex-shrink:0; font-size:10px; }
.sl-row input[type="range"] {
    flex:1; height:3px; background:var(--bg3);
    border-radius:99px; outline:none; cursor:pointer; -webkit-appearance:none;
}
.sl-row input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance:none; width:13px; height:13px;
    background:var(--accent); border-radius:50%;
    border:2px solid var(--bg); cursor:pointer;
}
.sl-row .sl-val { width:32px; text-align:right; color:var(--text-muted); font-size:10px; }

/* ─── Env buttons ─────────────────────────────────────── */
.env-row { display:flex; gap:5px; flex-wrap:wrap; }
.env-btn {
    padding:5px 10px; border-radius:6px;
    border:1px solid var(--border); background:var(--bg3);
    cursor:pointer; font-size:10px; font-weight:600;
    color:var(--text-muted); font-family:'DM Sans',sans-serif; transition:all 0.12s;
}
.env-btn.on { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }

/* ─── Stats ───────────────────────────────────────────── */
.stat-row {
    display:flex; justify-content:space-between; align-items:center;
    font-size:12px; padding:5px 0; border-bottom:1px solid var(--border);
}
.stat-row:last-child { border-bottom:none; }
.stat-label { color:var(--text-muted); font-size:11px; }
.stat-val   { font-weight:700; font-size:11px; }

/* ─── Mesh card: AI panel textarea + form-textarea ───── */
.form-textarea,
.mesh-controls .form-textarea {
    width: 100%; box-sizing: border-box;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 7px; color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px; padding: 8px 10px; outline: none;
    transition: border-color 0.15s; line-height: 1.5;
    resize: vertical;
}
.form-textarea:focus,
.mesh-controls .form-textarea:focus { border-color: var(--accent); }

/* ─── Clear button ────────────────────────────────────── */
.clear-face-btn {
    width:100%; padding:6px; border:1px solid rgba(255,71,87,.3);
    border-radius:6px; background:transparent; color:var(--danger);
    font-size:11px; cursor:pointer; font-family:'DM Sans',sans-serif;
    transition:all 0.12s;
}
.clear-face-btn:hover { background:rgba(255,71,87,.1); }
</style>
@endpush

@section('content')
@isset($project)

@php
    $cat       = $project->productModel?->category ?? 'box';
    $modelName = $project->productModel?->name ?? 'Produk';
    $dims      = $project->productModel?->dimensions ?? ['width'=>10,'height'=>15,'depth'=>8];
    $slug      = $project->slug;
    $modelFileUrl = $project->productModel?->model_3d_path
        ? asset('storage/'.$project->productModel->model_3d_path)
        : null;

    $faceDefs = [
        'box'    => [
            ['key'=>'front',  'label'=>'Depan',       'icon'=>'🟦', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'back',   'label'=>'Belakang',    'icon'=>'🟨', 'bg'=>'rgba(255,184,0,.15)'],
            ['key'=>'left',   'label'=>'Kiri',        'icon'=>'🟩', 'bg'=>'rgba(39,174,96,.15)'],
            ['key'=>'right',  'label'=>'Kanan',       'icon'=>'🟧', 'bg'=>'rgba(230,126,34,.15)'],
            ['key'=>'top',    'label'=>'Atas',        'icon'=>'🟥', 'bg'=>'rgba(231,76,60,.15)'],
            ['key'=>'bottom', 'label'=>'Bawah',       'icon'=>'⬜', 'bg'=>'rgba(200,200,200,.15)'],
        ],
        'bottle' => [
            ['key'=>'body',   'label'=>'Badan Label', 'icon'=>'🏷️', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'cap',    'label'=>'Tutup',       'icon'=>'🔵', 'bg'=>'rgba(142,68,173,.15)'],
        ],
        'can'    => [
            ['key'=>'wrap',   'label'=>'Wrap Badan',  'icon'=>'🏷️', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'top',    'label'=>'Tutup Atas',  'icon'=>'🔘', 'bg'=>'rgba(200,200,200,.15)'],
        ],
        'pouch'  => [
            ['key'=>'front',  'label'=>'Depan',       'icon'=>'🟦', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'back',   'label'=>'Belakang',    'icon'=>'🟨', 'bg'=>'rgba(255,184,0,.15)'],
        ],
        'tube'   => [
            ['key'=>'body',   'label'=>'Badan Tube',  'icon'=>'🏷️', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'cap',    'label'=>'Tutup',       'icon'=>'🔵', 'bg'=>'rgba(142,68,173,.15)'],
        ],
        'bag'    => [
            ['key'=>'front',  'label'=>'Depan',       'icon'=>'🟦', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'back',   'label'=>'Belakang',    'icon'=>'🟨', 'bg'=>'rgba(255,184,0,.15)'],
        ],
        'jar'    => [
            ['key'=>'body',   'label'=>'Badan',       'icon'=>'🏷️', 'bg'=>'rgba(61,156,245,.15)'],
            ['key'=>'lid',    'label'=>'Tutup',       'icon'=>'🔵', 'bg'=>'rgba(142,68,173,.15)'],
        ],
    ];
    $faces = $faceDefs[$cat] ?? $faceDefs['box'];
@endphp

<div class="ed-layout">

    {{-- ════════════════════════ LEFT PANEL ════════════════════════ --}}
    <div class="ed-panel ed-panel-l">

        {{-- Product info --}}
        <div class="psec">
            <div class="psec-title">Model Produk</div>
            <div style="display:flex;align-items:center;gap:10px;">
                @if($project->productModel?->thumbnail_path)
                <img src="{{ $project->productModel->thumbnail_url }}"
                    style="width:44px;height:44px;object-fit:contain;background:var(--bg3);border-radius:8px;border:1px solid var(--border);"
                    onerror="this.style.display='none'">
                @endif
                <div>
                    <div style="font-weight:700;font-size:13px;">{{ $modelName }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ ucfirst($cat) }}</div>
                    @if($dims)
                    <div style="font-size:10px;color:var(--text-muted);">
                        {{ $dims['width']??'?' }}×{{ $dims['height']??'?' }}×{{ $dims['depth']??'?' }} cm
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Mode switch: Built-in vs Custom 3D file ── --}}
        <div class="psec">
            <div class="psec-title">Mode Editor</div>
            <div class="mode-tabs">
                <button class="mode-tab on" id="mtab-builtin" onclick="switchEditorMode('builtin')">
                     Built-in
                </button>
                <button class="mode-tab" id="mtab-custom" onclick="switchEditorMode('custom')">
                     File 3D
                </button>
            </div>
            <div style="margin-top:8px;font-size:10px;color:var(--text-muted);line-height:1.6;" id="mode-hint">
                Menggunakan model bawaan sesuai tipe produk.
            </div>
        </div>

        {{-- ══════ BUILTIN MODE ══════ --}}
        <div id="sec-builtin">
            {{-- Face selector --}}
            <div class="psec">
                <div class="psec-title">Pilih Bagian</div>
                <div class="face-grid" id="face-grid">
                    @foreach($faces as $i => $face)
                    <div class="face-btn {{ $i===0 ? 'active' : '' }}"
                        id="face-btn-{{ $face['key'] }}"
                        onclick="selectFace('{{ $face['key'] }}', this)"
                        data-face="{{ $face['key'] }}">
                        <div class="face-icon" style="background:{{ $face['bg'] }};">{{ $face['icon'] }}</div>
                        <div>
                            <div class="face-name">{{ $face['label'] }}</div>
                            <div class="face-sub" id="face-sub-{{ $face['key'] }}">Belum ada gambar</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Design panel for selected face --}}
            <div class="psec">
                <div class="psec-title">
                    Desain — <span id="active-face-label" style="color:var(--accent);">{{ $faces[0]['label'] }}</span>
                </div>

                <div class="tab-mini-row">
                    <button class="tab-mini on" id="btmb-upload" onclick="switchBuiltinTab('upload')">Upload</button>
                    <button class="tab-mini"    id="btmb-ai"     onclick="switchBuiltinTab('ai')">AI Generate</button>
                </div>

                {{-- Upload --}}
                <div id="bdt-upload">
                    <div id="face-preview-wrap" style="display:none;">
                        <img id="face-preview-img" class="tex-prev-img" src="" alt="preview"
                            style="width:100%;height:64px;object-fit:cover;border-radius:6px;border:1px solid var(--border);margin-bottom:8px;">
                        <button class="clear-face-btn" onclick="clearFaceTexture()">✕ Hapus gambar bagian ini</button>
                        <div style="margin:8px 0;border-top:1px solid var(--border);"></div>
                    </div>
                    <div class="upload-zone" id="builtin-upload-zone"
                        onclick="document.getElementById('builtin-file-input').click()">
                        <input type="file" id="builtin-file-input" accept=".png,.jpg,.jpeg,.webp"
                            style="display:none;" onchange="handleBuiltinUpload(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            style="display:block;margin:0 auto 8px;opacity:0.35;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                        </svg>
                        <div style="font-size:12px;font-weight:600;margin-bottom:2px;">Upload ke bagian ini</div>
                        <div style="font-size:10px;color:var(--text-muted);">PNG, JPG, WEBP</div>
                    </div>
                    <div class="upload-status" id="builtin-upload-status"></div>
                </div>

                {{-- AI Generate (builtin mode) --}}
                <div id="bdt-ai" style="display:none;">
                    <div id="bai-status-card" style="display:none;" class="st-card"></div>
                    <textarea id="bai-prompt" class="form-textarea" rows="3" style="font-size:12px;margin-bottom:8px;"
                        placeholder="Deskripsi desain untuk bagian ini...&#10;mis: Label premium teh hijau, gaya Jepang minimalis"></textarea>
                    <div style="margin-bottom:6px;font-size:10px;color:var(--text-muted);font-weight:600;">STYLE</div>
                    <div class="chips" id="bchips" style="margin-bottom:10px;">
                        @foreach(['minimalist','bold','elegant','modern','vintage','eco','playful'] as $s)
                        <button class="chip" onclick="toggleBuiltinChip(this,'{{ $s }}')">{{ ucfirst($s) }}</button>
                        @endforeach
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:8px;">
                        <span>Biaya: <strong style="color:var(--text)">10 token</strong></span>
                        <span>Saldo: <strong id="bai-bal" style="color:var(--accent)">{{ $user->token_balance }}</strong></span>
                    </div>
                    <button class="btn btn-primary w-full" id="bbtn-gen" onclick="doBuiltinGenerate()"
                        style="justify-content:center;font-size:13px;">
                        ✦ Generate untuk bagian ini
                    </button>
                    @if($user->isFree())
                    <div style="text-align:center;font-size:10px;color:var(--text-muted);margin-top:4px;">Free: masuk antrian</div>
                    @endif
                </div>
            </div>

            {{-- Texture transform (builtin) --}}
            <div class="psec">
                <div class="psec-title">
                    Texture — <span id="tex-face-label" style="color:var(--accent);">{{ $faces[0]['label'] }}</span>
                </div>
                <div class="sl-row">
                    <label>Rotasi</label>
                    <input type="range" min="0" max="360" step="1" value="0" id="bsl-rot"
                        oninput="updateBuiltinTexParam('rot',+this.value);this.nextElementSibling.textContent=this.value+'°'">
                    <span class="sl-val">0°</span>
                </div>
                <div class="sl-row">
                    <label>Skala X</label>
                    <input type="range" min="0.1" max="3" step="0.05" value="1" id="bsl-sx"
                        oninput="updateBuiltinTexParam('sx',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                    <span class="sl-val">1.0</span>
                </div>
                <div class="sl-row">
                    <label>Skala Y</label>
                    <input type="range" min="0.1" max="3" step="0.05" value="1" id="bsl-sy"
                        oninput="updateBuiltinTexParam('sy',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                    <span class="sl-val">1.0</span>
                </div>
                <div class="sl-row">
                    <label>Offset X</label>
                    <input type="range" min="-1" max="1" step="0.01" value="0" id="bsl-ox"
                        oninput="updateBuiltinTexParam('ox',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                    <span class="sl-val">0.00</span>
                </div>
                <div class="sl-row">
                    <label>Offset Y</label>
                    <input type="range" min="-1" max="1" step="0.01" value="0" id="bsl-oy"
                        oninput="updateBuiltinTexParam('oy',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                    <span class="sl-val">0.00</span>
                </div>
            </div>
        </div>

        {{-- ══════ CUSTOM FILE 3D MODE ══════ --}}
        <div id="sec-custom" style="display:none;">
            {{-- Upload file 3D --}}
            <div class="psec">
                <div class="psec-title">
                    File 3D Model
                    @if($modelFileUrl)
                    <span style="color:var(--accent);font-size:9px;"> · AUTO-LOADED</span>
                    @endif
                </div>
                <div class="model-upload-zone" id="drop-model"
                    onclick="document.getElementById('input-3d-file').click()">
                    <input type="file" id="input-3d-file" accept=".obj,.3ds,.glb,.gltf"
                        style="display:none" onchange="loadModelFile(this)">
                    <div class="muz-icon"></div>
                    <div class="muz-label" id="muz-label">
                        @if($modelFileUrl) Ganti file 3D @else Upload file 3D @endif
                    </div>
                    <div class="muz-sub">OBJ · 3DS · GLB · GLTF</div>
                </div>
                <div class="prog-wrap" id="prog-wrap">
                    <div class="prog-label" id="prog-label">Parsing mesh...</div>
                    <div class="prog-bar-bg">
                        <div class="prog-bar-fill" id="prog-fill" style="width:0%"></div>
                    </div>
                </div>
            </div>

            {{-- Detected mesh list --}}
            <div class="psec" id="sec-meshes" style="display:none;">
                <div class="psec-title">
                    Mesh Terdeteksi —
                    <span id="mesh-count" style="color:var(--accent)">0</span> bagian
                </div>
                <div class="mesh-list" id="mesh-list"></div>
            </div>

            {{-- Empty state --}}
            <div class="psec" id="sec-no-model">
                <p style="font-size:11px;color:var(--text-muted);line-height:1.7;">
                    Upload file <b>.obj</b>, <b>.3ds</b>, atau <b>.glb/.gltf</b>.<br>
                    Sistem akan <b style="color:var(--accent)">otomatis mendeteksi</b> semua mesh
                    dan menampilkan kontrol per bagian.
                </p>
            </div>
        </div>

    </div>

    {{-- ════════════════════════ 3D CANVAS ════════════════════════ --}}
    <div class="canvas-3d" id="canvas-wrap">
        <div id="loading-overlay" style="display:none;">
            <div class="spin-big"></div>
            <div id="loading-msg" style="font-size:12px;color:var(--text-muted);">Memuat model...</div>
        </div>
        <canvas id="three-canvas"></canvas>
    </div>

    {{-- ════════════════════════ RIGHT PANEL ════════════════════════ --}}
    <div class="ed-panel ed-panel-r">

        {{-- Stats (only relevant in custom mode) --}}
        <div class="psec" id="sec-stats" style="display:none;">
            <div class="psec-title">Statistik Model</div>
            <div class="stat-row"><span class="stat-label">Mesh</span><span class="stat-val" id="st-mesh" style="color:var(--accent)">—</span></div>
            <div class="stat-row"><span class="stat-label">Vertices</span><span class="stat-val" id="st-vert" style="color:#42f5c8">—</span></div>
            <div class="stat-row"><span class="stat-label">Faces</span><span class="stat-val" id="st-face">—</span></div>
            <div class="stat-row"><span class="stat-label">Format</span><span class="stat-val" id="st-fmt">—</span></div>
            <div class="stat-row"><span class="stat-label">Tekstur aktif</span><span class="stat-val" id="st-tex" style="color:var(--accent)">0 / 0</span></div>
        </div>

        {{-- Per-mesh design controls (custom mode) --}}
        <div class="psec" id="sec-mesh-ctrl" style="display:none;">
            <div class="psec-title">
                Desain untuk:
                <span id="ctrl-mesh-name" style="color:var(--accent);">—</span>
            </div>

            <div class="tab-mini-row">
                <button class="tab-mini on" id="ctmb-upload" onclick="switchCustomTab('upload')">Upload</button>
                <button class="tab-mini"    id="ctmb-ai"     onclick="switchCustomTab('ai')">AI Generate</button>
            </div>

            {{-- Upload --}}
            <div id="cdt-upload">
                <div class="tex-prev-wrap" id="right-prev-wrap">
                    <img id="right-prev-img" class="tex-prev-img" src="" alt="">
                    <button class="tex-clear-btn" onclick="clearActiveMeshTex()" title="Hapus">✕</button>
                </div>
                <div class="tex-upload-zone" id="right-upload-zone" onclick="triggerUploadActive()">
                    <input type="file" id="right-file-input" accept=".png,.jpg,.jpeg,.webp"
                        style="display:none" onchange="handleRightUpload(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        style="display:block;margin:0 auto 6px;opacity:0.4;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    <div style="font-size:11px;font-weight:600;margin-bottom:1px;">
                        Upload ke <b id="right-mesh-name">mesh ini</b>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);">PNG · JPG · WEBP</div>
                </div>
                <div class="upload-status" id="right-upload-status"></div>
            </div>

            {{-- AI Generate (custom mode) --}}
            <div id="cdt-ai" style="display:none;">
                <div id="cai-status-card" style="display:none;" class="st-card"></div>
                <textarea id="cai-prompt" class="form-textarea" rows="3"
                    style="font-size:12px;margin-bottom:8px;"
                    placeholder="Deskripsi desain untuk mesh ini...&#10;&#10;mis: Label premium teh hijau, gaya Jepang minimalis, warna gold dan hijau sage"></textarea>
                <div style="font-size:10px;color:var(--text-muted);font-weight:600;margin-bottom:6px;">STYLE</div>
                <div class="chips" id="cchips" style="margin-bottom:10px;">
                    @foreach(['minimalist','bold','elegant','modern','vintage','eco','playful'] as $s)
                    <button class="chip" onclick="toggleCustomChip(this,'{{ $s }}')">{{ ucfirst($s) }}</button>
                    @endforeach
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-bottom:8px;">
                    <span>Biaya: <strong style="color:var(--text)">10 token</strong></span>
                    <span>Saldo: <strong id="cai-bal" style="color:var(--accent)">{{ $user->token_balance }}</strong></span>
                </div>
                <button class="btn btn-primary w-full" id="cbtn-gen" onclick="doCustomGenerate()"
                    style="justify-content:center;font-size:13px;">
                    ✦ Generate untuk mesh ini
                </button>
                @if($user->isFree())
                <div style="text-align:center;font-size:10px;color:var(--text-muted);margin-top:4px;">Free: masuk antrian</div>
                @endif
            </div>

            {{-- Texture transform (custom mode) --}}
            <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border);">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:8px;">Texture Transform</div>
                <div class="sl-row">
                    <label>Rotasi</label>
                    <input type="range" min="0" max="360" step="1" value="0" id="csl-rot"
                        oninput="updateActiveTexParam('rot',+this.value);this.nextElementSibling.textContent=this.value+'°'">
                    <span class="sl-val">0°</span>
                </div>
                <div class="sl-row">
                    <label>Skala X</label>
                    <input type="range" min="0.1" max="4" step="0.05" value="1" id="csl-sx"
                        oninput="updateActiveTexParam('sx',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                    <span class="sl-val">1.0</span>
                </div>
                <div class="sl-row">
                    <label>Skala Y</label>
                    <input type="range" min="0.1" max="4" step="0.05" value="1" id="csl-sy"
                        oninput="updateActiveTexParam('sy',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                    <span class="sl-val">1.0</span>
                </div>
                <div class="sl-row">
                    <label>Offset X</label>
                    <input type="range" min="-1" max="1" step="0.01" value="0" id="csl-ox"
                        oninput="updateActiveTexParam('ox',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                    <span class="sl-val">0.00</span>
                </div>
                <div class="sl-row">
                    <label>Offset Y</label>
                    <input type="range" min="-1" max="1" step="0.01" value="0" id="csl-oy"
                        oninput="updateActiveTexParam('oy',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                    <span class="sl-val">0.00</span>
                </div>
            </div>
        </div>

        {{-- Material global --}}
        <div class="psec">
            <div class="psec-title">Material Global</div>
            <div class="sl-row">
                <label>Metalness</label>
                <input type="range" min="0" max="1" step="0.01" value="0.05"
                    oninput="setMatAll('metalness',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span class="sl-val">0.05</span>
            </div>
            <div class="sl-row">
                <label>Roughness</label>
                <input type="range" min="0" max="1" step="0.01" value="0.35"
                    oninput="setMatAll('roughness',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span class="sl-val">0.35</span>
            </div>
            <div class="sl-row">
                <label>Clearcoat</label>
                <input type="range" min="0" max="1" step="0.01" value="0.6"
                    oninput="setMatAll('clearcoat',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span class="sl-val">0.60</span>
            </div>
            <div class="sl-row">
                <label>Opacity</label>
                <input type="range" min="0.1" max="1" step="0.01" value="1"
                    oninput="setMatAll('opacity',+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(2)">
                <span class="sl-val">1.00</span>
            </div>
        </div>

        {{-- Skala model (hanya builtin) --}}
        <div class="psec" id="sec-scale">
            <div class="psec-title">Skala Model</div>
            <div class="sl-row">
                <label>Lebar</label>
                <input type="range" min="0.4" max="2.5" step="0.05" value="1" id="sl-mx"
                    oninput="rebuildWithScale(+this.value,null,null);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                <span class="sl-val">1.0</span>
            </div>
            <div class="sl-row">
                <label>Tinggi</label>
                <input type="range" min="0.4" max="3" step="0.05" value="1.5" id="sl-my"
                    oninput="rebuildWithScale(null,+this.value,null);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                <span class="sl-val">1.5</span>
            </div>
            <div class="sl-row">
                <label>Kedalaman</label>
                <input type="range" min="0.2" max="2" step="0.05" value="0.7" id="sl-mz"
                    oninput="rebuildWithScale(null,null,+this.value);this.nextElementSibling.textContent=(+this.value).toFixed(1)">
                <span class="sl-val">0.7</span>
            </div>
        </div>

        {{-- Lighting --}}
        <div class="psec">
            <div class="psec-title">Lighting</div>
            <div class="env-row">
                @foreach(['Studio','Outdoor','Warm','Cold','Neon','Dark'] as $e)
                <button class="env-btn {{ $e==='Studio'?'on':'' }}"
                    onclick="setEnv('{{ strtolower($e) }}',this)">{{ $e }}</button>
                @endforeach
            </div>
            <div style="margin-top:10px;">
                <div class="sl-row" style="margin-bottom:0;">
                    <label>Bg Scene</label>
                    <input type="color" value="#06060a"
                        oninput="scene.background=new THREE.Color(this.value)"
                        style="flex:1;height:24px;border:1px solid var(--border);border-radius:5px;background:var(--bg3);cursor:pointer;padding:2px;">
                </div>
            </div>
        </div>

        {{-- Export --}}
        <div class="psec">
            <div class="psec-title">Tampilan & Export</div>
            <div style="display:flex;flex-direction:column;gap:7px;">
                <button class="btn btn-ghost btn-sm" onclick="toggleWireframe()">⬡ Toggle Wireframe</button>
                @if($user->isPremium())
                <button class="btn btn-primary btn-sm" onclick="saveSnapshot()" style="justify-content:center;">
                    Download PNG HD
                </button>
                @else
                <div style="font-size:11px;color:var(--text-muted);padding:8px;background:var(--bg3);border-radius:8px;text-align:center;">
                    <a href="{{ route('payment.pricing') }}" style="color:var(--accent);">Upgrade Premium</a> untuk export HD
                </div>
                @endif
            </div>
        </div>

        {{-- Info proyek --}}
        <div class="psec">
            <div class="psec-title">Info Proyek</div>
            <div style="font-size:12px;display:flex;flex-direction:column;gap:7px;">
                <div class="flex items-center justify-between">
                    <span class="text-muted">Status</span>
                    @php $sm=['draft'=>'badge-free','completed'=>'badge-success','rendering'=>'badge-pending','failed'=>'badge-failed']; @endphp
                    <span class="badge {{ $sm[$project->status]??'' }}" style="font-size:10px;">{{ ucfirst($project->status) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-muted">Sumber</span>
                    <span>{{ $project->design_source ? ucfirst(str_replace('_',' ',$project->design_source)) : '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-muted">Dibuat</span>
                    <span>{{ $project->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

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
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/OBJLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/TDSLoader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>

<script>
// ══════════════════════════════════════════════════════════════
// CONFIG
// ══════════════════════════════════════════════════════════════
const CATEGORY = '{{ $cat }}';
const SLUG     = '{{ $slug }}';
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
const DIMS     = {
    w: {{ ($dims['width']  ?? 10) / 10 }},
    h: {{ ($dims['height'] ?? 15) / 10 }},
    d: {{ ($dims['depth']  ?? 8)  / 10 }}
};
const MODEL_URL = @json($modelFileUrl);

const FACE_DEFS = {
    box:    ['front','back','left','right','top','bottom'],
    bottle: ['body','cap'],
    can:    ['wrap','top'],
    pouch:  ['front','back'],
    tube:   ['body','cap'],
    bag:    ['front','back'],
    jar:    ['body','lid'],
};
const FACES = FACE_DEFS[CATEGORY] || FACE_DEFS.box;

const MESH_COLORS = [
    '#4fc3f7','#ef5350','#66bb6a','#ffa726',
    '#ab47bc','#26c6da','#d4e157','#ff7043',
    '#8d6e63','#78909c','#f06292','#aed581',
];

// ══════════════════════════════════════════════════════════════
// GLOBAL STATE
// ══════════════════════════════════════════════════════════════
let scene, camera, renderer, raycaster;
let lights        = [];
let isAutoRotate  = false;
let isWireframe   = false;

// Camera
let sph   = { theta: 0.5, phi: 1.1, r: 3.8 };
let pan   = { x: 0, y: 0 };
let isDrag=false, isRDrag=false, prevM={x:0,y:0}, clickStart={x:0,y:0};

// Editor mode: 'builtin' | 'custom'
let editorMode = 'builtin';

// ── Builtin mode state ──
let builtinGroup  = null;
let builtinMats   = {};   // { faceKey: Material }
let builtinTex    = {};   // { faceKey: Texture }
let builtinParams = {};   // { faceKey: {rot,sx,sy,ox,oy} }
let builtinThumbs = {};   // { faceKey: url }
let activeFace    = FACES[0];
let builtinStyle  = null;
let modelScale    = { x: DIMS.w, y: DIMS.h, z: DIMS.d };

// ── Custom mode state ──
let customGroup    = null;
let meshData       = [];  // [{ uuid,name,mesh,color,material,texture,texParams,thumbUrl,verts,faces }]
let activeMeshIdx  = -1;
let customStyle    = null;

// ══════════════════════════════════════════════════════════════
// INIT FACE PARAMS
// ══════════════════════════════════════════════════════════════
FACES.forEach(f => { builtinParams[f] = { rot:0, sx:1, sy:1, ox:0, oy:0 }; });

// ══════════════════════════════════════════════════════════════
// THREE.JS INIT
// ══════════════════════════════════════════════════════════════
function initThree() {
    const wrap   = document.getElementById('canvas-wrap');
    const canvas = document.getElementById('three-canvas');
    const W = wrap.clientWidth, H = wrap.clientHeight;

    scene = new THREE.Scene();
    scene.background = new THREE.Color('#06060a');

    camera    = new THREE.PerspectiveCamera(48, W/H, 0.001, 1000);
    raycaster = new THREE.Raycaster();
    updateCam();

    renderer = new THREE.WebGLRenderer({ canvas, antialias:true, preserveDrawingBuffer:true });
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
    renderer.setSize(W, H);
    renderer.shadowMap.enabled  = true;
    renderer.shadowMap.type     = THREE.PCFSoftShadowMap;
    renderer.toneMapping        = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.35;
    renderer.sortObjects        = true;

    // Shadow ground
    const gMesh = new THREE.Mesh(
        new THREE.PlaneGeometry(20, 20),
        new THREE.ShadowMaterial({ opacity: 0.18 })
    );
    gMesh.rotation.x  = -Math.PI/2;
    gMesh.position.y  = -1.1;
    gMesh.receiveShadow = true;
    scene.add(gMesh);

    setEnv('studio', document.querySelector('.env-btn.on'));
    setupMouseEvents(canvas);
    setupTouchEvents(canvas);
    setupCanvasDropzone();
    window.addEventListener('resize', onResize);

    // Default: builtin mode, build box model
    buildBuiltinModel();

    // Load existing texture
    @if($project->design_file_path)
    loadBuiltinTexFromUrl('{{ asset("storage/".$project->design_file_path) }}', FACES[0]);
    @endif

    hideLoading();
    animate();
}

// ══════════════════════════════════════════════════════════════
// EDITOR MODE SWITCH
// ══════════════════════════════════════════════════════════════
function switchEditorMode(mode) {
    editorMode = mode;

    // Toggle tab UI
    document.getElementById('mtab-builtin').classList.toggle('on', mode==='builtin');
    document.getElementById('mtab-custom').classList.toggle('on',  mode==='custom');

    // Toggle panels
    document.getElementById('sec-builtin').style.display = mode==='builtin' ? 'block' : 'none';
    document.getElementById('sec-custom').style.display  = mode==='custom'  ? 'block' : 'none';

    // Toggle right panel sections
    document.getElementById('sec-scale').style.display       = mode==='builtin' ? 'block' : 'none';
    document.getElementById('sec-stats').style.display       = mode==='custom'  ? 'block' : 'none';
    document.getElementById('sec-mesh-ctrl').style.display   = 'none';

    document.getElementById('mode-hint').textContent = mode==='builtin'
        ? 'Menggunakan model bawaan sesuai tipe produk.'
        : 'Upload file 3D kustom untuk deteksi mesh otomatis.';

    if (mode === 'builtin') {
        // Sembunyikan custom group, tampilkan builtin
        if (customGroup) customGroup.visible = false;
        if (builtinGroup) builtinGroup.visible = true;
        else buildBuiltinModel();
    } else {
        // Sembunyikan builtin, tampilkan custom
        if (builtinGroup) builtinGroup.visible = false;
        if (customGroup)  customGroup.visible = true;

        // Auto-load model jika ada
        if (MODEL_URL && meshData.length === 0) {
            const ext = MODEL_URL.split('.').pop().toLowerCase();
            showLoading('Memuat model ' + ext.toUpperCase() + '...');
            loadFromURL(MODEL_URL, ext, 'Model Produk');
        }
    }
}

// ══════════════════════════════════════════════════════════════
// ════════════════ BUILTIN MODE ════════════════════════════════
// ══════════════════════════════════════════════════════════════

// ── Build model ──────────────────────────────────────────────
function buildBuiltinModel() {
    if (builtinGroup) {
        builtinGroup.traverse(o => { if (o.geometry) o.geometry.dispose(); });
        scene.remove(builtinGroup);
    }
    builtinGroup = new THREE.Group();
    const { x:W, y:H, z:D } = modelScale;

    switch (CATEGORY) {
        case 'box':    buildBox(W, H, D);    break;
        case 'bottle': buildBottle(W, H, D); break;
        case 'can':    buildCan(W, H, D);    break;
        case 'pouch':  buildPouch(W, H, D);  break;
        case 'tube':   buildTube(W, H, D);   break;
        default:       buildBox(W, H, D);
    }
    builtinGroup.traverse(o => { if (o.isMesh) o.castShadow = true; });
    scene.add(builtinGroup);
    reapplyBuiltinTextures();
}

function getBuiltinMat(key) {
    if (!builtinMats[key]) {
        builtinMats[key] = new THREE.MeshPhysicalMaterial({
            color: 0xffffff, metalness: 0.05, roughness: 0.35,
            clearcoat: 0.6, clearcoatRoughness: 0.1,
        });
    }
    return builtinMats[key];
}

// ── Box ──
function buildBox(W, H, D) {
    const geo = new THREE.BoxGeometry(W, H, D, 1, 1, 1);
    const mat = [
        getBuiltinMat('right'), getBuiltinMat('left'),
        getBuiltinMat('top'),   getBuiltinMat('bottom'),
        getBuiltinMat('front'), getBuiltinMat('back'),
    ];
    builtinGroup.add(new THREE.Mesh(geo, mat));
}

// ── Bottle ──
function buildBottle(W, H, D) {
    const pts = [
        [0.08,0],[0.12,.04],[0.18,.12],[0.22,.25],[0.22,.65],
        [0.18,.78],[0.12,.86],[0.08,.90],[0.08,1.0],
    ].map(([rx,ry]) => new THREE.Vector2(rx*W, (ry-.5)*H));
    const bodyM = new THREE.Mesh(new THREE.LatheGeometry(pts,48), getBuiltinMat('body'));
    bodyM.userData.faceKey = 'body';
    builtinGroup.add(bodyM);
    const capH = H*.08, capR = W*.09;
    const capM = new THREE.Mesh(new THREE.CylinderGeometry(capR,capR*1.1,capH,24), getBuiltinMat('cap'));
    capM.position.y = H*.5+capH*.3;
    capM.userData.faceKey = 'cap';
    builtinGroup.add(capM);
}

// ── Can ──
function buildCan(W, H, D) {
    const r = W*.5;
    const bodyM = new THREE.Mesh(new THREE.CylinderGeometry(r,r,H,48,4,true), getBuiltinMat('wrap'));
    bodyM.userData.faceKey='wrap'; builtinGroup.add(bodyM);
    const topM = new THREE.Mesh(new THREE.CircleGeometry(r,36), getBuiltinMat('top'));
    topM.rotation.x=-Math.PI/2; topM.position.y=H/2; topM.userData.faceKey='top'; builtinGroup.add(topM);
    const botM = new THREE.Mesh(new THREE.CircleGeometry(r,36), getBuiltinMat('top').clone());
    botM.rotation.x=Math.PI/2; botM.position.y=-H/2; builtinGroup.add(botM);
}

// ── Pouch ──
function buildPouch(W, H, D) {
    const depth = D*.15;
    const fM = new THREE.Mesh(new THREE.PlaneGeometry(W,H), getBuiltinMat('front'));
    fM.position.z=depth; fM.userData.faceKey='front'; builtinGroup.add(fM);
    const bM = new THREE.Mesh(new THREE.PlaneGeometry(W,H), getBuiltinMat('back'));
    bM.position.z=-depth; bM.rotation.y=Math.PI; bM.userData.faceKey='back'; builtinGroup.add(bM);
    const shape = new THREE.Shape();
    const r=.08;
    shape.moveTo(-W/2+r,-H/2); shape.lineTo(W/2-r,-H/2);
    shape.quadraticCurveTo(W/2,-H/2,W/2,-H/2+r); shape.lineTo(W/2,H/2-r);
    shape.quadraticCurveTo(W/2,H/2,W/2-r,H/2); shape.lineTo(-W/2+r,H/2);
    shape.quadraticCurveTo(-W/2,H/2,-W/2,H/2-r); shape.lineTo(-W/2,-H/2+r);
    shape.quadraticCurveTo(-W/2,-H/2,-W/2+r,-H/2);
    const ext={depth:depth*2,bevelEnabled:true,bevelThickness:.04,bevelSize:.04,bevelSegments:3};
    const bodyM=new THREE.Mesh(new THREE.ExtrudeGeometry(shape,ext),new THREE.MeshPhysicalMaterial({color:0xdddddd,roughness:.5}));
    bodyM.position.z=-depth; builtinGroup.add(bodyM);
}

// ── Tube ──
function buildTube(W, H, D) {
    const r=W*.35;
    const bodyM=new THREE.Mesh(new THREE.CylinderGeometry(r,r*.88,H,40,4,true),getBuiltinMat('body'));
    bodyM.userData.faceKey='body'; builtinGroup.add(bodyM);
    const botM=new THREE.Mesh(new THREE.CircleGeometry(r*.88,32),new THREE.MeshPhysicalMaterial({color:0xcccccc,roughness:.5}));
    botM.rotation.x=Math.PI/2; botM.position.y=-H/2; builtinGroup.add(botM);
    const shM=new THREE.Mesh(new THREE.CylinderGeometry(r*.5,r,H*.15,32),getBuiltinMat('cap'));
    shM.position.y=H/2; shM.userData.faceKey='cap'; builtinGroup.add(shM);
    const nzM=new THREE.Mesh(new THREE.CylinderGeometry(r*.22,r*.5,H*.12,24),getBuiltinMat('cap').clone());
    nzM.position.y=H/2+H*.16; builtinGroup.add(nzM);
}

function rebuildWithScale(x,y,z) {
    if (x!==null) modelScale.x=x;
    if (y!==null) modelScale.y=y;
    if (z!==null) modelScale.z=z;
    buildBuiltinModel();
}

// ── Texture (builtin) ────────────────────────────────────────
function loadBuiltinTexFromUrl(url, faceKey) {
    new THREE.TextureLoader().load(url, tex => applyBuiltinTex(tex, faceKey, url));
}
function loadBuiltinTexFromBase64(dataUrl, faceKey) {
    const img = new Image();
    img.onload = () => { const t=new THREE.Texture(img); t.needsUpdate=true; applyBuiltinTex(t,faceKey,dataUrl); };
    img.src = dataUrl;
}
function applyBuiltinTex(tex, faceKey, thumbUrl) {
    builtinTex[faceKey]    = tex;
    builtinThumbs[faceKey] = thumbUrl;
    configBuiltinTex(tex, faceKey);
    const m = getBuiltinMat(faceKey);
    m.map = tex; m.color.set(0xffffff); m.needsUpdate = true;
    document.getElementById(`face-btn-${faceKey}`)?.classList.add('has-tex');
    const sub = document.getElementById(`face-sub-${faceKey}`);
    if (sub) sub.textContent = 'Ada gambar ✓';
    if (faceKey === activeFace) updateBuiltinPreview(faceKey);
}
function configBuiltinTex(tex, faceKey) {
    const p = builtinParams[faceKey];
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(p.sx, p.sy);
    tex.offset.set(p.ox, p.oy);
    tex.rotation = (p.rot*Math.PI)/180;
    tex.center.set(.5,.5);
    if (THREE.sRGBEncoding) tex.encoding = THREE.sRGBEncoding;
    tex.needsUpdate = true;
}
function reapplyBuiltinTextures() {
    Object.entries(builtinTex).forEach(([k,t]) => {
        const m = builtinMats[k]; if (m) { m.map=t; m.needsUpdate=true; }
    });
}
function updateBuiltinTexParam(param, val) {
    builtinParams[activeFace][param] = val;
    const t = builtinTex[activeFace]; if (!t) return;
    const p = builtinParams[activeFace];
    t.repeat.set(p.sx,p.sy); t.offset.set(p.ox,p.oy);
    t.rotation=(p.rot*Math.PI)/180; t.needsUpdate=true;
}
function updateBuiltinPreview(faceKey) {
    const wrap = document.getElementById('face-preview-wrap');
    const img  = document.getElementById('face-preview-img');
    if (builtinThumbs[faceKey]) { img.src=builtinThumbs[faceKey]; wrap.style.display='block'; }
    else wrap.style.display='none';
}

// ── Face select ──────────────────────────────────────────────
function selectFace(key, el) {
    activeFace = key;
    document.querySelectorAll('.face-btn').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    const label = el.querySelector('.face-name')?.textContent || key;
    document.getElementById('active-face-label').textContent = label;
    document.getElementById('tex-face-label').textContent    = label;
    const p = builtinParams[key];
    syncSlider('bsl-rot', p.rot, '°');
    syncSlider('bsl-sx',  p.sx,  '', 1);
    syncSlider('bsl-sy',  p.sy,  '', 1);
    syncSlider('bsl-ox',  p.ox,  '', 2);
    syncSlider('bsl-oy',  p.oy,  '', 2);
    updateBuiltinPreview(key);
}

function clearFaceTexture() {
    delete builtinTex[activeFace]; delete builtinThumbs[activeFace];
    const m = builtinMats[activeFace];
    if (m) { m.map=null; m.color.set(0xf0f0f0); m.needsUpdate=true; }
    document.getElementById(`face-btn-${activeFace}`)?.classList.remove('has-tex');
    const sub = document.getElementById(`face-sub-${activeFace}`);
    if (sub) sub.textContent='Belum ada gambar';
    document.getElementById('face-preview-wrap').style.display='none';
}

// ── Builtin upload ───────────────────────────────────────────
async function handleBuiltinUpload(input) {
    if (!input.files[0]) return;
    const st = document.getElementById('builtin-upload-status');
    st.style.display='block'; st.innerHTML='<span style="color:var(--text-muted)">⏳ Uploading...</span>';
    const fd = new FormData();
    fd.append('design_file', input.files[0]);
    fd.append('_token', CSRF);
    try {
        const res  = await fetch(`/projects/${SLUG}/upload-design`,{method:'POST',headers:{'Accept':'application/json'},body:fd});
        const data = JSON.parse(await res.text());
        if (data.success) {
            st.innerHTML='<span style="color:var(--accent)">✓ Diterapkan ke bagian ini!</span>';
            loadBuiltinTexFromUrl(data.design_url, activeFace);
        } else {
            st.innerHTML=`<span style="color:var(--danger)">✗ ${data.message||'Gagal'}</span>`;
        }
    } catch(e) { st.innerHTML=`<span style="color:var(--danger)">✗ ${e.message}</span>`; }
    input.value='';
}

// ── Builtin AI generate ──────────────────────────────────────
let builtinStyleSel = null;
function toggleBuiltinChip(btn, style) {
    const on=btn.classList.contains('on');
    document.querySelectorAll('#bchips .chip').forEach(c=>c.classList.remove('on'));
    if (!on) { btn.classList.add('on'); builtinStyleSel=style; } else builtinStyleSel=null;
}

async function doBuiltinGenerate() {
    const prompt = document.getElementById('bai-prompt').value.trim();
    if (!prompt) { alert('Masukkan deskripsi desain.'); return; }
    const btn = document.getElementById('bbtn-gen');
    btn.disabled=true; btn.textContent='⏳ Memproses...';
    showBuiltinAiCard('processing','<span style="color:var(--accent)">⏳ AI sedang membuat desain...</span>');
    try {
        const res  = await fetch(`/ai/direct/${SLUG}`,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({prompt,style:builtinStyleSel,face_key:activeFace})
        });
        const data = JSON.parse(await res.text());
        if (data.success) {
            document.getElementById('bai-bal').textContent = data.token_balance??'—';
            if (data.image_base64&&data.mime_type)
                loadBuiltinTexFromBase64('data:'+data.mime_type+';base64,'+data.image_base64, activeFace);
            else if (data.image_url)
                loadBuiltinTexFromUrl(data.image_url, activeFace);
            showBuiltinAiCard('completed','<span style="color:var(--accent);font-weight:600;">✓ Diterapkan ke bagian ini!</span>');
        } else {
            showBuiltinAiCard('failed',`<span style="color:var(--danger)">✗ ${data.error||'Gagal'}</span>`);
        }
    } catch(e) { showBuiltinAiCard('failed',`<span style="color:var(--danger)">✗ ${e.message}</span>`); }
    finally { btn.disabled=false; btn.textContent='✦ Generate untuk bagian ini'; }
}

function showBuiltinAiCard(type, html) {
    const el=document.getElementById('bai-status-card');
    el.style.display='block'; el.className='st-card st-'+type; el.innerHTML=html;
}

function switchBuiltinTab(tab) {
    const isUp = tab==='upload';
    document.getElementById('btmb-upload').classList.toggle('on', isUp);
    document.getElementById('btmb-ai').classList.toggle('on', !isUp);
    document.getElementById('bdt-upload').style.display = isUp?'block':'none';
    document.getElementById('bdt-ai').style.display     = isUp?'none':'block';
}

// ══════════════════════════════════════════════════════════════
// ════════════════ CUSTOM FILE 3D MODE ═════════════════════════
// ══════════════════════════════════════════════════════════════

// ── Load model ───────────────────────────────────────────────
function loadModelFile(input) {
    const file=input.files[0]; if (!file) return;
    input.value='';
    const ext=file.name.split('.').pop().toLowerCase();
    const url=URL.createObjectURL(file);
    showLoading('Membaca '+file.name+'...');
    setProgress(true,'Parsing...',15);
    dispatchLoader(ext, url,
        obj => { processCustomModel(obj, file.name, ext); URL.revokeObjectURL(url); },
        e   => { hideLoading(); setProgress(false); alert('Gagal: '+(e.message||e)); }
    );
}
function loadFromURL(url, ext, label) {
    setProgress(true,'Memuat...',20);
    dispatchLoader(ext, url,
        obj => processCustomModel(obj, label, ext),
        e   => { hideLoading(); setProgress(false); console.warn('Load gagal:',e); }
    );
}
function dispatchLoader(ext, url, onLoad, onError) {
    if      (ext==='obj')              new THREE.OBJLoader().load(url,onLoad,null,onError);
    else if (ext==='3ds')              new THREE.TDSLoader().load(url,onLoad,null,onError);
    else if (ext==='glb'||ext==='gltf') new THREE.GLTFLoader().load(url,g=>onLoad(g.scene),null,onError);
    else onError(new Error('Format tidak didukung: .'+ext));
}

// ── Process model ────────────────────────────────────────────
function processCustomModel(object, filename, fmt) {
    if (customGroup) {
        customGroup.traverse(o=>{ if(o.geometry)o.geometry.dispose(); });
        scene.remove(customGroup);
    }
    meshData=[]; activeMeshIdx=-1;
    customGroup=new THREE.Group();
    setProgress(true,'Mendeteksi mesh...',55);

    const rawMeshes=[];
    object.traverse(child=>{ if(child.isMesh) rawMeshes.push(child); });

    if (rawMeshes.length===0) { hideLoading(); setProgress(false); alert('Tidak ditemukan mesh dalam file ini.'); return; }

    const nameCount={},nameIdx={};
    rawMeshes.forEach(m=>{ const n=m.name||'Mesh'; nameCount[n]=(nameCount[n]||0)+1; });

    let totalV=0,totalF=0;
    rawMeshes.forEach((child,i)=>{
        const base=child.name||`Mesh_${i}`;
        nameIdx[base]=(nameIdx[base]||0)+1;
        const name=nameCount[base]>1?`${base}_${nameIdx[base]}`:base;
        const color=MESH_COLORS[i%MESH_COLORS.length];
        const mat=new THREE.MeshPhysicalMaterial({
            color:new THREE.Color(color), metalness:.05, roughness:.4,
            clearcoat:.5, clearcoatRoughness:.15,
            polygonOffset:true, polygonOffsetFactor:-1, polygonOffsetUnits:-1,
        });
        const clone=child.clone();
        clone.material=mat; clone.castShadow=true; clone.receiveShadow=false;
        clone.applyMatrix4(child.matrixWorld); clone.renderOrder=i+1;
        customGroup.add(clone);
        const geo=clone.geometry;
        const vC=geo?.attributes?.position?.count||0;
        const fC=geo?.index?geo.index.count/3:vC/3;
        totalV+=vC; totalF+=fC;
        meshData.push({uuid:clone.uuid,name,mesh:clone,color,material:mat,
            texture:null,texParams:{rot:0,sx:1,sy:1,ox:0,oy:0},thumbUrl:null,verts:vC,faces:fC});
    });

    // Normalize
    const box=new THREE.Box3().setFromObject(customGroup);
    const center=box.getCenter(new THREE.Vector3());
    const size=box.getSize(new THREE.Vector3());
    const scale=2.2/(Math.max(size.x,size.y,size.z)||1);
    customGroup.position.sub(center.multiplyScalar(scale));
    customGroup.scale.setScalar(scale);
    scene.add(customGroup);

    // Stats
    document.getElementById('st-mesh').textContent = meshData.length;
    document.getElementById('st-vert').textContent = totalV.toLocaleString();
    document.getElementById('st-face').textContent = Math.round(totalF).toLocaleString();
    document.getElementById('st-fmt').textContent  = fmt.toUpperCase();
    document.getElementById('sec-stats').style.display='block';
    updateTexCount();

    buildMeshUI();
    sph.r=3.8; pan={x:0,y:0}; updateCam();
    setProgress(true,'Selesai!',100);
    setTimeout(()=>{ setProgress(false); hideLoading(); },600);

    document.getElementById('sec-meshes').style.display  ='block';
    document.getElementById('sec-no-model').style.display='none';
    document.getElementById('muz-label').textContent='Ganti file 3D';
    selectMesh(0);
}

// ── Build mesh UI ────────────────────────────────────────────
// Style options untuk AI (sama seperti builtin)
const AI_STYLES = ['minimalist','bold','elegant','modern','vintage','eco','playful'];

// Per-mesh AI state: meshAiStyle[i] = string|null
const meshAiStyle = {};

function buildMeshUI() {
    const list=document.getElementById('mesh-list');
    list.innerHTML='';
    document.getElementById('mesh-count').textContent=meshData.length;

    meshData.forEach((md,i)=>{
        meshAiStyle[i] = null;

        const styleChips = AI_STYLES.map(s =>
            `<button class="chip" onclick="toggleMeshChip(this,${i},'${s}')">${s.charAt(0).toUpperCase()+s.slice(1)}</button>`
        ).join('');

        const card=document.createElement('div');
        card.className='mesh-card'; card.id=`mc-${i}`;
        card.innerHTML=`
            <div class="mesh-header" onclick="selectMesh(${i})">
                <div class="mesh-dot" style="background:${md.color};color:${md.color};"></div>
                <div class="mesh-info">
                    <div class="mesh-name" title="${md.name}">${md.name}</div>
                    <div class="mesh-meta">${Math.round(md.faces).toLocaleString()} faces</div>
                </div>
                <span class="mesh-badge">✓ Tex</span>
            </div>

            <div class="mesh-controls" id="mctrl-${i}">

                {{-- Thumbnail preview --}}
                <div class="tex-prev-wrap" id="tpw-${i}">
                    <img id="tpimg-${i}" class="tex-prev-img" src="" alt="">
                    <button class="tex-clear-btn" onclick="clearMeshTex(${i})" title="Hapus">✕</button>
                </div>

                {{-- Tab row: Upload | AI Generate --}}
                <div class="tab-mini-row" style="margin-bottom:8px;">
                    <button class="tab-mini on" id="mtab-up-${i}"  onclick="switchMeshTab(${i},'upload')">⬆ Upload</button>
                    <button class="tab-mini"    id="mtab-ai-${i}"  onclick="switchMeshTab(${i},'ai')">✦ AI Generate</button>
                </div>

                {{-- Upload panel --}}
                <div id="mpanel-up-${i}">
                    <div class="tex-upload-zone" id="tuz-${i}"
                         onclick="document.getElementById('tfi-${i}').click()">
                        <input type="file" id="tfi-${i}" accept=".png,.jpg,.jpeg,.webp"
                               style="display:none" onchange="applyTexToMesh(${i},this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            style="display:block;margin:0 auto 5px;opacity:0.4;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                        </svg>
                        <div style="font-size:11px;font-weight:600;">Upload gambar</div>
                        <div style="font-size:10px;color:var(--text-muted);">PNG · JPG · WEBP</div>
                    </div>
                    <div class="upload-status" id="ust-${i}"></div>
                </div>

                {{-- AI Generate panel --}}
                <div id="mpanel-ai-${i}" style="display:none;">
                    <div id="mai-card-${i}" style="display:none;" class="st-card"></div>
                    <textarea id="mai-prompt-${i}" class="form-textarea" rows="2"
                        style="font-size:11px;margin-bottom:7px;resize:none;"
                        placeholder="Deskripsi desain untuk ${md.name}...&#10;mis: label minimalis hijau sage"></textarea>
                    <div style="font-size:9px;color:var(--text-muted);font-weight:700;margin-bottom:5px;text-transform:uppercase;letter-spacing:.08em;">Style</div>
                    <div class="chips" id="mchips-${i}" style="margin-bottom:8px;gap:4px;">
                        ${styleChips}
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted);margin-bottom:7px;">
                        <span>Biaya: <strong style="color:var(--text)">10 token</strong></span>
                        <span>Saldo: <strong id="mai-bal-${i}" style="color:var(--accent)">—</strong></span>
                    </div>
                    <button class="btn btn-primary w-full" id="mai-btn-${i}"
                        onclick="doMeshGenerate(${i})"
                        style="justify-content:center;font-size:12px;padding:7px;">
                        ✦ Generate untuk ${md.name}
                    </button>
                </div>

            </div>`;

        // Drag-drop on upload zone
        const zone=card.querySelector(`#tuz-${i}`);
        zone.addEventListener('dragover',  e=>{e.preventDefault();zone.classList.add('drag-over');});
        zone.addEventListener('dragleave', ()=>zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e=>{
            e.preventDefault(); zone.classList.remove('drag-over');
            const f=e.dataTransfer.files[0]; if(!f) return;
            const dt=new DataTransfer(); dt.items.add(f);
            const inp=document.getElementById(`tfi-${i}`); inp.files=dt.files; applyTexToMesh(i,inp);
        });

        list.appendChild(card);
    });

    // Sync saldo ke semua mesh AI panels
    syncMeshAiBal();
}

// ── Tab switch per mesh card ─────────────────────────────────
function switchMeshTab(idx, tab) {
    const isUp = tab==='upload';
    document.getElementById(`mtab-up-${idx}`)?.classList.toggle('on',  isUp);
    document.getElementById(`mtab-ai-${idx}`)?.classList.toggle('on', !isUp);
    document.getElementById(`mpanel-up-${idx}`).style.display = isUp ? 'block' : 'none';
    document.getElementById(`mpanel-ai-${idx}`).style.display = isUp ? 'none'  : 'block';
}

// ── Style chip per mesh ──────────────────────────────────────
function toggleMeshChip(btn, idx, style) {
    const on=btn.classList.contains('on');
    document.querySelectorAll(`#mchips-${idx} .chip`).forEach(c=>c.classList.remove('on'));
    if (!on) { btn.classList.add('on'); meshAiStyle[idx]=style; } else meshAiStyle[idx]=null;
}

// ── AI generate per mesh ─────────────────────────────────────
async function doMeshGenerate(idx) {
    const prompt = document.getElementById(`mai-prompt-${idx}`)?.value.trim();
    if (!prompt) { alert('Masukkan deskripsi desain.'); return; }
    const btn = document.getElementById(`mai-btn-${idx}`);
    btn.disabled=true; btn.textContent=' Memproses...';
    showMeshAiCard(idx,'processing',`
        <div style="display:flex;align-items:center;gap:7px;color:var(--accent);font-size:11px;font-weight:600;">
            <div style="width:10px;height:10px;border:2px solid rgba(200,245,66,.3);border-top-color:var(--accent);border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0;"></div>
            AI membuat desain...
        </div>`);
    try {
        const res=await fetch(`/ai/direct/${SLUG}`,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({
                prompt,
                style:    meshAiStyle[idx] || null,
                face_key: meshData[idx].name,
            })
        });
        const data=JSON.parse(await res.text());
        if (data.success) {
            // Update saldo di semua panel mesh
            if (data.token_balance !== undefined) {
                document.querySelectorAll('[id^="mai-bal-"]').forEach(el=>el.textContent=data.token_balance);
                // Juga update saldo di panel right (custom mode) dan builtin
                const cbal=document.getElementById('cai-bal'); if(cbal) cbal.textContent=data.token_balance;
                const bbal=document.getElementById('bai-bal'); if(bbal) bbal.textContent=data.token_balance;
            }
            if (data.image_base64&&data.mime_type)
                applyTexFromBase64(idx,'data:'+data.mime_type+';base64,'+data.image_base64);
            else if (data.image_url)
                applyTexFromUrl(idx, data.image_url);
            showMeshAiCard(idx,'completed',`<span style="font-size:11px;color:var(--accent);font-weight:600;">✓ Diterapkan ke ${meshData[idx].name}!</span>`);
        } else {
            showMeshAiCard(idx,'failed',`<span style="font-size:11px;color:var(--danger)">✗ ${data.error||'Gagal'}</span>`);
        }
    } catch(e) {
        showMeshAiCard(idx,'failed',`<span style="font-size:11px;color:var(--danger)">✗ ${e.message}</span>`);
    } finally {
        btn.disabled=false; btn.textContent=`✦ Generate untuk ${meshData[idx].name}`;
    }
}

function showMeshAiCard(idx, type, html) {
    const el=document.getElementById(`mai-card-${idx}`);
    if (!el) return;
    el.style.display='block'; el.className='st-card st-'+type; el.innerHTML=html;
}

function syncMeshAiBal() {
    // Ambil saldo dari elemen yang sudah ada (cai-bal atau bai-bal)
    const bal = document.getElementById('cai-bal')?.textContent
             || document.getElementById('bai-bal')?.textContent
             || '—';
    document.querySelectorAll('[id^="mai-bal-"]').forEach(el=>el.textContent=bal);
}

// ── Select mesh ──────────────────────────────────────────────
function selectMesh(idx) {
    if (activeMeshIdx>=0) {
        document.getElementById(`mc-${activeMeshIdx}`)?.classList.remove('active');
        const old=meshData[activeMeshIdx];
        if (old) { old.material.emissive.set(0,0,0); old.material.needsUpdate=true; }
    }
    activeMeshIdx=idx;
    const md=meshData[idx]; if (!md) return;
    const card=document.getElementById(`mc-${idx}`);
    if (card) { card.classList.add('active'); card.scrollIntoView({behavior:'smooth',block:'nearest'}); }
    md.material.emissive=new THREE.Color(0x222200);
    md.material.emissiveIntensity=0.3; md.material.needsUpdate=true;

    document.getElementById('sec-mesh-ctrl').style.display='block';
    document.getElementById('ctrl-mesh-name').textContent=md.name;
    document.getElementById('right-mesh-name').textContent=md.name;

    const p=md.texParams;
    syncSlider('csl-rot',p.rot,'°');
    syncSlider('csl-sx', p.sx,'',1);
    syncSlider('csl-sy', p.sy,'',1);
    syncSlider('csl-ox', p.ox,'',2);
    syncSlider('csl-oy', p.oy,'',2);
    updateRightPreview(idx);
}

function updateRightPreview(idx) {
    const md=meshData[idx];
    const wrap=document.getElementById('right-prev-wrap');
    const img=document.getElementById('right-prev-img');
    if (md.thumbUrl) { img.src=md.thumbUrl; wrap.style.display='block'; }
    else wrap.style.display='none';
}

// ── Apply texture to mesh ────────────────────────────────────
async function applyTexToMesh(idx, input) {
    const file=input.files[0]; if (!file) return;
    input.value='';
    const md=meshData[idx];
    const url=URL.createObjectURL(file);
    showMeshUploadStatus(idx,' Uploading...','var(--text-muted)');
    const fd=new FormData(); fd.append('design_file',file); fd.append('_token',CSRF);
    try {
        const res=await fetch(`/projects/${SLUG}/upload-design`,{method:'POST',headers:{'Accept':'application/json'},body:fd});
        const data=JSON.parse(await res.text());
        if (data.success) showMeshUploadStatus(idx,'✓ Tersimpan','var(--accent)');
        else showMeshUploadStatus(idx,'✗ '+(data.message||'Gagal'),'var(--danger)');
    } catch(e) { showMeshUploadStatus(idx,'✗ Upload error','var(--danger)'); }
    new THREE.TextureLoader().load(url, tex=>{ applyTexObject(idx,tex,url); URL.revokeObjectURL(url); });
}

function applyTexObject(idx, tex, thumbUrl) {
    const md=meshData[idx];
    if (md.texture) md.texture.dispose();
    const p=md.texParams;
    tex.wrapS=tex.wrapT=THREE.RepeatWrapping;
    tex.repeat.set(p.sx,p.sy); tex.offset.set(p.ox,p.oy);
    tex.rotation=(p.rot*Math.PI)/180; tex.center.set(.5,.5);
    tex.encoding=THREE.sRGBEncoding;
    tex.anisotropy=renderer.capabilities.getMaxAnisotropy();
    tex.needsUpdate=true;
    md.texture=tex; md.thumbUrl=thumbUrl;
    md.material.map=tex; md.material.color.set(0xffffff);
    md.material.depthTest=true; md.material.depthWrite=true;
    md.material.transparent=false; md.material.needsUpdate=true;
    const pw=document.getElementById(`tpw-${idx}`);
    if (pw) { document.getElementById(`tpimg-${idx}`).src=thumbUrl; pw.style.display='block'; }
    document.getElementById(`mc-${idx}`)?.classList.add('has-tex');
    if (idx===activeMeshIdx) updateRightPreview(idx);
    updateTexCount();
}

function applyTexFromBase64(idx, dataUrl) {
    const img=new Image();
    img.onload=()=>{ const t=new THREE.Texture(img); t.needsUpdate=true; applyTexObject(idx,t,dataUrl); };
    img.src=dataUrl;
}
function applyTexFromUrl(idx, url) {
    new THREE.TextureLoader().load(url, tex=>applyTexObject(idx,tex,url));
}

// ── Clear mesh texture ───────────────────────────────────────
function clearMeshTex(idx) {
    const md=meshData[idx]; if (!md) return;
    if (md.texture) { md.texture.dispose(); md.texture=null; }
    md.thumbUrl=null;
    md.material.map=null; md.material.color.set(new THREE.Color(md.color)); md.material.needsUpdate=true;
    const pw=document.getElementById(`tpw-${idx}`);
    if (pw) { document.getElementById(`tpimg-${idx}`).src=''; pw.style.display='none'; }
    document.getElementById(`mc-${idx}`)?.classList.remove('has-tex');
    if (idx===activeMeshIdx) updateRightPreview(idx);
    updateTexCount();
}
function clearActiveMeshTex() { if (activeMeshIdx>=0) clearMeshTex(activeMeshIdx); }

// ── Texture transform (custom) ───────────────────────────────
function updateActiveTexParam(param, val) {
    if (activeMeshIdx<0) return;
    const md=meshData[activeMeshIdx]; md.texParams[param]=val;
    const t=md.texture; if (!t) return;
    const p=md.texParams;
    t.repeat.set(p.sx,p.sy); t.offset.set(p.ox,p.oy);
    t.rotation=(p.rot*Math.PI)/180; t.needsUpdate=true;
}

// ── Custom upload (right panel shortcut) ─────────────────────
function triggerUploadActive() {
    if (activeMeshIdx<0) { alert('Pilih mesh terlebih dahulu.'); return; }
    document.getElementById('right-file-input').click();
}
function handleRightUpload(input) {
    if (activeMeshIdx<0||!input.files[0]) return;
    const dt=new DataTransfer(); dt.items.add(input.files[0]);
    const inp=document.getElementById(`tfi-${activeMeshIdx}`);
    inp.files=dt.files; applyTexToMesh(activeMeshIdx,inp); input.value='';
}

// ── Custom AI generate ───────────────────────────────────────
function toggleCustomChip(btn, style) {
    const on=btn.classList.contains('on');
    document.querySelectorAll('#cchips .chip').forEach(c=>c.classList.remove('on'));
    if (!on) { btn.classList.add('on'); customStyle=style; } else customStyle=null;
}

async function doCustomGenerate() {
    if (activeMeshIdx<0) { alert('Pilih mesh terlebih dahulu.'); return; }
    const prompt=document.getElementById('cai-prompt').value.trim();
    if (!prompt) { alert('Masukkan deskripsi desain.'); return; }
    const btn=document.getElementById('cbtn-gen');
    btn.disabled=true; btn.textContent='Memproses...';
    showCustomAiCard('processing',`
        <div style="display:flex;align-items:center;gap:8px;color:var(--accent);font-weight:600;">
            <div style="width:12px;height:12px;border:2px solid rgba(200,245,66,.3);border-top-color:var(--accent);border-radius:50%;animation:spin .6s linear infinite;"></div>
            AI membuat desain untuk "${meshData[activeMeshIdx].name}"...
        </div>`);
    try {
        const res=await fetch(`/ai/direct/${SLUG}`,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body:JSON.stringify({prompt,style:customStyle,face_key:meshData[activeMeshIdx].name})
        });
        const data=JSON.parse(await res.text());
        if (data.success) {
            document.getElementById('cai-bal').textContent=data.token_balance??'—';
            if (data.image_base64&&data.mime_type)
                applyTexFromBase64(activeMeshIdx,'data:'+data.mime_type+';base64,'+data.image_base64);
            else if (data.image_url)
                applyTexFromUrl(activeMeshIdx,data.image_url);
            showCustomAiCard('completed',`<span style="color:var(--accent);font-weight:600;">✓ Diterapkan ke "${meshData[activeMeshIdx].name}"!</span>`);
        } else {
            showCustomAiCard('failed',`<span style="color:var(--danger)">✗ ${data.error||'Gagal'}</span>`);
        }
    } catch(e) { showCustomAiCard('failed',`<span style="color:var(--danger)">✗ ${e.message}</span>`); }
    finally { btn.disabled=false; btn.textContent='✦ Generate untuk mesh ini'; }
}

function showCustomAiCard(type, html) {
    const el=document.getElementById('cai-status-card');
    el.style.display='block'; el.className='st-card st-'+type; el.innerHTML=html;
}

function switchCustomTab(tab) {
    const isUp=tab==='upload';
    document.getElementById('ctmb-upload').classList.toggle('on', isUp);
    document.getElementById('ctmb-ai').classList.toggle('on', !isUp);
    document.getElementById('cdt-upload').style.display=isUp?'block':'none';
    document.getElementById('cdt-ai').style.display    =isUp?'none':'block';
}

// ── Raycast click to select mesh ─────────────────────────────
function onCanvasClick(e) {
    if (!customGroup||editorMode!=='custom') return;
    const wrap=document.getElementById('canvas-wrap');
    const rect=wrap.getBoundingClientRect();
    const mouse=new THREE.Vector2(
        ((e.clientX-rect.left)/rect.width)*2-1,
        -((e.clientY-rect.top)/rect.height)*2+1
    );
    raycaster.setFromCamera(mouse,camera);
    const hits=raycaster.intersectObjects(meshData.map(m=>m.mesh).filter(Boolean),false);
    if (hits.length>0) {
        const idx=meshData.findIndex(m=>m.mesh===hits[0].object);
        if (idx>=0) selectMesh(idx);
    }
}

// ══════════════════════════════════════════════════════════════
// SHARED: Material, Lighting, Camera, Etc.
// ══════════════════════════════════════════════════════════════

function setMatAll(prop, val) {
    // Apply to both builtin and custom materials
    Object.values(builtinMats).forEach(m=>{ m[prop]=val; m.needsUpdate=true; });
    meshData.forEach(md=>{ md.material[prop]=val; md.material.needsUpdate=true; });
}

function setEnv(preset, btn) {
    document.querySelectorAll('.env-btn').forEach(b=>b.classList.remove('on'));
    if (btn) btn.classList.add('on');
    lights.forEach(l=>scene.remove(l)); lights=[];
    const cfgs = {
        studio:[
            {t:'amb',c:0xffffff,i:0.45},
            {t:'dir',c:0xffffff,i:2.8,p:[4,6,5],sh:true},
            {t:'dir',c:0xc8e0ff,i:0.7,p:[-4,2,-3]},
            {t:'pt', c:0xffffff,i:0.5,p:[0,5,3]},
        ],
        outdoor:[
            {t:'amb',c:0x87ceeb,i:0.7},
            {t:'hemi',sky:0x87ceeb,gnd:0x8b7355,i:0.6},
            {t:'dir',c:0xfff3e0,i:3,p:[5,8,3],sh:true},
        ],
        warm:[
            {t:'amb',c:0xfff0d0,i:0.5},
            {t:'dir',c:0xffaa44,i:3,p:[3,6,3],sh:true},
            {t:'pt', c:0xff7700,i:1.2,p:[-2,1,2]},
        ],
        cold:[
            {t:'amb',c:0xd0e8ff,i:0.5},
            {t:'dir',c:0xaaddff,i:3,p:[-4,6,3],sh:true},
            {t:'pt', c:0x4488ff,i:1,p:[3,2,-2]},
        ],
        neon:[
            {t:'amb',c:0x060612,i:0.15},
            {t:'pt',c:0xff00ff,i:5,p:[-4,2,2]},
            {t:'pt',c:0x00ffff,i:5,p:[4,2,-2]},
            {t:'pt',c:0xc8f542,i:2,p:[0,5,0]},
        ],
        dark:[
            {t:'amb',c:0x111111,i:0.1},
            {t:'spot',c:0xffffff,i:6,p:[0,7,4],sh:true},
            {t:'pt',c:0xc8f542,i:1.5,p:[4,0,2]},
        ],
    };
    (cfgs[preset]||cfgs.studio).forEach(cfg=>{
        let l;
        if      (cfg.t==='amb') l=new THREE.AmbientLight(cfg.c,cfg.i);
        else if (cfg.t==='hemi') l=new THREE.HemisphereLight(cfg.sky,cfg.gnd,cfg.i);
        else if (cfg.t==='dir') {
            l=new THREE.DirectionalLight(cfg.c,cfg.i); l.position.set(...cfg.p);
            if (cfg.sh) { l.castShadow=true; l.shadow.mapSize.set(2048,2048);
                Object.assign(l.shadow.camera,{left:-4,right:4,top:4,bottom:-4,near:.1,far:30}); }
        }
        else if (cfg.t==='pt') { l=new THREE.PointLight(cfg.c,cfg.i,30); l.position.set(...cfg.p); }
        else if (cfg.t==='spot') {
            l=new THREE.SpotLight(cfg.c,cfg.i); l.position.set(...cfg.p);
            l.angle=Math.PI/7; l.penumbra=0.3;
            if (cfg.sh) { l.castShadow=true; l.shadow.mapSize.set(2048,2048); }
        }
        if (l) { scene.add(l); lights.push(l); }
    });
}

// ── Camera ───────────────────────────────────────────────────
function updateCam() {
    camera.position.set(
        sph.r*Math.sin(sph.phi)*Math.sin(sph.theta)+pan.x,
        sph.r*Math.cos(sph.phi)+pan.y,
        sph.r*Math.sin(sph.phi)*Math.cos(sph.theta)
    );
    camera.lookAt(pan.x,pan.y,0);
}
function resetCamera() {
    sph={theta:.5,phi:1.1,r:3.8}; pan={x:0,y:0};
    if (builtinGroup) builtinGroup.rotation.set(0,0,0);
    if (customGroup)  customGroup.rotation.set(0,0,0);
    updateCam();
}

function setupMouseEvents(canvas) {
    canvas.addEventListener('mousedown',e=>{
        isDrag=true; isRDrag=e.button===2;
        clickStart=prevM={x:e.clientX,y:e.clientY}; e.preventDefault();
    });
    canvas.addEventListener('contextmenu',e=>e.preventDefault());
    window.addEventListener('mousemove',e=>{
        if (!isDrag) return;
        const dx=e.clientX-prevM.x, dy=e.clientY-prevM.y;
        prevM={x:e.clientX,y:e.clientY};
        if (isRDrag) { pan.x-=dx*.004; pan.y+=dy*.004; }
        else { sph.theta-=dx*.008; sph.phi=Math.max(.05,Math.min(Math.PI-.05,sph.phi+dy*.008)); }
        updateCam();
    });
    window.addEventListener('mouseup',e=>{
        const d=Math.hypot(e.clientX-clickStart.x,e.clientY-clickStart.y);
        if (d<5&&!isRDrag) onCanvasClick(e);
        isDrag=false;
    });
    canvas.addEventListener('wheel',e=>{
        e.preventDefault(); sph.r=Math.max(.5,Math.min(20,sph.r+e.deltaY*.004)); updateCam();
    },{passive:false});
}

function setupTouchEvents(canvas) {
    let lpd=null;
    canvas.addEventListener('touchstart',e=>{
        if (e.touches.length===1) { isDrag=true; prevM={x:e.touches[0].clientX,y:e.touches[0].clientY}; }
        else if (e.touches.length===2)
            lpd=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);
        e.preventDefault();
    },{passive:false});
    canvas.addEventListener('touchmove',e=>{
        if (e.touches.length===1&&isDrag) {
            const dx=e.touches[0].clientX-prevM.x, dy=e.touches[0].clientY-prevM.y;
            prevM={x:e.touches[0].clientX,y:e.touches[0].clientY};
            sph.theta-=dx*.01; sph.phi=Math.max(.05,Math.min(Math.PI-.05,sph.phi+dy*.01)); updateCam();
        } else if (e.touches.length===2&&lpd) {
            const d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);
            sph.r=Math.max(.5,Math.min(20,sph.r-(d-lpd)*.01)); lpd=d; updateCam();
        }
        e.preventDefault();
    },{passive:false});
    canvas.addEventListener('touchend',()=>{ isDrag=false; lpd=null; });
}

function setupCanvasDropzone() {
    const wrap=document.getElementById('canvas-wrap');
    wrap.addEventListener('dragover',e=>e.preventDefault());
    wrap.addEventListener('drop',e=>{
        e.preventDefault();
        const f=e.dataTransfer.files[0]; if (!f) return;
        const ext=f.name.split('.').pop().toLowerCase();
        if (['obj','3ds','glb','gltf'].includes(ext)) {
            const dt=new DataTransfer(); dt.items.add(f);
            const inp=document.getElementById('input-3d-file');
            inp.files=dt.files; loadModelFile(inp);
        } else if (['png','jpg','jpeg','webp'].includes(ext)) {
            if (editorMode==='custom'&&activeMeshIdx>=0) {
                const dt=new DataTransfer(); dt.items.add(f);
                const inp=document.getElementById(`tfi-${activeMeshIdx}`);
                inp.files=dt.files; applyTexToMesh(activeMeshIdx,inp);
            } else if (editorMode==='builtin') {
                const dt=new DataTransfer(); dt.items.add(f);
                const inp=document.getElementById('builtin-file-input');
                inp.files=dt.files; handleBuiltinUpload(inp);
            }
        }
    });
}

// ── Misc ─────────────────────────────────────────────────────
function syncSlider(id, val, suffix='', dec=2) {
    const el=document.getElementById(id); if (!el) return;
    el.value=val;
    const nxt=el.nextElementSibling;
    if (nxt) nxt.textContent=suffix==='°'?Math.round(val)+suffix:(+val).toFixed(dec)+suffix;
}
function toggleAutoRotate() {
    isAutoRotate=!isAutoRotate;
    document.getElementById('btn-autorot').style.color=isAutoRotate?'var(--accent)':'';
}
function toggleWireframe() {
    isWireframe=!isWireframe;
    Object.values(builtinMats).forEach(m=>{ m.wireframe=isWireframe; });
    meshData.forEach(md=>{ md.material.wireframe=isWireframe; });
}
function saveSnapshot() {
    renderer.render(scene,camera);
    const a=document.createElement('a');
    a.download=`packaging-3d-${SLUG}.png`;
    a.href=renderer.domElement.toDataURL('image/png'); a.click();
}
function onResize() {
    const wrap=document.getElementById('canvas-wrap');
    const W=wrap.clientWidth, H=wrap.clientHeight;
    camera.aspect=W/H; camera.updateProjectionMatrix(); renderer.setSize(W,H);
}
function updateTexCount() {
    const a=meshData.filter(m=>m.texture).length, t=meshData.length;
    document.getElementById('st-tex').textContent=`${a} / ${t}`;
}
function showLoading(msg) {
    document.getElementById('loading-msg').textContent=msg;
    document.getElementById('loading-overlay').style.display='flex';
}
function hideLoading() { document.getElementById('loading-overlay').style.display='none'; }
function setProgress(show, msg='', pct=0) {
    const el=document.getElementById('prog-wrap');
    if (!show) { el.classList.remove('show'); return; }
    el.classList.add('show');
    document.getElementById('prog-label').textContent=msg;
    document.getElementById('prog-fill').style.width=pct+'%';
}
function showMeshUploadStatus(idx, msg, color) {
    const el=document.getElementById(`ust-${idx}`); if (!el) return;
    el.style.display='block'; el.innerHTML=`<span style="color:${color}">${msg}</span>`;
    if (color==='var(--accent)') setTimeout(()=>el.style.display='none',3000);
}

// ── Animate ──────────────────────────────────────────────────
function animate() {
    requestAnimationFrame(animate);
    if (isAutoRotate) {
        if (builtinGroup&&editorMode==='builtin') builtinGroup.rotation.y+=.005;
        if (customGroup &&editorMode==='custom')  customGroup.rotation.y+=.005;
    }
    renderer.render(scene,camera);
}

window.addEventListener('load', initThree);
</script>
@endisset
@endpush