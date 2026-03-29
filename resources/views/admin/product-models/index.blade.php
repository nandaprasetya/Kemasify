@extends('admin.layouts.admin')
@section('page-title', 'Product Models')

@section('content')

<div class="flex items-center justify-between mb-4">
    <h2 style="font-size:18px; font-weight:800;">Product Models <span class="text-muted" style="font-size:14px; font-weight:400;">{{ $models->total() }} total</span></h2>
    <a href="{{ route('admin.product-models.create') }}" class="btn btn-primary">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Model
    </a>
</div>

<form method="GET" class="filters-bar">
    <input type="text" name="search" class="form-input" placeholder="Cari nama model..." value="{{ request('search') }}">
    <select name="category" class="form-select">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select">
        <option value="">Semua Status</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('admin.product-models.index') }}" class="btn btn-ghost">Reset</a>
</form>

<div class="table-wrap">
    <table>
        <thead><tr>
            <th>Model</th><th>Kategori</th><th>Dimensi</th><th>Proyek</th><th>Status</th><th>Type</th><th>Sort</th><th style="text-align:right">Aksi</th>
        </tr></thead>
        <tbody>
        @forelse($models as $model)
        <tr>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    @if($model->thumbnail_path)
                    <img src="{{ asset('storage/'.$model->thumbnail_path) }}"
                        style="width:36px; height:36px; object-fit:contain; background:var(--bg3); border-radius:6px; border:1px solid var(--border);"
                        onerror="this.style.display='none'">
                    @endif
                    <div>
                        <div style="font-weight:600;">{{ $model->name }}</div>
                        <div class="text-sm text-muted">{{ $model->slug }}</div>
                    </div>
                </div>
            </td>
            <td><span class="badge badge-gray">{{ ucfirst($model->category) }}</span></td>
            <td class="text-sm text-muted">
                @if($model->dimensions)
                    {{ $model->dimensions['width'] ?? '?' }}×{{ $model->dimensions['height'] ?? '?' }}×{{ $model->dimensions['depth'] ?? '?' }} cm
                @else — @endif
            </td>
            <td>{{ $model->design_projects_count }}</td>
            <td>
                <form method="POST" action="{{ route('admin.product-models.toggle', $model) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="badge {{ $model->is_active ? 'badge-green' : 'badge-red' }}" style="border:none; cursor:pointer; background:{{ $model->is_active ? 'rgba(200,245,66,0.12)' : 'rgba(255,71,87,0.12)' }}">
                        {{ $model->is_active ? '● Aktif' : '○ Nonaktif' }}
                    </button>
                </form>
            </td>
            <td>{{ $model->is_premium ? '<span class="badge badge-premium">Premium</span>' : '<span class="badge badge-gray">Free</span>' }}</td>
            <td class="text-sm text-muted">{{ $model->sort_order }}</td>
            <td>
                <div style="display:flex; gap:4px; justify-content:flex-end;">
                    <a href="{{ route('admin.product-models.edit', $model) }}" class="btn btn-info btn-xs">Edit</a>
                    @if($model->design_projects_count === 0)
                    <form method="POST" action="{{ route('admin.product-models.destroy', $model) }}"
                        onsubmit="return confirm('Hapus model {{ $model->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada product model.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $models->links() }}</div>

@endsection