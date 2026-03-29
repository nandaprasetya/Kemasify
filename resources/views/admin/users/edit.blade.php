@extends('admin.layouts.admin')
@section('page-title', 'Edit User: ' . $user->name)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <h2 style="font-size:18px; font-weight:800;">Edit User</h2>
</div>

<div style="max-width:680px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Token Balance *</label>
                    <input type="number" name="token_balance" class="form-input" value="{{ old('token_balance', $user->token_balance) }}" min="0" max="99999" required>
                    @error('token_balance')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Plan *</label>
                    <select name="plan" class="form-select">
                        <option value="free" {{ old('plan', $user->plan) === 'free' ? 'selected' : '' }}>Free</option>
                        <option value="premium" {{ old('plan', $user->plan) === 'premium' ? 'selected' : '' }}>Premium</option>
                    </select>
                    @error('plan')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Plan Expires At</label>
                <input type="date" name="plan_expires_at" class="form-input"
                    value="{{ old('plan_expires_at', $user->plan_expires_at?->format('Y-m-d')) }}">
                <div class="form-hint">Kosongkan untuk premium selamanya</div>
                @error('plan_expires_at')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-input" placeholder="Kosongkan jika tidak ingin ganti">
                <div class="form-hint">Min. 8 karakter dengan huruf dan angka</div>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            @if($user->id !== auth()->id())
            <div class="form-group">
                <label class="toggle-wrap">
                    <span class="toggle">
                        <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </span>
                    <span style="font-size:13px;">Jadikan Admin</span>
                </label>
            </div>
            @endif

            <hr class="divider">
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection