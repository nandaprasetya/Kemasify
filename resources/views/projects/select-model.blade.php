@extends('layouts.app')

@section('title', 'Pilih Model Produk')

@section('breadcrumb')
<div class="flex items-center gap-2" style="font-size:13.5px;">
    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;">Dashboard</a>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
    <span style="color:var(--text); font-weight:600;">Proyek Baru</span>
</div>
@endsection

@push('styles')
<style>
/* ─── Page header ────────────────────────────────────── */
.page-header { margin-bottom: 28px; }
.page-header h1 { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
.page-header p { color: var(--text-muted); font-size: 14px; }

/* ─── Layout ─────────────────────────────────────────── */
.select-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 28px;
    align-items: start;
}

/* ─── Category ───────────────────────────────────────── */
.category-section { margin-bottom: 28px; }
.category-label {
    font-size: 10.5px; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--text-muted); margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.category-label::after {
    content: '';
    flex: 1; height: 1px;
    background: var(--border);
}

/* ─── Model grid ─────────────────────────────────────── */
.model-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}
.model-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.18s;
    overflow: hidden;
    position: relative;
}
.model-card:hover:not(.locked) {
    border-color: var(--purple-border);
    transform: translateY(-2px);
    background: var(--bg-card-hover);
}
.model-card.selected {
    border-color: var(--purple);
    background: rgba(137,82,255,0.06);
    box-shadow: 0 0 0 3px rgba(137,82,255,0.15);
}
.model-card.locked { opacity: 0.45; cursor: not-allowed; }

.model-thumb {
    aspect-ratio: 1;
    background: var(--bg-card-hover);
    display: flex; align-items: center; justify-content: center;
    padding: 12px;
}
.model-thumb img { width: 100%; height: 100%; object-fit: contain; }
.model-thumb svg { opacity: 0.2; }

.model-body { padding: 10px 12px 12px; }
.model-name {
    font-family: 'Syne', sans-serif;
    font-size: 12.5px; font-weight: 700;
    line-height: 1.3;
}
.model-cat { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

.model-check {
    position: absolute; top: 8px; right: 8px;
    width: 20px; height: 20px;
    background: var(--purple); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transform: scale(0.7);
    transition: all 0.18s;
}
.model-card.selected .model-check { opacity: 1; transform: scale(1); }

.lock-badge {
    position: absolute; top: 8px; right: 8px;
    width: 22px; height: 22px;
    background: rgba(255,165,2,0.15);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
}

/* ─── Form panel ─────────────────────────────────────── */
.form-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 22px;
    position: sticky;
    top: calc(var(--topbar-h) + 16px);
}
.form-panel-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.form-panel-sub { font-size: 12.5px; color: var(--text-muted); margin-bottom: 18px; }

.selected-model-box {
    background: var(--bg-card-hover);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 11px 13px;
    margin-bottom: 18px;
    display: none;
}
.selected-model-box-label { font-size: 11px; color: var(--text-muted); margin-bottom: 3px; }
.selected-model-box-name { font-weight: 600; font-size: 13.5px; }

.btn-create {
    width: 100%;
    justify-content: center;
    padding: 11px;
    font-size: 14px;
    opacity: 0.45;
    cursor: not-allowed;
    pointer-events: none;
    transition: all 0.2s;
}
.btn-create.enabled {
    opacity: 1;
    cursor: pointer;
    pointer-events: all;
}

/* ─── Responsive ─────────────────────────────────────── */
@media (max-width: 900px) {
    .select-layout {
        grid-template-columns: 1fr;
    }
    .form-panel {
        position: static;
        order: -1;
    }
    .model-grid {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    }
}
@media (max-width: 480px) {
    .model-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .model-body { padding: 8px 10px 10px; }
    .model-name { font-size: 11px; }
}
@media (max-width: 360px) {
    .model-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')

<div class="page-header">
    <h1>Pilih Model Produk</h1>
    <p>Pilih template packaging yang akan kamu desain dengan AI</p>
</div>

<div class="select-layout">

    {{-- ─── Model picker ──────────────────────────────── --}}
    <div>
        @foreach($models as $category => $categoryModels)
        <div class="category-section">
            <div class="category-label">{{ ucfirst($category) }}</div>
            <div class="model-grid">
                @foreach($categoryModels as $model)
                <div class="model-card"
                    data-model-id="{{ $model->id }}"
                    data-model-name="{{ $model->name }}"
                    onclick="selectModel(this)">
                    <div class="model-thumb">
                        @if($model->thumbnail_path)
                            <img src="{{ $model->thumbnail_url }}" alt="{{ $model->name }}">
                        @else
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                    </div>
                    <div class="model-body">
                        <div class="model-name">{{ $model->name }}</div>
                        <div class="model-cat">{{ $model->description ?? ucfirst($model->category) }}</div>
                    </div>
                    <div class="model-check">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                </div>
                @endforeach

                @if($user->isFree())
                    @foreach($premiumModels->get($category, []) as $model)
                    <div class="model-card locked">
                        <div class="model-thumb">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="model-body">
                            <div class="model-name">{{ $model->name }}</div>
                            <div class="model-cat">Premium only</div>
                        </div>
                        <div class="lock-badge">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#ffa502" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach

        @if($models->isEmpty())
        <div style="text-align:center; padding:60px; color:var(--text-muted);">
            <p>Belum ada model produk tersedia.</p>
        </div>
        @endif
    </div>

    {{-- ─── Form panel ─────────────────────────────────── --}}
    <div class="form-panel">
        <div class="form-panel-title">Detail Proyek</div>
        <div class="form-panel-sub">Beri nama proyekmu untuk mulai</div>

        <div class="selected-model-box" id="selected-model-info">
            <div class="selected-model-box-label">Model dipilih</div>
            <div class="selected-model-box-name" id="selected-model-name"></div>
        </div>

        <p class="text-sm text-muted" id="no-model-msg" style="margin-bottom:16px;">
            ← Pilih model terlebih dahulu
        </p>

        <form method="POST" action="{{ route('projects.create') }}" id="project-form">
            @csrf
            <input type="hidden" name="product_model_id" id="model-id-input">
            <div class="form-group">
                <label class="form-label">Nama Proyek</label>
                <input type="text" name="name" class="form-input"
                    placeholder="mis: Kemasan Teh Hijau Premium"
                    required maxlength="100">
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" id="btn-create" class="btn btn-primary btn-create">
                Mulai Desain →
            </button>
        </form>

        @if($user->isFree())
        <div class="alert alert-warning" style="margin-top:14px; margin-bottom:0; font-size:12.5px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            <div><strong>Free Plan:</strong> Saldo token: <strong>{{ $user->token_balance }}</strong></div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
let selectedCard = null;

function selectModel(card) {
    if (card.classList.contains('locked')) return;
    if (selectedCard) selectedCard.classList.remove('selected');
    card.classList.add('selected');
    selectedCard = card;

    document.getElementById('model-id-input').value = card.dataset.modelId;
    document.getElementById('selected-model-name').textContent = card.dataset.modelName;
    document.getElementById('selected-model-info').style.display = 'block';
    document.getElementById('no-model-msg').style.display = 'none';
    document.getElementById('btn-create').classList.add('enabled');
}
</script>
@endpush

@endsection