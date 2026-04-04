@extends('admin.layouts.admin')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ════════════════════════════════════════
       STATS GRID OVERRIDE (lebih lebar di desktop)
       Desktop ≥1100px : 3 kolom
       860–1099px      : 3 kolom
       600–859px       : 2 kolom
       <600px          : 2 kolom kecil
       <400px          : 1 kolom
    ════════════════════════════════════════ */
    .stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 14px !important;
        margin-bottom: 20px !important;
    }
    .stat-box {
        padding: 20px !important;
    }
    .stat-box-value {
        font-size: 24px !important;
    }

    /* ════════════════════════════════════════
       CHART ROW (registrasi + ai jobs)
       Desktop : 2 kolom
       ≤820px  : 1 kolom
    ════════════════════════════════════════ */
    .dashboard-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }

    /* ════════════════════════════════════════
       BOTTOM ROW (users + ai jobs terbaru)
       Desktop : 2 kolom
       ≤820px  : 1 kolom
    ════════════════════════════════════════ */
    .dashboard-grid-2-bottom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* ── Bar chart ── */
    .bar-chart { display: flex; align-items: flex-end; gap: 6px; height: 90px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .bar-val { font-size: 9px; color: var(--text-muted); font-weight: 600; }
    .bar-fill {
        width: 100%;
        border-radius: 4px 4px 0 0;
        min-height: 4px;
        transition: height 0.4s ease;
    }
    .bar-fill.has-data {
        background: linear-gradient(to top, var(--accent), var(--accent-bright));
        box-shadow: 0 0 8px var(--accent-glow);
    }
    .bar-fill.no-data { background: var(--bg4); }
    .bar-label { font-size: 9px; color: var(--text-muted); }

    /* ── Progress bar status ── */
    .status-row { margin-bottom: 10px; }
    .status-row:last-child { margin-bottom: 0; }
    .status-bar-track {
        height: 4px;
        background: var(--bg4);
        border-radius: 99px;
        overflow: hidden;
        margin-top: 5px;
    }
    .status-bar-fill {
        height: 4px;
        border-radius: 99px;
        transition: width 0.5s ease;
    }

    /* ── Card accent borders ── */
    .card-accent-red  { border-top: 2px solid var(--danger); }
    .card-accent-blue { border-top: 2px solid var(--info); }

    .stat-revenue { border-color: rgba(130,80,255,0.25); }
    .stat-revenue::after {
        background: radial-gradient(ellipse at top right, rgba(130,80,255,0.18), transparent 70%) !important;
    }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 32px 20px;
        color: var(--text-muted);
        font-size: 12px;
    }
    .empty-state svg { opacity: 0.2; margin-bottom: 10px; }

    /* ════════════════════════════════════════
       RESPONSIVE
    ════════════════════════════════════════ */

    /* Tablet portrait */
    @media (max-width: 860px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .dashboard-grid-2,
        .dashboard-grid-2-bottom { grid-template-columns: 1fr; }
    }

    /* Mobile */
    @media (max-width: 600px) {
        .stats-grid { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
        .stat-box-value { font-size: 20px !important; }
        .stat-box { padding: 14px 16px !important; }
    }

    /* Small mobile */
    @media (max-width: 400px) {
        .stats-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')

{{-- ════════════════ STATS ════════════════ --}}
<div class="stats-grid">

    <div class="stat-box">
        <div class="stat-box-label">Total Users</div>
        <div class="stat-box-value">{{ number_format($stats['total_users']) }}</div>
        <div class="stat-box-sub">
            <span style="color:var(--accent-bright)">{{ $stats['premium_users'] }} premium</span>
            · {{ $stats['free_users'] }} free
        </div>
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
        <div class="stat-box-value" style="font-size:18px; letter-spacing:-0.03em;">
            {{ number_format($stats['total_tokens_consumed']) }}
        </div>
        <div class="stat-box-sub">total semua user</div>
    </div>

    <div class="stat-box stat-revenue">
        <div class="stat-box-label">Total Revenue</div>
        <div class="stat-box-value" style="font-size:18px; color:var(--accent-bright); letter-spacing:-0.03em;">
            Rp{{ number_format($stats['total_revenue'], 0, ',', '.') }}
        </div>
        <div class="stat-box-sub" style="{{ $stats['pending_orders'] > 0 ? 'color:var(--warning)' : '' }}">
            {{ $stats['pending_orders'] }} pending
            · <span style="color:var(--success)">{{ $stats['paid_orders_today'] }} lunas hari ini</span>
        </div>
    </div>

</div>

{{-- ════════════════ CHARTS ROW ════════════════ --}}
<div class="dashboard-grid-2">

    {{-- Registration chart --}}
    <div class="card card-accent-blue">
        <div class="flex items-center justify-between mb-4" style="gap:8px;">
            <h3 style="font-size:13px; font-weight:700;">Registrasi 7 Hari</h3>
            <span class="badge badge-blue">Terakhir</span>
        </div>
        @php $maxVal = max(array_values($chartDates->toArray()) ?: [1]); @endphp
        <div class="bar-chart">
            @foreach($chartDates as $date => $count)
            <div class="bar-col">
                <div class="bar-val">{{ $count > 0 ? $count : '' }}</div>
                <div class="bar-fill {{ $count > 0 ? 'has-data' : 'no-data' }}"
                     style="height: {{ $maxVal > 0 ? max(4, round($count / $maxVal * 70)) : 4 }}px;">
                </div>
                <div class="bar-label">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- AI Job status --}}
    <div class="card card-accent-red">
        <div class="flex items-center justify-between mb-4" style="gap:8px;">
            <h3 style="font-size:13px; font-weight:700;">Status AI Jobs</h3>
            <div class="ai-chip"><span class="ai-chip-dot"></span> Live</div>
        </div>
        @php
            $statusColors = [
                'completed'  => 'var(--success)',
                'pending'    => 'var(--warning)',
                'processing' => 'var(--info)',
                'failed'     => 'var(--danger)',
                'cancelled'  => 'var(--text-muted)',
            ];
            $total = $aiJobStats->sum();
        @endphp
        @forelse($aiJobStats as $status => $count)
        <div class="status-row">
            <div class="flex items-center justify-between">
                <span style="font-size:12px; color:var(--text-dim); display:flex; align-items:center; gap:6px;">
                    <span style="width:6px; height:6px; border-radius:50%; background:{{ $statusColors[$status] ?? 'var(--text-muted)' }}; display:inline-block; flex-shrink:0;"></span>
                    {{ ucfirst($status) }}
                </span>
                <span style="font-size:12px; font-weight:600; font-family:'Syne',sans-serif;">{{ $count }}</span>
            </div>
            <div class="status-bar-track">
                <div class="status-bar-fill"
                     style="background:{{ $statusColors[$status] ?? 'var(--text-muted)' }};
                            width:{{ $total > 0 ? round($count/$total*100) : 0 }}%;
                            box-shadow: 0 0 6px {{ $statusColors[$status] ?? 'transparent' }};">
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">Belum ada AI jobs.</div>
        @endforelse
    </div>

</div>

{{-- ════════════════ TABLES ROW ════════════════ --}}
<div class="dashboard-grid-2-bottom">

    {{-- Recent Users --}}
    <div>
        <div class="section-header">
            <h3 class="section-title" style="font-size:14px;">User Terbaru</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">
                Lihat semua
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="table-wrap">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Plan</th>
                            <th>Token</th>
                            <th class="col-hide-mobile">Bergabung</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($recentUsers as $u)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $u) }}"
                               style="color:var(--text); text-decoration:none; font-weight:600; font-size:13px;">
                                {{ $u->name }}
                            </a>
                            <div class="text-xs text-muted truncate" style="max-width:160px;">{{ $u->email }}</div>
                        </td>
                        <td>
                            @if($u->isPremium())
                                <span class="badge badge-premium">✦ Premium</span>
                            @else
                                <span class="badge badge-gray">Free</span>
                            @endif
                        </td>
                        <td>
                            <span style="color:var(--accent-bright); font-weight:700; font-family:'Syne',sans-serif;">
                                {{ number_format($u->token_balance) }}
                            </span>
                        </td>
                        <td class="text-xs text-muted col-hide-mobile">{{ $u->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-state">Belum ada user.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent AI Jobs --}}
    <div>
        <div class="section-header">
            <h3 class="section-title" style="font-size:14px;">
                AI Jobs Terbaru
                <div class="ai-chip" style="margin-left:8px; vertical-align:middle; display:inline-flex;">
                    <span class="ai-chip-dot"></span>
                </div>
            </h3>
            <a href="{{ route('admin.ai-jobs.index') }}" class="btn btn-ghost btn-sm">
                Lihat semua
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="table-wrap">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Status</th>
                            <th class="col-hide-mobile">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($recentAiJobs as $job)
                    @php
                        $bc = [
                            'completed'  => 'badge-green',
                            'pending'    => 'badge-yellow',
                            'processing' => 'badge-blue',
                            'failed'     => 'badge-red',
                            'cancelled'  => 'badge-gray',
                        ];
                    @endphp
                    <tr>
                        <td class="text-xs text-muted" style="font-family:'Syne',sans-serif;">#{{ $job->id }}</td>
                        <td>
                            <div style="font-size:13px; font-weight:600;">{{ $job->user?->name ?? '—' }}</div>
                            <div class="text-xs text-muted truncate" style="max-width:130px;">{{ $job->prompt }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $bc[$job->status] ?? 'badge-gray' }}">
                                {{ ucfirst($job->status) }}
                            </span>
                        </td>
                        <td class="text-xs text-muted col-hide-mobile">{{ $job->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-state">Belum ada AI jobs.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection