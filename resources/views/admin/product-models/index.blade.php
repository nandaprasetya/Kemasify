@extends('admin.layouts.admin')
@section('page-title', 'Product Models')

@push('styles')
<style>
    .model-thumb {
        width: 38px; height: 38px;
        object-fit: contain;
        background: var(--bg3);
        border-radius: 7px;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }
    .model-thumb-placeholder {
        width: 38px; height: 38px;
        background: var(--bg3);
        border: 1px solid var(--border);
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .toggle-status-btn {
        border: none;
        cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        background: none;
        padding: 0;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.13s;
        border: 1px solid transparent;
    }
    .status-pill.active {
        background: rgba(34,212,160,0.1);
        color: var(--success);
        border-color: rgba(34,212,160,0.25);
    }
    .status-pill.inactive {
        background: var(--danger-dim);
        color: var(--danger);
        border-color: rgba(255,64,85,0.2);
    }
    .status-pill:hover { opacity: 0.75; }
    .status-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
</style>
@endpush

@section('content')

<div class="section-header">
    <h2 class="section-title">
        Product Models
        <span class="count">{{ number_format($models->total()) }} total</span>
    </h2>
    <a href="{{ route('admin.product-models.create') }}" class="btn btn-primary btn-sm">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Model
    </a>
</div>

<form method="GET" class="filters-bar">
    <input type="text" name="search" class="form-input"
           placeholder="Cari nama model..."
           value="{{ request('search') }}"
           style="flex:1; min-width:160px;">

    <select name="category" class="form-select" style="min-width:130px;">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
            {{ ucfirst($cat) }}
        </option>
        @endforeach
    </select>

    <select name="status" class="form-select" style="min-width:120px;">
        <option value="">Semua Status</option>
        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
    </select>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.product-models.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead><tr>
                <th>Model</th>
                <th>Kategori</th>
                <th class="col-hide-mobile">Dimensi</th>
                <th>Proyek</th>
                <th>Status</th>
                <th>Type</th>
                <th class="col-hide-mobile">Sort</th>
                <th style="text-align:right">Aksi</th>
            </tr></thead>
            <tbody>
            @forelse($models as $model)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        @if($model->thumbnail_path)
                            <img src="{{ asset('storage/'.$model->thumbnail_path) }}"
                                 class="model-thumb"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="model-thumb-placeholder" style="display:none;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--text-muted)"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @else
                            <div class="model-thumb-placeholder">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--text-muted)"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                        <div>
                            <div style="font-weight:600; font-size:13px;">{{ $model->name }}</div>
                            <div class="text-xs text-muted">{{ $model->slug }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-gray">{{ ucfirst($model->category) }}</span>
                </td>
                <td class="text-xs text-muted col-hide-mobile">
                    @if($model->dimensions)
                        {{ $model->dimensions['width'] ?? '?' }}×{{ $model->dimensions['height'] ?? '?' }}×{{ $model->dimensions['depth'] ?? '?' }}
                        <span style="color:var(--bg4)"> cm</span>
                    @else
                        <span style="opacity:.4">—</span>
                    @endif
                </td>
                <td>
                    <span style="font-family:'Syne',sans-serif; font-weight:700; font-size:13px;">
                        {{ $model->design_projects_count }}
                    </span>
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.product-models.toggle', $model) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="toggle-status-btn" title="Klik untuk toggle">
                            <div class="status-pill {{ $model->is_active ? 'active' : 'inactive' }}">
                                <span class="status-dot"></span>
                                {{ $model->is_active ? 'Aktif' : 'Nonaktif' }}
                            </div>
                        </button>
                    </form>
                </td>
                <td>
                    @if($model->is_premium)
                        <span class="badge badge-premium">✦ Premium</span>
                    @else
                        <span class="badge badge-gray">Free</span>
                    @endif
                </td>
                <td class="text-xs text-muted col-hide-mobile">{{ $model->sort_order }}</td>
                <td>
                    <div class="table-action-group flex gap-1 justify-end">
                        <a href="{{ route('admin.product-models.edit', $model) }}" class="btn btn-info btn-xs">Edit</a>
                        @if($model->design_projects_count === 0)
                        <form method="POST" action="{{ route('admin.product-models.destroy', $model) }}"
                              onsubmit="return confirm('Hapus model {{ addslashes($model->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:48px 20px;">
                    <div style="color:var(--text-muted); font-size:13px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 10px; opacity:0.2;"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Belum ada product model.
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
    <div class="text-xs text-muted">
        Menampilkan {{ $models->firstItem() }}–{{ $models->lastItem() }} dari {{ $models->total() }} model
    </div>
    {{ $models->links() }}
</div>

@endsection