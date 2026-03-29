{{-- Shared form partial untuk create & edit product model --}}
{{-- $model = existing model (edit) | undefined (create) --}}

@php $m = $model ?? null; @endphp

<div class="form-grid-2">
    <div class="form-group">
        <label class="form-label">Nama Model *</label>
        <input type="text" name="name" class="form-input"
            value="{{ old('name', $m?->name) }}"
            placeholder="mis: Cardboard Box 500ml" required>
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Kategori *</label>
        <select name="category" class="form-select" required>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ old('category', $m?->category) === $cat ? 'selected' : '' }}>
                {{ ucfirst($cat) }}
            </option>
            @endforeach
        </select>
        @error('category')<div class="form-error">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-group">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-textarea" placeholder="Deskripsi singkat model produk..." rows="3">{{ old('description', $m?->description) }}</textarea>
    @error('description')<div class="form-error">{{ $message }}</div>@enderror
</div>

<div class="form-grid-2">
    <div class="form-group">
        <label class="form-label">Dimensi (P×L×T cm)</label>
        <input type="text" name="dimensions" class="form-input"
            value="{{ old('dimensions', $m?->dimensions ? implode('x', [$m->dimensions['width'] ?? 0, $m->dimensions['height'] ?? 0, $m->dimensions['depth'] ?? 0]) : '') }}"
            placeholder="mis: 10x15x5">
        <div class="form-hint">Format: lebar x tinggi x kedalaman</div>
        @error('dimensions')<div class="form-error">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-input"
            value="{{ old('sort_order', $m?->sort_order ?? 0) }}" min="0">
        <div class="form-hint">Urutan tampil (makin kecil makin atas)</div>
    </div>
</div>

{{-- Thumbnail --}}
<div class="form-group">
    <label class="form-label">Thumbnail (Gambar Preview)</label>
    @if($m?->thumbnail_path && $m->thumbnail_path !== 'models/thumbnails/default.png')
    <div style="margin-bottom:10px; display:flex; align-items:center; gap:12px;">
        <img src="{{ asset('storage/'.$m->thumbnail_path) }}"
            style="width:64px; height:64px; object-fit:contain; background:var(--bg3); border-radius:8px; border:1px solid var(--border);"
            onerror="this.style.display='none'">
        <span class="text-sm text-muted">Thumbnail saat ini</span>
    </div>
    @endif
    <input type="file" name="thumbnail" class="form-input" accept=".png,.jpg,.jpeg,.webp"
        style="padding:8px; cursor:pointer;">
    <div class="form-hint">PNG, JPG, WEBP · Max 2MB. Kosongkan untuk tidak mengubah.</div>
    @error('thumbnail')<div class="form-error">{{ $message }}</div>@enderror
</div>

{{-- 3D Model --}}
<div class="form-group">
    <label class="form-label">File 3D Model</label>
    @if($m?->model_3d_path)
    <div style="margin-bottom:8px; padding:8px 12px; background:var(--bg3); border-radius:var(--radius-sm); font-size:12px; color:var(--accent);">
        ✓ File 3D tersedia: <span class="text-muted">{{ basename($m->model_3d_path) }}</span>
    </div>
    @endif
    <input type="file" name="model_3d" class="form-input" accept=".glb,.gltf,.obj"
        style="padding:8px; cursor:pointer;">
    <div class="form-hint">GLB, GLTF, OBJ · Max 50MB. Untuk render 3D packaging.</div>
    @error('model_3d')<div class="form-error">{{ $message }}</div>@enderror
</div>

{{-- Toggles --}}
<div style="display:flex; gap:32px; margin-top:4px;">
    <div class="form-group" style="margin-bottom:0;">
        <label class="toggle-wrap">
            <span class="toggle">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $m ? $m->is_active : true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </span>
            <div>
                <div style="font-size:13px; font-weight:500;">Aktif</div>
                <div class="text-sm text-muted">Tampilkan di halaman pemilihan model</div>
            </div>
        </label>
    </div>
    <div class="form-group" style="margin-bottom:0;">
        <label class="toggle-wrap">
            <span class="toggle">
                <input type="checkbox" name="is_premium" value="1"
                    {{ old('is_premium', $m?->is_premium) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </span>
            <div>
                <div style="font-size:13px; font-weight:500;">Premium Only</div>
                <div class="text-sm text-muted">Hanya untuk user premium</div>
            </div>
        </label>
    </div>
</div>