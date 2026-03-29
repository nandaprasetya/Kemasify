@extends('admin.layouts.admin')
@section('page-title', 'Manajemen Users')

@section('content')

<div class="flex items-center justify-between mb-4">
    <h2 style="font-size:18px; font-weight:800;">Users <span class="text-muted" style="font-size:14px; font-weight:400;">{{ $users->total() }} total</span></h2>
</div>

{{-- Filters --}}
<form method="GET" class="filters-bar">
    <input type="text" name="search" class="form-input" placeholder="Cari nama / email..." value="{{ request('search') }}" style="min-width:220px;">
    <select name="plan" class="form-select">
        <option value="">Semua Plan</option>
        <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free</option>
        <option value="premium" {{ request('plan') === 'premium' ? 'selected' : '' }}>Premium</option>
    </select>
    <select name="role" class="form-select">
        <option value="">Semua Role</option>
        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
    </select>
    <select name="sort" class="form-select">
        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Terbaru</option>
        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama</option>
        <option value="token_balance" {{ request('sort') === 'token_balance' ? 'selected' : '' }}>Token</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Reset</a>
</form>

<div class="table-wrap">
    <table>
        <thead><tr>
            <th>User</th>
            <th>Plan</th>
            <th>Token</th>
            <th>Proyek</th>
            <th>AI Jobs</th>
            <th>Role</th>
            <th>Bergabung</th>
            <th style="text-align:right">Aksi</th>
        </tr></thead>
        <tbody>
        @forelse($users as $user)
        <tr>
            <td>
                <div style="font-weight:600; font-size:13px;">
                    {{ $user->name }}
                    @if($user->id === auth()->id())
                        <span class="badge badge-blue" style="font-size:10px;">Kamu</span>
                    @endif
                </div>
                <div class="text-sm text-muted">{{ $user->email }}</div>
            </td>
            <td>
                @if($user->isPremium())
                    <span class="badge badge-premium">✦ Premium</span>
                    @if($user->plan_expires_at)
                        <div class="text-sm text-muted" style="margin-top:3px;">s/d {{ $user->plan_expires_at->format('d M Y') }}</div>
                    @endif
                @else
                    <span class="badge badge-gray">Free</span>
                @endif
            </td>
            <td><span style="color:var(--accent); font-weight:700;">{{ $user->token_balance }}</span></td>
            <td>{{ $user->design_projects_count }}</td>
            <td>{{ $user->ai_generation_jobs_count }}</td>
            <td>
                @if($user->is_admin)
                    <span class="badge badge-red">Admin</span>
                @else
                    <span class="badge badge-gray">User</span>
                @endif
            </td>
            <td class="text-sm text-muted">{{ $user->created_at->format('d M Y') }}</td>
            <td>
                <div style="display:flex; gap:4px; justify-content:flex-end;">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-xs">Detail</a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-info btn-xs">Edit</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada user ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $users->links() }}</div>

@endsection