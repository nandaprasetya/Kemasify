@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
<span style="font-size:15px; font-weight:600;">Dashboard</span>
@endsection

@section('topbar-actions')
<a href="{{ route('projects.select-model') }}" class="btn btn-primary">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
    Proyek Baru
</a>
@endsection

@push('styles')
<style>
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 40px; }
.stat-card {
    background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 20px 24px;
}
.stat-label { font-size: 12px; color: var(--text-muted); margin-bottom: 8px; font-weight: 500; }
.stat-value { font-family: 'Syne', sans-serif; font-size: 32px; font-weight: 800; line-height: 1; }
.stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

.projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.project-card {
    background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius);
    overflow: hidden; transition: border-color 0.15s, transform 0.15s;
    text-decoration: none; color: var(--text); display: flex; flex-direction: column;
}
.project-card:hover { border-color: var(--border-hover); transform: translateY(-2px); }

.project-thumb {
    aspect-ratio: 16/10;
    background: var(--bg3);
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.project-thumb img { width: 100%; height: 100%; object-fit: cover; }
.project-thumb-placeholder {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    color: var(--text-muted); font-size: 13px;
}
.project-thumb-placeholder svg { opacity: 0.3; }

.project-body { padding: 16px 18px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
.project-name { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; }
.project-meta { display: flex; align-items: center; gap: 8px; }
.project-footer {
    padding: 12px 18px;
    border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}

.empty-state {
    text-align: center; padding: 80px 40px;
    background: var(--bg2); border: 1px dashed var(--border); border-radius: var(--radius-lg);
}
.empty-state svg { margin: 0 auto 20px; opacity: 0.3; }
.empty-state h3 { font-size: 20px; font-weight: 700; margin-bottom: 10px; }
.empty-state p { color: var(--text-muted); font-size: 14px; margin-bottom: 28px; }
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
        <div class="stat-label">Plan</div>
        <div class="stat-value" style="font-size:22px; margin-top:6px;">
            @if(auth()->user()->isPremium())
                <span class="badge badge-premium" style="font-size:16px; padding:6px 14px;">✦ Premium</span>
            @else
                <span class="badge badge-free" style="font-size:14px; padding:6px 14px;">Free</span>
            @endif
        </div>
        <div class="stat-sub">
            @if(auth()->user()->isFree())
                <a href="#" style="color:var(--accent);text-decoration:none;font-size:12px;">Upgrade →</a>
            @else
                aktif hingga {{ auth()->user()->plan_expires_at?->format('d M Y') ?? 'selamanya' }}
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Refill Berikutnya</div>
        <div class="stat-value" style="font-size:18px; margin-top:4px;">
            @if(auth()->user()->canRefill())
                <span class="text-accent">Sekarang!</span>
            @else
                {{ auth()->user()->refillCountdown() }}
            @endif
        </div>
        <div class="stat-sub">token gratis 24 jam</div>
    </div>
</div>

{{-- Section title --}}
<div class="flex items-center justify-between mb-4">
    <h2 style="font-size:20px; font-weight:700;">Proyek Desainku</h2>
</div>

{{-- Projects --}}
@if($projects->isEmpty())
<div class="empty-state">
    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <h3>Belum ada proyek</h3>
    <p>Mulai buat desain packaging pertamamu!</p>
    <a href="{{ route('projects.select-model') }}" class="btn btn-primary btn-lg">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
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
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Belum ada desain</span>
                </div>
            @endif
        </div>
        <div class="project-body">
            <div class="project-name">{{ $project->name }}</div>
            <div class="project-meta">
                @if($project->productModel)
                    <span class="text-sm text-muted">{{ $project->productModel->name }}</span>
                @endif
            </div>
        </div>
        <div class="project-footer">
            @php
                $statusMap = [
                    'draft' => ['label' => 'Draft', 'class' => ''],
                    'rendering' => ['label' => '⏳ Rendering', 'class' => 'badge-pending'],
                    'completed' => ['label' => '✓ Selesai', 'class' => 'badge-success'],
                    'failed' => ['label' => '✗ Gagal', 'class' => 'badge-failed'],
                ];
                $s = $statusMap[$project->status] ?? ['label' => $project->status, 'class' => ''];
            @endphp
            <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
            <span class="text-sm text-muted">{{ $project->created_at->diffForHumans() }}</span>
        </div>
    </a>
    @endforeach
</div>

{{-- Pagination --}}
@if($projects->hasPages())
<div style="margin-top:32px; display:flex; justify-content:center;">
    {{ $projects->links() }}
</div>
@endif

@endif
@endsection