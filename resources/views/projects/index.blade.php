@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
<span style="font-size:14px; font-weight:600; color:var(--text);">Dashboard</span>
@endsection

@section('topbar-actions')
<a href="{{ route('projects.select-model') }}" class="btn btn-primary btn-sm">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
    Proyek Baru
</a>
@endsection

@push('styles')
<style>
/* ─── Stats ──────────────────────────────────────────── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 36px;
}
.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    transition: border-color 0.15s;
}
.stat-card:hover { border-color: var(--border-hover); }
.stat-label {
    font-size: 11px; color: var(--text-muted);
    margin-bottom: 10px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.07em;
}
.stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 30px; font-weight: 800; line-height: 1;
}
.stat-sub { font-size: 11.5px; color: var(--text-muted); margin-top: 8px; }

/* ─── Section header ─────────────────────────────────── */
.section-header {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}
.section-title { font-size: 18px; font-weight: 700; }

/* ─── Projects grid ──────────────────────────────────── */
.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}
.project-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
    text-decoration: none; color: var(--text);
    display: flex; flex-direction: column;
}
.project-card:hover {
    border-color: var(--purple-border);
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.3);
}
.project-thumb {
    aspect-ratio: 16/10;
    background: var(--bg-card-hover);
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.project-thumb img { width: 100%; height: 100%; object-fit: cover; }
.project-thumb-placeholder {
    display: flex; flex-direction: column;
    align-items: center; gap: 10px;
    color: var(--text-muted); font-size: 12px;
}
.project-thumb-placeholder svg { opacity: 0.2; }
.project-body {
    padding: 14px 16px; flex: 1;
    display: flex; flex-direction: column; gap: 6px;
}
.project-name {
    font-family: 'Syne', sans-serif;
    font-size: 14px; font-weight: 700;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.project-footer {
    padding: 10px 16px;
    border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}

/* ─── Empty state ────────────────────────────────────── */
.empty-state {
    text-align: center; padding: 72px 40px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: var(--radius-lg);
}
.empty-icon {
    width: 60px; height: 60px;
    background: var(--purple-light);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}
.empty-state h3 { font-size: 19px; font-weight: 700; margin-bottom: 8px; }
.empty-state p { color: var(--text-muted); font-size: 13.5px; margin-bottom: 26px; max-width: 300px; margin-left: auto; margin-right: auto; }

/* ─── Responsive ─────────────────────────────────────── */
@media (max-width: 1100px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .stats-row { grid-template-columns: 1fr 1fr; gap: 10px; }
    .stat-value { font-size: 24px; }
    .projects-grid { grid-template-columns: 1fr; }
}
@media (max-width: 400px) {
    .stats-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

{{-- Stats --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Token Tersisa</div>
        <div class="stat-value text-accent">{{ auth()->user()->token_balance }}</div>
        <div class="stat-sub">dari 50 token gratis</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Proyek</div>
        <div class="stat-value">{{ $projects->total() }}</div>
        <div class="stat-sub">desain packaging</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Plan Aktif</div>
        <div class="stat-value" style="font-size:18px; padding-top:6px;">
            @if(auth()->user()->isPremium())
                <span class="badge badge-premium" style="font-size:13px; padding:5px 12px;">✦ Premium</span>
            @else
                <span class="badge badge-free" style="font-size:13px; padding:5px 12px;">Free</span>
            @endif
        </div>
        <div class="stat-sub">
            @if(auth()->user()->isFree())
                <a href="{{ route('payment.pricing') }}" style="color:var(--purple);text-decoration:none;">Upgrade →</a>
            @else
                aktif hingga {{ auth()->user()->plan_expires_at?->format('d M Y') ?? 'selamanya' }}
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Refill Berikutnya</div>
        <div class="stat-value" style="font-size:18px; padding-top:6px;">
            @if(auth()->user()->canRefill())
                <span class="text-accent" style="font-size:15px; font-weight:700;">Sekarang!</span>
            @else
                <span style="font-size:15px;">{{ auth()->user()->refillCountdown() }}</span>
            @endif
        </div>
        <div class="stat-sub">token gratis / 24 jam</div>
    </div>
</div>

{{-- Section title --}}
<div class="section-header">
    <h2 class="section-title">Proyek Desainku</h2>
</div>

{{-- Projects --}}
@if($projects->isEmpty())
<div class="empty-state">
    <div class="empty-icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="1.8">
            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
    </div>
    <h3>Belum ada proyek</h3>
    <p>Mulai buat desain packaging pertamamu dengan AI!</p>
    <a href="{{ route('projects.select-model') }}" class="btn btn-primary btn-lg">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Buat Proyek Baru
    </a>
</div>
@else
<div class="projects-grid">
    @foreach($projects as $project)
    <a href="{{ route('projects.editor', $project->slug) }}" class="project-card">
        <div class="project-thumb">
            @if($project->render_preview_path)
                <img src="{{ asset('storage/'.$project->render_preview_path) }}" alt="{{ $project->name }}">
            @elseif($project->design_file_path)
                <img src="{{ asset('storage/'.$project->design_file_path) }}" alt="{{ $project->name }}">
            @else
                <div class="project-thumb-placeholder">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Belum ada desain</span>
                </div>
            @endif
        </div>
        <div class="project-body">
            <div class="project-name">{{ $project->name }}</div>
            @if($project->productModel)
                <div class="text-sm text-muted">{{ $project->productModel->name }}</div>
            @endif
        </div>
        <div class="project-footer">
            @php
                $statusMap = [
                    'draft'      => ['label' => 'Draft',       'class' => 'badge-free'],
                    'rendering'  => ['label' => '⏳ Rendering', 'class' => 'badge-pending'],
                    'completed'  => ['label' => '✓ Selesai',   'class' => 'badge-success'],
                    'failed'     => ['label' => '✗ Gagal',     'class' => 'badge-failed'],
                ];
                $s = $statusMap[$project->status] ?? ['label' => $project->status, 'class' => 'badge-free'];
            @endphp
            <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
            <span class="text-sm text-muted">{{ $project->created_at->diffForHumans() }}</span>
        </div>
    </a>
    @endforeach
</div>

@if($projects->hasPages())
<div style="margin-top:28px; display:flex; justify-content:center;">
    {{ $projects->links() }}
</div>
@endif
@endif

@endsection