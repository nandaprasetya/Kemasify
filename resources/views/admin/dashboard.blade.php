@extends('admin.layouts.admin')
@section('page-title', 'Dashboard')

@section('content')

<div class="stats-grid">
    <div class="stat-box">
        <div class="stat-box-label">Total Users</div>
        <div class="stat-box-value">{{ number_format($stats['total_users']) }}</div>
        <div class="stat-box-sub">{{ $stats['premium_users'] }} premium · {{ $stats['free_users'] }} free</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-label">Total Proyek</div>
        <div class="stat-box-value">{{ number_format($stats['total_projects']) }}</div>
        <div class="stat-box-sub">desain packaging</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-label">AI Jobs</div>
        <div class="stat-box-value">{{ number_format($stats['total_ai_jobs']) }}</div>
        <div class="stat-box-sub" style="{{ $stats['pending_ai_jobs'] > 0 ? 'color:var(--warning)' : '' }}">
            {{ $stats['pending_ai_jobs'] }} pending
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-label">Render Jobs</div>
        <div class="stat-box-value">{{ number_format($stats['total_renders']) }}</div>
        <div class="stat-box-sub" style="{{ $stats['pending_renders'] > 0 ? 'color:var(--warning)' : '' }}">
            {{ $stats['pending_renders'] }} pending
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-box-label">Token Dikonsumsi</div>
        <div class="stat-box-value" style="font-size:22px;">{{ number_format($stats['total_tokens_consumed']) }}</div>
        <div class="stat-box-sub">total semua user</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-label">Konversi Premium</div>
        <div class="stat-box-value" style="font-size:22px; color:var(--accent);">
            {{ $stats['total_users'] > 0 ? round($stats['premium_users'] / $stats['total_users'] * 100, 1) : 0 }}%
        </div>
        <div class="stat-box-sub">free → premium</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px;">

    {{-- Registrasi 7 hari --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 style="font-size:14px; font-weight:700;">Registrasi 7 Hari Terakhir</h3>
        </div>
        @php $maxVal = max(array_values($chartDates->toArray()) ?: [1]); @endphp
        <div style="display:flex; align-items:flex-end; gap:6px; height:100px;">
            @foreach($chartDates as $date => $count)
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                <div style="font-size:10px; color:var(--text-muted);">{{ $count }}</div>
                <div style="width:100%; background:{{ $count > 0 ? 'var(--accent)' : 'var(--bg3)' }}; border-radius:3px 3px 0 0; height:{{ $maxVal > 0 ? max(4, round($count/$maxVal*80)) : 4 }}px;"></div>
                <div style="font-size:9px; color:var(--text-muted);">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- AI Job Status --}}
    <div class="card">
        <h3 style="font-size:14px; font-weight:700; margin-bottom:16px;">Status AI Jobs</h3>
        @php
        $statusColors = ['completed'=>'var(--accent)','pending'=>'var(--warning)','processing'=>'var(--info)','failed'=>'var(--danger)','cancelled'=>'var(--text-muted)'];
        $total = $aiJobStats->sum();
        @endphp
        @foreach($aiJobStats as $status => $count)
        <div style="margin-bottom:10px;">
            <div class="flex items-center justify-between" style="margin-bottom:4px;">
                <span style="font-size:12px; color:var(--text-muted);">{{ ucfirst($status) }}</span>
                <span style="font-size:12px; font-weight:600;">{{ $count }}</span>
            </div>
            <div style="height:5px; background:var(--bg3); border-radius:99px; overflow:hidden;">
                <div style="height:5px; background:{{ $statusColors[$status] ?? 'var(--text-muted)' }}; border-radius:99px; width:{{ $total > 0 ? round($count/$total*100) : 0 }}%;"></div>
            </div>
        </div>
        @endforeach
        @if($aiJobStats->isEmpty())
        <div class="text-sm text-muted">Belum ada AI jobs.</div>
        @endif
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- Recent Users --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 style="font-size:14px; font-weight:700;">User Terbaru</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Nama</th><th>Plan</th><th>Token</th><th>Bergabung</th>
                </tr></thead>
                <tbody>
                @forelse($recentUsers as $u)
                <tr>
                    <td>
                        <a href="{{ route('admin.users.show', $u) }}" style="color:var(--text); text-decoration:none; font-weight:500;">{{ $u->name }}</a>
                        <div class="text-sm text-muted">{{ $u->email }}</div>
                    </td>
                    <td>
                        @if($u->isPremium())
                            <span class="badge badge-premium">✦ Premium</span>
                        @else
                            <span class="badge badge-gray">Free</span>
                        @endif
                    </td>
                    <td><span style="color:var(--accent); font-weight:600;">{{ $u->token_balance }}</span></td>
                    <td class="text-sm text-muted">{{ $u->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-sm text-muted" style="text-align:center; padding:24px;">Belum ada user.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent AI Jobs --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 style="font-size:14px; font-weight:700;">AI Jobs Terbaru</h3>
            <a href="{{ route('admin.ai-jobs.index') }}" class="btn btn-ghost btn-sm">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>#</th><th>User</th><th>Status</th><th>Waktu</th>
                </tr></thead>
                <tbody>
                @forelse($recentAiJobs as $job)
                @php
                $bc = ['completed'=>'badge-green','pending'=>'badge-yellow','processing'=>'badge-blue','failed'=>'badge-red','cancelled'=>'badge-gray'];
                @endphp
                <tr>
                    <td class="text-sm text-muted">#{{ $job->id }}</td>
                    <td>
                        <div style="font-size:13px; font-weight:500;">{{ $job->user?->name ?? '—' }}</div>
                        <div class="text-sm text-muted truncate" style="max-width:120px;">{{ $job->prompt }}</div>
                    </td>
                    <td><span class="badge {{ $bc[$job->status] ?? 'badge-gray' }}">{{ ucfirst($job->status) }}</span></td>
                    <td class="text-sm text-muted">{{ $job->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-sm text-muted" style="text-align:center; padding:24px;">Belum ada AI jobs.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection