{{-- Shared form partial untuk create & edit product model --}}
{{-- $model = existing model (edit) | undefined (create) --}}

@php $m = $model ?? null; @endphp

@push('styles')
<style>
    .form-section {
        margin-bottom: 28px;
    }
    .form-section-title {
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title svg { opacity: 0.6; }

    .file-drop-area {
        border: 1px dashed var(--border-hover);
        border-radius: var(--radius-sm);
        padding: 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s;
        background: var(--bg3);
        position: relative;
    }
    .file-drop-area:hover {
        border-color: rgba(130,80,255,0.4);
        background: var(--bg4);
    }
    .file-drop-area input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .file-drop-label { font-size: 12px; color: var(--text-muted); line-height: 1.5; pointer-events: none; }
    .file-drop-label strong { color: var(--accent-bright); }

    .current-file-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: var(--bg3);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        margin-bottom: 10px;
    }
    .current-file-preview img {
        width: 52px; height: 52px;
        object-fit: contain;
        border-radius: 6px;
        background: var(--bg4);
        border: 1px solid var(--border);
    }
    .current-file-info { font-size: 12px; color: var(--text-dim); }
    .current-file-info strong { display: block; color: var(--success); margin-bottom: 2px; font-size: 11px; }

    .toggle-group {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .toggle-card {
        flex: 1;
        min-width: 200px;
        padding: 14px 16px;
        background: var(--bg3);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        transition: border-color 0.15s;
    }
    .toggle-card:has(input:checked) {
        border-color: rgba(130,80,255,0.3);
        background: var(--bg4);
    }

    @media (max-width: 580px) {
        .toggle-group { flex-direction: column; gap: 10px; }
        .toggle-card { min-width: unset; }
    }
</style>
@endpush

{{-- ════════ BASIC INFO ════════ --}}
<div class="form-section">
    <div class="form-section-title">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Informasi Dasar
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Nama Model <span class="required">*</span></label>
            <input type="text" name="name" class="form-input"
                   value="{{ old('name', $m?->name) }}"
                   placeholder="mis: Cardboard Box 500ml" required
                   autocomplete="off">
            @error('name')<div class="form-error">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                {{ $message }}
            </div>@enderror
        </div>
        <div class="form-group">
            <label class="form-label">Kategori <span class="required">*</span></label>
            <select name="category" class="form-select" required>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $m?->category) === $cat ? 'selected' : '' }}>
                    {{ ucfirst($cat) }}
                </option>
                @endforeach
            </select>
            @error('category')<div class="form-error">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                {{ $message }}
            </div>@enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-textarea"
                  placeholder="Deskripsi singkat model produk..." rows="3">{{ old('description', $m?->description) }}</textarea>
        @error('description')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Dimensi (P×L×T)</label>
            <input type="text" name="dimensions" class="form-input"
                   value="{{ old('dimensions', $m?->dimensions ? implode('x', [$m->dimensions['width'] ?? 0, $m->dimensions['height'] ?? 0, $m->dimensions['depth'] ?? 0]) : '') }}"
                   placeholder="10x15x5" autocomplete="off">
            <div class="form-hint">Format: lebar × tinggi × kedalaman (cm)</div>
            @error('dimensions')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input"
                   value="{{ old('sort_order', $m?->sort_order ?? 0) }}" min="0">
            <div class="form-hint">Makin kecil = makin atas di daftar</div>
        </div>
    </div>
</div>

{{-- ════════ MEDIA ════════ --}}
<div class="form-section">
    <div class="form-section-title">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        Media & Assets
    </div>

    {{-- Thumbnail --}}
    <div class="form-group">
        <label class="form-label">Thumbnail (Gambar Preview)</label>
        @if($m?->thumbnail_path && $m->thumbnail_path !== 'models/thumbnails/default.png')
        <div class="current-file-preview">
            <img src="{{ asset('storage/'.$m->thumbnail_path) }}"
                 onerror="this.parentElement.style.display='none'">
            <div class="current-file-info">
                <strong>✓ Thumbnail tersimpan</strong>
                Upload file baru untuk mengganti
            </div>
        </div>
        @endif
        <div class="file-drop-area">
            <input type="file" name="thumbnail" accept=".png,.jpg,.jpeg,.webp">
            <div class="file-drop-label">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 6px; opacity:0.3;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <strong>Klik untuk upload</strong> atau drag & drop<br>
                PNG, JPG, WEBP · Maks 2MB
            </div>
        </div>
        @error('thumbnail')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    {{-- 3D Model --}}
    <div class="form-group">
        <label class="form-label">File 3D Model</label>
        @if($m?->model_3d_path)
        <div class="current-file-preview">
            <div style="width:52px; height:52px; background:var(--accent-dim); border:1px solid rgba(130,80,255,0.2); border-radius:6px; display:flex; align-items:center; justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--accent-bright);"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="current-file-info">
                <strong>✓ File 3D tersimpan</strong>
                {{ basename($m->model_3d_path) }}
            </div>
        </div>
        @endif
        <div class="file-drop-area">
            <input type="file" name="model_3d" accept=".glb,.gltf,.obj">
            <div class="file-drop-label">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 6px; opacity:0.3;"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <strong>Upload 3D model</strong><br>
                GLB, GLTF, OBJ · Maks 50MB
            </div>
        </div>
        @error('model_3d')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ════════ VISIBILITY ════════ --}}
<div class="form-section">
    <div class="form-section-title">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Visibilitas & Akses
    </div>

    <div class="toggle-group">
        <label class="toggle-card toggle-wrap">
            <span class="toggle">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $m ? $m->is_active : true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </span>
            <div>
                <div style="font-size:13px; font-weight:600; margin-bottom:2px;">Aktif</div>
                <div class="text-xs text-muted">Tampilkan di halaman pemilihan model</div>
            </div>
        </label>

        <label class="toggle-card toggle-wrap">
            <span class="toggle">
                <input type="checkbox" name="is_premium" value="1"
                       {{ old('is_premium', $m?->is_premium) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </span>
            <div>
                <div style="font-size:13px; font-weight:600; margin-bottom:2px;">
                    Premium Only
                    <span class="badge badge-premium" style="margin-left:6px; font-size:9px;">✦</span>
                </div>
                <div class="text-xs text-muted">Hanya untuk user premium</div>
            </div>
        </label>
    </div>
</div>