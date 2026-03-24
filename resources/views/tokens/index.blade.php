@extends('layouts.app')

@section('title', $project->name . ' — Editor')

@section('breadcrumb')
<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;">Dashboard</a>
    <span class="text-muted">/</span>
    <span style="font-weight:600;">{{ $project->name }}</span>
    @if($project->productModel)
        <span class="badge badge-free" style="font-size:10px;">{{ $project->productModel->name }}</span>
    @endif
</div>
@endsection

@section('topbar-actions')
<button id="btn-save" class="btn btn-ghost btn-sm" onclick="saveCanvas()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
    Simpan
</button>
<form method="POST" action="{{ route('projects.destroy', $project->slug) }}"
    onsubmit="return confirm('Hapus proyek ini?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
</form>
@endsection

@push('styles')
<style>
/* Override main-content padding for editor */
.main-content { overflow: hidden; }
.page-content { padding: 0; max-width: 100%; }

/* Editor layout */
.editor-layout {
    display: grid;
    grid-template-columns: 300px 1fr 280px;
    height: calc(100vh - 64px);
    overflow: hidden;
}

/* Left panel */
.panel-left {
    border-right: 1px solid var(--border);
    background: var(--bg2);
    display: flex; flex-direction: column;
    overflow-y: auto;
}
.panel-section {
    border-bottom: 1px solid var(--border);
    padding: 16px;
}
.panel-section-title {
    font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;
    color: var(--text-muted); margin-bottom: 12px;
}

/* Canvas area */
.canvas-area {
    background: var(--bg3);
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.canvas-wrapper {
    position: relative;
    box-shadow: 0 20px 80px rgba(0,0,0,0.6);
    border-radius: 4px;
}
.canvas-bg-grid {
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
}

/* Right panel */
.panel-right {
    border-left: 1px solid var(--border);
    background: var(--bg2);
    display: flex; flex-direction: column;
    overflow-y: auto;
}

/* Tabs */
.tab-row {
    display: flex; gap: 2px;
    padding: 8px;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
}
.tab-btn {
    flex: 1; padding: 8px 6px; border: none; background: none;
    color: var(--text-muted); font-size: 12px; font-weight: 500;
    cursor: pointer; border-radius: 6px; transition: all 0.15s;
    font-family: 'DM Sans', sans-serif;
}
.tab-btn.active { background: var(--bg3); color: var(--text); }
.tab-btn:hover:not(.active) { color: var(--text); }

.tab-content { display: none; }
.tab-content.active { display: block; }

/* Upload zone */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-sm);
    padding: 24px 16px; text-align: center;
    cursor: pointer; transition: all 0.15s;
}
.upload-zone:hover { border-color: var(--accent); background: var(--accent-dim); }

/* AI Panel */
.style-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.style-chip {
    padding: 4px 10px; border-radius: 99px; font-size: 12px;
    border: 1px solid var(--border); cursor: pointer; transition: all 0.15s;
    background: none; color: var(--text-muted); font-family: 'DM Sans', sans-serif;
}
.style-chip:hover { border-color: var(--border-hover); color: var(--text); }
.style-chip.selected { border-color: var(--accent); background: var(--accent-dim); color: var(--accent); }

/* Job status */
.job-status-card {
    border-radius: var(--radius-sm); padding: 12px; font-size: 13px;
    margin-bottom: 12px;
}
.job-status-pending  { background: rgba(255,165,2,0.1); border: 1px solid rgba(255,165,2,0.3); }
.job-status-processing { background: rgba(200,245,66,0.08); border: 1px solid rgba(200,245,66,0.2); }
.job-status-completed { background: rgba(200,245,66,0.1); border: 1px solid rgba(200,245,66,0.3); }
.job-status-failed   { background: rgba(255,71,87,0.1); border: 1px solid rgba(255,71,87,0.3); }

/* Render panel */
.render-info { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; line-height: 1.6; }
.token-cost { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--bg3); border-radius: var(--radius-sm); margin-bottom: 12px; font-size: 13px; }
</style>
@endpush

@section('content')

{{-- Flash alerts here too --}}
@if(session('success'))
<div class="alert alert-success" style="position:fixed;top:72px;left:50%;transform:translateX(-50%);z-index:500;min-width:300px;">
    {{ session('success') }}
</div>
@endif

<div class="editor-layout">

    {{-- ─── LEFT PANEL ────────────────────────────────────────────────────────── --}}
    <div class="panel-left">
        {{-- Product info --}}
        <div class="panel-section">
            <div class="panel-section-title">Model Produk</div>
            @if($project->productModel)
            <div style="display:flex;align-items:center;gap:12px;">
                @if($project->productModel->thumbnail_path)
                <img src="{{ $project->productModel->thumbnail_url }}" style="width:48px;height:48px;object-fit:contain;background:var(--bg3);border-radius:8px;">
                @else
                <div style="width:48px;height:48px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;opacity:0.4;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                @endif
                <div>
                    <div style="font-weight:600;font-size:14px;">{{ $project->productModel->name }}</div>
                    <div class="text-sm text-muted">{{ ucfirst($project->productModel->category) }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Design source tabs --}}
        <div class="panel-section">
            <div class="panel-section-title">Sumber Desain</div>
            <div class="tab-row" style="padding:0;border:none;background:none;margin-bottom:12px;">
                <button class="tab-btn active" onclick="switchTab(this,'tab-upload')">Upload</button>
                <button class="tab-btn" onclick="switchTab(this,'tab-ai')">Generate AI</button>
            </div>

            {{-- Upload tab --}}
            <div id="tab-upload" class="tab-content active">
                <div class="upload-zone" id="upload-zone" onclick="document.getElementById('file-input').click()">
                    <input type="file" id="file-input" accept=".png,.jpg,.jpeg,.svg,.pdf" style="display:none" onchange="handleUpload(this)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 10px;opacity:0.4;display:block">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    <div style="font-size:13px; font-weight:600; margin-bottom:4px;">Klik untuk upload</div>
                    <div class="text-sm text-muted">PNG, JPG, SVG, PDF · max 10MB</div>
                </div>
                <div id="upload-status" style="margin-top:10px;font-size:13px;display:none;"></div>

                @if($project->design_file_path)
                <div style="margin-top:12px; font-size:13px; color:var(--accent);">
                    ✓ Desain sudah diupload
                </div>
                @endif
            </div>

            {{-- AI Generate tab --}}
            <div id="tab-ai" class="tab-content">
                @if($project->latestAiJob)
                    @php $job = $project->latestAiJob; @endphp
                    <div class="job-status-card job-status-{{ $job->status }}" id="ai-job-status">
                        @if($job->status === 'pending')
                            <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--warning);">
                                <div class="spinner"></div> Antrian #{{ $job->queue_position }}
                            </div>
                            <div class="text-sm" style="margin-top:6px;color:var(--text-muted);">Sedang menunggu diproses...</div>
                        @elseif($job->status === 'processing')
                            <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--accent);">
                                <div class="spinner"></div> Sedang dibuat...
                            </div>
                        @elseif($job->status === 'completed')
                            <div style="font-weight:600;color:var(--accent);">✓ Generate selesai!</div>
                        @elseif($job->status === 'failed')
                            <div style="font-weight:600;color:var(--danger);">✗ Generate gagal</div>
                            <div class="text-sm" style="margin-top:4px;color:var(--text-muted);">{{ $job->error_message }}</div>
                        @elseif($job->status === 'cancelled')
                            <div style="color:var(--text-muted);">— Dibatalkan</div>
                        @endif
                    </div>
                @endif

                <textarea class="form-textarea" id="ai-prompt" placeholder="Describe your packaging design...&#10;&#10;ex: Minimalist tea packaging with Japanese aesthetic, earthy green tones, elegant typography" style="font-size:13px;"></textarea>

                <div style="margin-top:10px;margin-bottom:6px;font-size:12px;color:var(--text-muted);font-weight:500;">Style</div>
                <div class="style-chips" id="style-chips">
                    @foreach(['minimalist','bold','elegant','playful','modern','vintage','eco'] as $style)
                    <button class="style-chip" onclick="toggleStyle(this, '{{ $style }}')">{{ ucfirst($style) }}</button>
                    @endforeach
                </div>

                <div class="form-group">
                    <label class="form-label">Palet Warna (opsional)</label>
                    <input type="text" class="form-input" id="ai-color" placeholder="mis: deep green, gold, cream" style="font-size:13px;">
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label class="form-label">Target Audiens (opsional)</label>
                    <input type="text" class="form-input" id="ai-audience" placeholder="mis: premium, eco-conscious" style="font-size:13px;">
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--text-muted);margin-bottom:10px;">
                    <span>Biaya: <strong style="color:var(--text)">10 token</strong></span>
                    <span>Saldo: <strong id="ai-token-balance" style="color:var(--accent)">{{ $user->token_balance }}</strong></span>
                </div>

                <button class="btn btn-primary w-full" id="btn-generate" onclick="generateAI()" style="justify-content:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Generate Desain
                </button>

                @if($user->isFree())
                <div class="text-sm text-muted" style="margin-top:8px;text-align:center;">
                    Free user: generate masuk antrian
                </div>
                @endif
            </div>
        </div>

        {{-- Canvas tools --}}
        <div class="panel-section">
            <div class="panel-section-title">Canvas</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button class="btn btn-ghost btn-sm" onclick="canvasAddText()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                    Tambah Teks
                </button>
                <button class="btn btn-ghost btn-sm" onclick="canvasClear()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    Clear Canvas
                </button>
                <a href="{{ route('projects.editor', $project->slug) }}?download=design" class="btn btn-ghost btn-sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Download Flat
                </a>
            </div>
        </div>
    </div>

    {{-- ─── CANVAS ─────────────────────────────────────────────────────────────── --}}
    <div class="canvas-area" id="canvas-area">
        <div class="canvas-bg-grid"></div>
        <div class="canvas-wrapper">
            <canvas id="design-canvas" width="800" height="600" style="display:block;background:white;"></canvas>
        </div>
    </div>

    {{-- ─── RIGHT PANEL ──────────────────────────────────────────────────────── --}}
    <div class="panel-right">

        {{-- Render 3D --}}
        <div class="panel-section">
            <div class="panel-section-title">Render 3D Mockup</div>

            @if($project->render_output_path)
            <img src="{{ $project->render_url }}" style="width:100%;border-radius:var(--radius-sm);margin-bottom:12px;">
            @if($user->isPremium())
            <a href="{{ route('render.download', $project->slug) }}" class="btn btn-primary btn-sm w-full" style="justify-content:center;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Download Render
            </a>
            @else
            <div class="alert alert-warning" style="margin:0; font-size:12px;">
                Download render 3D hanya untuk akun premium
            </div>
            @endif
            @endif

            @php $latestRender = $project->latestRenderJob; @endphp
            @if($latestRender && in_array($latestRender->status, ['pending','processing']))
            <div class="job-status-card job-status-processing" id="render-status-card">
                <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--accent);">
                    <div class="spinner"></div>
                    @if($latestRender->status === 'pending') Antrian render...
                    @else Rendering 3D...
                    @endif
                </div>
            </div>
            @endif

            <div class="render-info">
                Wrap desainmu ke model 3D {{ $project->productModel?->name ?? 'produk' }}. Hasil render siap download.
            </div>
            <div class="token-cost">
                <span class="text-muted">Biaya render:</span>
                <strong>50 token</strong>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">
                Token kamu: <strong style="color:var(--accent)" id="render-token-balance">{{ $user->token_balance }}</strong>
            </div>

            @if($user->isPremium())
            <button class="btn btn-primary w-full" id="btn-render" onclick="requestRender()" style="justify-content:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
                Render 3D Sekarang
            </button>
            @else
            <div style="background:rgba(255,165,2,0.08);border:1px solid rgba(255,165,2,0.2);border-radius:var(--radius-sm);padding:16px;text-align:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffa502" stroke-width="2" style="display:block;margin:0 auto 8px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <div style="font-size:13px; font-weight:600; margin-bottom:4px;">Fitur Premium</div>
                <div class="text-sm text-muted">Upgrade untuk render packaging 3D</div>
                <a href="#" class="btn btn-primary btn-sm" style="margin-top:12px;justify-content:center;display:inline-flex;">
                    Upgrade Sekarang
                </a>
            </div>
            @endif
        </div>

        {{-- Project info --}}
        <div class="panel-section">
            <div class="panel-section-title">Info Proyek</div>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                <div class="flex items-center justify-between">
                    <span class="text-muted">Status</span>
                    @php
                    $statusMap = ['draft'=>'badge-free','rendering'=>'badge-pending','completed'=>'badge-success','failed'=>'badge-failed'];
                    @endphp
                    <span class="badge {{ $statusMap[$project->status] ?? '' }}">{{ ucfirst($project->status) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-muted">Sumber Desain</span>
                    <span>{{ $project->design_source ? ucfirst(str_replace('_', ' ', $project->design_source)) : '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-muted">Dibuat</span>
                    <span>{{ $project->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ─── State ─────────────────────────────────────────────────────────────────────
const slug = '{{ $project->slug }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let selectedStyle = null;
let pollingInterval = null;

@if($project->latestAiJob && in_array($project->latestAiJob->status, ['pending','processing']))
    startPollingJob({{ $project->latestAiJob->id }});
@endif

// ─── Tabs ────────────────────────────────────────────────────────────────────
function switchTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}

// ─── Upload ───────────────────────────────────────────────────────────────────
async function handleUpload(input) {
    if (!input.files[0]) return;
    const statusEl = document.getElementById('upload-status');
    statusEl.style.display = 'block';
    statusEl.innerHTML = '<span style="color:var(--text-muted)">⏳ Mengupload...</span>';

    const fd = new FormData();
    fd.append('design_file', input.files[0]);
    fd.append('_token', csrfToken);

    try {
        const res = await fetch(`/projects/${slug}/upload-design`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            statusEl.innerHTML = '<span style="color:var(--accent)">✓ Upload berhasil!</span>';
            // Tampilkan di canvas
            const img = new Image();
            img.onload = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const scale = Math.min(canvas.width/img.width, canvas.height/img.height);
                const w = img.width * scale; const h = img.height * scale;
                ctx.drawImage(img, (canvas.width-w)/2, (canvas.height-h)/2, w, h);
            };
            img.src = data.design_url;
        } else {
            statusEl.innerHTML = '<span style="color:var(--danger)">✗ Upload gagal</span>';
        }
    } catch(e) {
        statusEl.innerHTML = '<span style="color:var(--danger)">✗ Error</span>';
    }
}

// ─── Style chip ───────────────────────────────────────────────────────────────
function toggleStyle(btn, style) {
    document.querySelectorAll('.style-chip').forEach(c => c.classList.remove('selected'));
    if (selectedStyle === style) { selectedStyle = null; return; }
    btn.classList.add('selected');
    selectedStyle = style;
}

// ─── AI Generate ─────────────────────────────────────────────────────────────
async function generateAI() {
    const prompt = document.getElementById('ai-prompt').value.trim();
    if (!prompt) { alert('Masukkan prompt desain terlebih dahulu.'); return; }

    const btn = document.getElementById('btn-generate');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Mengirim...';

    try {
        const res = await fetch(`/ai/generate/${slug}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                prompt,
                style: selectedStyle,
                color_palette: document.getElementById('ai-color').value || null,
                target_audience: document.getElementById('ai-audience').value || null,
            })
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('ai-token-balance').textContent = data.token_balance || '—';
            showJobStatus(data);
            if (data.job_id) startPollingJob(data.job_id);
        } else {
            alert(data.error || 'Gagal mengirim request');
        }
    } catch(e) {
        alert('Error: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> Generate Desain';
    }
}

function showJobStatus(data) {
    let html = '';
    if (data.is_queued) {
        html = `<div class="job-status-card job-status-pending" id="ai-job-status">
            <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--warning);">
                <div class="spinner"></div> Antrian #${data.queue_position}
            </div>
            <div class="text-sm" style="margin-top:6px;color:var(--text-muted);">Sedang menunggu diproses...</div>
        </div>`;
    } else {
        html = `<div class="job-status-card job-status-processing" id="ai-job-status">
            <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--accent);">
                <div class="spinner"></div> Sedang dibuat...
            </div>
        </div>`;
    }
    const existing = document.getElementById('ai-job-status');
    const tab = document.getElementById('tab-ai');
    if (existing) existing.outerHTML = html;
    else tab.insertAdjacentHTML('afterbegin', html);
}

function startPollingJob(jobId) {
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(async () => {
        try {
            const res = await fetch(`/ai/status/${jobId}`);
            const data = await res.json();
            if (data.status === 'completed') {
                clearInterval(pollingInterval);
                const el = document.getElementById('ai-job-status');
                if (el) el.outerHTML = `<div class="job-status-card job-status-completed"><div style="font-weight:600;color:var(--accent);">✓ Generate selesai! Desain telah diterapkan ke canvas.</div></div>`;
                // Load result to canvas
                if (data.result_url) {
                    const img = new Image();
                    img.onload = () => {
                        ctx.clearRect(0,0,canvas.width,canvas.height);
                        const scale = Math.min(canvas.width/img.width, canvas.height/img.height);
                        const w = img.width*scale, h = img.height*scale;
                        ctx.drawImage(img, (canvas.width-w)/2, (canvas.height-h)/2, w, h);
                    };
                    img.src = data.result_url;
                }
            } else if (data.status === 'failed' || data.status === 'cancelled') {
                clearInterval(pollingInterval);
                const el = document.getElementById('ai-job-status');
                if (el) el.outerHTML = `<div class="job-status-card job-status-failed"><div style="font-weight:600;color:var(--danger);">✗ Generate gagal. Token dikembalikan.</div></div>`;
            }
        } catch(e) {}
    }, 3000);
}

// ─── Canvas ───────────────────────────────────────────────────────────────────
const canvas = document.getElementById('design-canvas');
const ctx = canvas.getContext('2d');

// Load existing design
@if($project->design_file_path)
(function() {
    const img = new Image();
    img.onload = () => {
        ctx.clearRect(0,0,canvas.width,canvas.height);
        const scale = Math.min(canvas.width/img.width, canvas.height/img.height);
        const w = img.width*scale, h = img.height*scale;
        ctx.drawImage(img, (canvas.width-w)/2, (canvas.height-h)/2, w, h);
    };
    img.src = '{{ asset("storage/".$project->design_file_path) }}';
})();
@endif

function canvasAddText() {
    const text = prompt('Masukkan teks:');
    if (!text) return;
    ctx.font = 'bold 32px Syne, sans-serif';
    ctx.fillStyle = '#1a1a1f';
    ctx.textAlign = 'center';
    ctx.fillText(text, canvas.width/2, canvas.height/2);
}

function canvasClear() {
    if (!confirm('Hapus semua dari canvas?')) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

async function saveCanvas() {
    const canvasData = canvas.toDataURL('image/png');
    const btn = document.getElementById('btn-save');
    btn.textContent = '⏳ Menyimpan...';
    try {
        const res = await fetch(`/projects/${slug}/save-canvas`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ canvas_data: canvasData })
        });
        const data = await res.json();
        btn.textContent = '✓ Tersimpan';
        setTimeout(() => {
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan';
        }, 2000);
    } catch(e) {
        btn.textContent = 'Error';
    }
}

// ─── Render ───────────────────────────────────────────────────────────────────
async function requestRender() {
    const btn = document.getElementById('btn-render');
    if (!btn) return;
    if (!confirm('Render 3D menggunakan 50 token. Lanjutkan?')) return;
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Mengirim...';

    try {
        const res = await fetch(`/render/${slug}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('render-token-balance').textContent = data.token_balance || '—';
            btn.innerHTML = '⏳ Sedang di-render...';
        } else {
            alert(data.error || 'Gagal memulai render');
            btn.disabled = false;
            btn.innerHTML = 'Render 3D Sekarang';
        }
    } catch(e) {
        alert('Error: ' + e.message);
        btn.disabled = false;
    }
}

// Drag-drop upload
const zone = document.getElementById('upload-zone');
if (zone) {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--accent)'; });
    zone.addEventListener('dragleave', () => { zone.style.borderColor = 'var(--border)'; });
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.style.borderColor = 'var(--border)';
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('file-input').files = dt.files;
            handleUpload(document.getElementById('file-input'));
        }
    });
}
</script>
@endpush