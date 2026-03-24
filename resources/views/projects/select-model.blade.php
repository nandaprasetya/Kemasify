@extends('layouts.app')

@section('title', 'Pilih Model Produk')

@section('breadcrumb')
<div class="flex items-center gap-2 text-sm text-muted">
    <a href="{{ route('dashboard') }}" style="color:var(--text-muted);text-decoration:none;">Dashboard</a>
    <span>/</span>
    <span style="color:var(--text)">Proyek Baru</span>
</div>
@endsection

@push('styles')
<style>
.model-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
.model-card {
    background: var(--bg2); border: 2px solid var(--border); border-radius: var(--radius);
    cursor: pointer; transition: all 0.15s; overflow: hidden;
    position: relative;
}
.model-card:hover:not(.locked) { border-color: var(--accent); transform: translateY(-2px); }
.model-card.selected { border-color: var(--accent); background: rgba(200,245,66,0.04); }
.model-card.locked { opacity: 0.5; cursor: not-allowed; }
.model-thumb {
    aspect-ratio: 1; background: var(--bg3);
    display: flex; align-items: center; justify-content: center;
}
.model-thumb img { width: 80%; height: 80%; object-fit: contain; }
.model-thumb svg { opacity: 0.25; }
.model-body { padding: 14px; }
.model-name { font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700; }
.model-cat { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
.model-check {
    position: absolute; top: 10px; right: 10px;
    width: 22px; height: 22px; background: var(--accent); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transform: scale(0.8); transition: all 0.15s;
}
.model-card.selected .model-check { opacity: 1; transform: scale(1); }
.lock-badge {
    position: absolute; top: 10px; right: 10px;
    width: 24px; height: 24px; background: rgba(255,165,2,0.2);
    border-radius: 6px; display: flex; align-items: center; justify-content: center;
}

.category-label {
    font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--text-muted); margin-bottom: 12px; margin-top: 32px;
}
.category-label:first-child { margin-top: 0; }

.form-panel {
    background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 28px; position: sticky; top: 80px;
}
.layout-cols { display: grid; grid-template-columns: 1fr 320px; gap: 32px; align-items: start; }
</style>
@endpush

@section('content')

<div style="margin-bottom:32px;">
    <h1 style="font-size:28px; font-weight:800; margin-bottom:8px;">Pilih Model Produk</h1>
    <p class="text-muted">Pilih template packaging yang akan kamu desain</p>
</div>

<div class="layout-cols">
    {{-- Model picker --}}
    <div>
        @foreach($models as $category => $categoryModels)
            <div class="category-label">{{ ucfirst($category) }}</div>
            <div class="model-grid">
                @foreach($categoryModels as $model)
                <div class="model-card" data-model-id="{{ $model->id }}" data-model-name="{{ $model->name }}" onclick="selectModel(this)">
                    <div class="model-thumb">
                        @if($model->thumbnail_path)
                            <img src="{{ $model->thumbnail_url }}" alt="{{ $model->name }}">
                        @else
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                    </div>
                    <div class="model-body">
                        <div class="model-name">{{ $model->name }}</div>
                        <div class="model-cat">{{ $model->description ?? ucfirst($model->category) }}</div>
                    </div>
                    <div class="model-check">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0d0d0f" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                </div>
                @endforeach

                {{-- Locked premium models --}}
                @if($user->isFree())
                    @foreach($premiumModels->get($category, []) as $model)
                    <div class="model-card locked">
                        <div class="model-thumb">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="model-body">
                            <div class="model-name">{{ $model->name }}</div>
                            <div class="model-cat">Premium only</div>
                        </div>
                        <div class="lock-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffa502" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        @endforeach

        @if($models->isEmpty())
        <div style="text-align:center; padding:60px; color:var(--text-muted);">
            <p>Belum ada model produk tersedia.</p>
        </div>
        @endif
    </div>

    {{-- Form panel --}}
    <div class="form-panel">
        <h3 style="font-size:16px; font-weight:700; margin-bottom:6px;">Detail Proyek</h3>
        <p class="text-sm text-muted mb-4">Beri nama proyekmu untuk mulai</p>

        <div id="selected-model-info" style="display:none; margin-bottom:20px;">
            <div style="background:var(--bg3); border:1px solid var(--border); border-radius:9px; padding:12px 14px;">
                <div class="text-sm text-muted">Model dipilih:</div>
                <div id="selected-model-name" style="font-weight:600; margin-top:4px;"></div>
            </div>
        </div>
        <div id="no-model-msg" class="text-sm text-muted" style="margin-bottom:20px;">
            ← Pilih model terlebih dahulu
        </div>

        <form method="POST" action="{{ route('projects.create') }}" id="project-form">
            @csrf
            <input type="hidden" name="product_model_id" id="model-id-input">
            <div class="form-group">
                <label class="form-label">Nama Proyek</label>
                <input type="text" name="name" class="form-input" placeholder="mis: Kemasan Teh Hijau Premium"
                    required maxlength="100">
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" id="btn-create" class="btn btn-primary w-full" disabled style="justify-content:center; opacity:0.5; cursor:not-allowed;">
                Mulai Desain →
            </button>
        </form>

        @if($user->isFree())
        <div class="alert alert-warning" style="margin-top:16px; margin-bottom:0;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            <div>
                <strong>Free Plan:</strong> Generate & render masuk antrian. Saldo token: <strong>{{ $user->token_balance }}</strong>
            </div>
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

    const id = card.dataset.modelId;
    const name = card.dataset.modelName;

    document.getElementById('model-id-input').value = id;
    document.getElementById('selected-model-name').textContent = name;
    document.getElementById('selected-model-info').style.display = 'block';
    document.getElementById('no-model-msg').style.display = 'none';

    const btn = document.getElementById('btn-create');
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';
}
</script>
@endpush
@endsection