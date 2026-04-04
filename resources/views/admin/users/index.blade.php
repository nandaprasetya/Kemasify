@extends('admin.layouts.admin')
@section('page-title', 'Manajemen Users')

@push('styles')
<style>
    .user-avatar-sm {
        width: 30px; height: 30px;
        border-radius: 7px;
        background: var(--bg4);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 11px;
        color: var(--text-dim);
        flex-shrink: 0;
        border: 1px solid var(--border);
    }
    .table-action-group { display: flex; gap: 4px; justify-content: flex-end; flex-wrap: wrap; }

    @media (max-width: 580px) {
        .filter-row-buttons { width: 100%; display: flex; gap: 6px; }
        .filter-row-buttons .btn { flex: 1; justify-content: center; }
    }
</style>
@endpush

@section('content')

<div class="section-header">
    <h2 class="section-title">
        Users
        <span class="count">{{ number_format($users->total()) }} total</span>
    </h2>
</div>

{{-- Filters --}}
<form method="GET" class="filters-bar">
    <input type="text" name="search" class="form-input"
           placeholder="Cari nama / email..."
           value="{{ request('search') }}"
           style="flex:1; min-width:180px;">

    <select name="plan" class="form-select" style="min-width:120px;">
        <option value="">Semua Plan</option>
        <option value="free"    {{ request('plan') === 'free'    ? 'selected' : '' }}>Free</option>
        <option value="premium" {{ request('plan') === 'premium' ? 'selected' : '' }}>Premium</option>
    </select>

    <select name="role" class="form-select" style="min-width:120px;">
        <option value="">Semua Role</option>
        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="user"  {{ request('role') === 'user'  ? 'selected' : '' }}>User</option>
    </select>

    <select name="sort" class="form-select" style="min-width:130px;">
        <option value="created_at"    {{ request('sort','created_at') === 'created_at'    ? 'selected' : '' }}>Terbaru</option>
        <option value="name"          {{ request('sort') === 'name'          ? 'selected' : '' }}>Nama A–Z</option>
        <option value="token_balance" {{ request('sort') === 'token_balance' ? 'selected' : '' }}>Token ↓</option>
    </select>

    <div class="filter-row-buttons flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead><tr>
                <th>User</th>
                <th>Plan</th>
                <th>Token</th>
                <th class="col-hide-mobile">Proyek</th>
                <th class="col-hide-mobile">AI Jobs</th>
                <th>Role</th>
                <th class="col-hide-mobile">Bergabung</th>
                <th style="text-align:right">Aksi</th>
            </tr></thead>
            <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="user-avatar-sm">{{ strtoupper(substr($user->name,0,1)) }}</div>
                        <div>
                            <div style="font-weight:600; font-size:13px; display:flex; align-items:center; gap:6px;">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="badge badge-blue" style="font-size:9px;">Kamu</span>
                                @endif
                            </div>
                            <div class="text-xs text-muted truncate" style="max-width:180px;">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($user->isPremium())
                        <span class="badge badge-premium">✦ Premium</span>
                        @if($user->plan_expires_at)
                            <div class="text-xs text-muted mt-1">
                                s/d {{ $user->plan_expires_at->format('d M Y') }}
                            </div>
                        @endif
                    @else
                        <span class="badge badge-gray">Free</span>
                    @endif
                </td>
                <td>
                    <span style="color:var(--accent-bright); font-weight:700; font-family:'Syne',sans-serif;">
                        {{ number_format($user->token_balance) }}
                    </span>
                </td>
                <td class="text-base text-dim col-hide-mobile">{{ $user->design_projects_count }}</td>
                <td class="text-base text-dim col-hide-mobile">{{ $user->ai_generation_jobs_count }}</td>
                <td>
                    @if($user->is_admin)
                        <span class="badge badge-red">Admin</span>
                    @else
                        <span class="badge badge-gray">User</span>
                    @endif
                </td>
                <td class="text-xs text-muted col-hide-mobile">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div class="table-action-group">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-xs">Detail</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-info btn-xs">Edit</a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.')">
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
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 10px; opacity:0.2;">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        Tidak ada user ditemukan.
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
        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
    </div>
    {{ $users->links() }}
</div>

@endsection