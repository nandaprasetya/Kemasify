@extends('admin.layouts.admin')
@section('page-title', 'Orders & Payments')

@push('styles')
<style>
    /* ════════════════════════════════════════
       REVENUE STATS GRID
       ≥1024px  : 4 kolom
       768–1023 : 2 kolom
       <768px   : 1 kolom
    ════════════════════════════════════════ */
    .rev-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 14px !important;
        margin-bottom: 20px !important;
        width: 100% !important;
    }

    .rev-card {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: border-color 0.15s, transform 0.15s;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .rev-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-2px);
    }

    /* Top accent line */
    .rev-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
    }
    .rev-card.green::before  { background: var(--accent); }
    .rev-card.yellow::before { background: var(--warning); }
    .rev-card.blue::before   { background: var(--info); }
    .rev-card.red::before    { background: var(--danger); }

    /* Corner glow */
    .rev-card::after {
        content: '';
        position: absolute;
        bottom: -20px; right: -20px;
        width: 80px; height: 80px;
        border-radius: 50%;
        opacity: 0.07;
        pointer-events: none;
    }
    .rev-card.green::after  { background: var(--accent); }
    .rev-card.yellow::after { background: var(--warning); }
    .rev-card.blue::after   { background: var(--info); }
    .rev-card.red::after    { background: var(--danger); }

    .rev-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
        flex-shrink: 0;
    }
    .rev-icon.green  { background: var(--accent-dim); }
    .rev-icon.yellow { background: var(--warning-dim); }
    .rev-icon.blue   { background: var(--info-dim); }
    .rev-icon.red    { background: var(--danger-dim); }

    .rev-label {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .rev-value {
        font-family: 'Syne', sans-serif;
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.02em;
    }
    .rev-sub {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    /* ════════════════════════════════════════
       SECONDARY ROW — split cards + chart
       ≥768px  : 2 kolom berdampingan
       <768px  : 1 kolom
    ════════════════════════════════════════ */
    .rev-secondary {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 14px !important;
        margin-bottom: 20px !important;
        width: 100% !important;
    }

    .rev-split-cards {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .rev-split-card {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: border-color 0.15s, transform 0.15s;
        flex: 1;
        min-width: 0;
    }
    .rev-split-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-1px);
    }

    .rev-split-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .rev-split-label {
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 3px;
        letter-spacing: 0.03em;
    }
    .rev-split-value {
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .rev-split-count {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* ════════════════════════════════════════
       CHART CARD
    ════════════════════════════════════════ */
    .chart-card {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        border-top: 2px solid var(--accent-bright);
        min-width: 0;
    }

    .chart-label {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chart-bars {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        height: 80px;
    }

    .chart-bar-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        min-width: 0;
    }

    .chart-bar-val {
        font-size: 8px;
        color: var(--text-muted);
        white-space: nowrap;
        font-weight: 600;
    }

    .chart-bar {
        width: 100%;
        background: linear-gradient(to top, var(--accent), var(--accent-bright));
        border-radius: 4px 4px 0 0;
        min-height: 3px;
        box-shadow: 0 0 8px var(--accent-glow);
        transition: height 0.3s ease;
    }
    .chart-bar.zero {
        background: var(--bg4);
        box-shadow: none;
    }

    .chart-bar-date {
        font-size: 8px;
        color: var(--text-muted);
    }

    /* ════════════════════════════════════════
       STATUS TABS
    ════════════════════════════════════════ */
    .status-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .status-tab {
        padding: 6px 13px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        background: transparent;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        color: var(--text-muted);
        transition: all 0.12s;
        display: flex;
        align-items: center;
        gap: 5px;
        font-family: 'DM Sans', sans-serif;
        letter-spacing: -0.01em;
    }
    .status-tab:hover { border-color: var(--border-hover); color: var(--text); background: var(--bg3); }
    .status-tab.t-all     { border-color: var(--border-hover); background: var(--bg3); color: var(--text); }
    .status-tab.t-paid    { border-color: rgba(34,212,160,0.35); background: rgba(34,212,160,0.07); color: var(--success); }
    .status-tab.t-pending { border-color: rgba(255,170,0,0.35);  background: rgba(255,170,0,0.07);  color: var(--warning); }
    .status-tab.t-failed  { border-color: rgba(255,64,85,0.35);  background: rgba(255,64,85,0.07);  color: var(--danger); }

    .tab-count {
        background: rgba(255,255,255,0.06);
        padding: 0 5px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
    }

    /* ════════════════════════════════════════
       TYPE BADGES
    ════════════════════════════════════════ */
    .type-premium {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 99px;
        background: linear-gradient(135deg, rgba(248,184,3,0.12), rgba(255,107,53,0.12));
        border: 1px solid rgba(248,184,3,0.22);
        color: #f8b803; font-size: 10px; font-weight: 700;
        white-space: nowrap;
    }
    .type-token {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 99px;
        background: var(--accent-dim);
        border: 1px solid rgba(130,80,255,0.22);
        color: var(--accent-bright); font-size: 10px; font-weight: 700;
        white-space: nowrap;
    }

    /* ════════════════════════════════════════
       PAGINATION
    ════════════════════════════════════════ */
    .pagination-row {
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* ════════════════════════════════════════
       RESPONSIVE
       Tablet  ≤1024px : rev-grid 2 kolom
       Mobile  <768px  : semua 1 kolom
    ════════════════════════════════════════ */
    @media (max-width: 1024px) {
        .rev-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 767px) {
        .rev-grid {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        .rev-secondary {
            grid-template-columns: 1fr !important;
        }
        .rev-value { font-size: 20px; }
        .rev-card { padding: 16px; }
        .chart-card { padding: 16px; }
    }
</style>
@endpush

@section('content')

{{-- ════════════════ REVENUE STATS ════════════════ --}}
<div class="rev-grid">

    <div class="rev-card green">
        <div class="rev-icon green">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="rev-label">Total Revenue</div>
        <div class="rev-value" style="color:var(--accent-bright);">
            Rp{{ number_format($stats['total_revenue'], 0, ',', '.') }}
        </div>
        <div class="rev-sub" style="color:var(--success);">{{ $stats['paid_orders'] }} transaksi lunas</div>
    </div>

    <div class="rev-card yellow">
        <div class="rev-icon yellow">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <path d="M1 10h22"/>
            </svg>
        </div>
        <div class="rev-label">Total Orders</div>
        <div class="rev-value">{{ number_format($stats['total_orders']) }}</div>
        <div class="rev-sub" style="{{ $stats['pending_orders'] > 0 ? 'color:var(--warning)' : '' }}">
            {{ $stats['pending_orders'] }} menunggu bayar
        </div>
    </div>

    <div class="rev-card blue">
        <div class="rev-icon blue">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
        </div>
        <div class="rev-label">Premium Sales</div>
        <div class="rev-value">{{ number_format($stats['premium_sales']) }}</div>
        <div class="rev-sub">Rp{{ number_format($stats['premium_revenue'], 0, ',', '.') }}</div>
    </div>

    <div class="rev-card red">
        <div class="rev-icon red">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </div>
        <div class="rev-label">Token Sales</div>
        <div class="rev-value">{{ number_format($stats['token_sales']) }}</div>
        <div class="rev-sub">Rp{{ number_format($stats['token_revenue'], 0, ',', '.') }}</div>
    </div>

</div>

{{-- ════════════════ SECONDARY ROW ════════════════ --}}
<div class="rev-secondary">

    {{-- Revenue split cards --}}
    <div class="rev-split-cards">
        <div class="rev-split-card">
            <div class="rev-split-icon" style="background:linear-gradient(135deg,rgba(248,184,3,0.12),rgba(255,107,53,0.12));">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f8b803" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </div>
            <div>
                <div class="rev-split-label">Revenue Premium</div>
                <div class="rev-split-value" style="color:#f8b803;">
                    Rp{{ number_format($stats['premium_revenue'], 0, ',', '.') }}
                </div>
                <div class="rev-split-count">
                    {{ $stats['premium_sales'] }} order
                    · avg Rp{{ $stats['premium_sales'] > 0 ? number_format($stats['premium_revenue'] / $stats['premium_sales'], 0, ',', '.') : 0 }}
                </div>
            </div>
        </div>

        <div class="rev-split-card">
            <div class="rev-split-icon" style="background:var(--accent-dim);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-bright)" stroke-width="2">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
            </div>
            <div>
                <div class="rev-split-label">Revenue Token</div>
                <div class="rev-split-value" style="color:var(--accent-bright);">
                    Rp{{ number_format($stats['token_revenue'], 0, ',', '.') }}
                </div>
                <div class="rev-split-count">
                    {{ $stats['token_sales'] }} order
                    · avg Rp{{ $stats['token_sales'] > 0 ? number_format($stats['token_revenue'] / $stats['token_sales'], 0, ',', '.') : 0 }}
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue chart --}}
    <div class="chart-card">
        <div class="chart-label">
            Revenue 7 Hari Terakhir
            <div class="ai-chip"><span class="ai-chip-dot"></span> Live</div>
        </div>
        @php $maxRev = max(array_values($chartDates->toArray()) ?: [1]); @endphp
        <div class="chart-bars">
            @foreach($chartDates as $date => $rev)
            <div class="chart-bar-wrap">
                <div class="chart-bar-val">
                    @if($rev >= 1000000)
                        {{ round($rev/1000000, 1) }}jt
                    @elseif($rev >= 1000)
                        {{ round($rev/1000) }}k
                    @elseif($rev > 0)
                        {{ $rev }}
                    @else
                        —
                    @endif
                </div>
                <div class="chart-bar {{ $rev == 0 ? 'zero' : '' }}"
                     style="height:{{ $maxRev > 0 ? max(3, round($rev / $maxRev * 56)) : 3 }}px;">
                </div>
                <div class="chart-bar-date">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ════════════════ TABLE SECTION ════════════════ --}}
<div class="section-header">
    <h3 class="section-title" style="font-size:15px;">
        Daftar Order
        <span class="count">{{ $orders->total() }} total</span>
    </h3>
</div>

{{-- Status tabs --}}
@php
    $currentStatus = request('status', '');
    $tabs = [
        ['label' => 'Semua',      'status' => '',        'active' => 't-all'],
        ['label' => 'Lunas',      'status' => 'paid',    'active' => 't-paid'],
        ['label' => 'Pending',    'status' => 'pending', 'active' => 't-pending'],
        ['label' => 'Gagal',      'status' => 'failed',  'active' => 't-failed'],
        ['label' => 'Kadaluarsa', 'status' => 'expired', 'active' => 't-failed'],
    ];
@endphp
<div class="status-tabs">
    @foreach($tabs as $tab)
    <a href="{{ route('admin.orders.index', array_merge(request()->except(['status','page']), ['status' => $tab['status']])) }}"
       class="status-tab {{ $currentStatus === $tab['status'] ? $tab['active'] : '' }}">
        {{ $tab['label'] }}
        <span class="tab-count">
            {{ \App\Models\Order::when($tab['status'], fn($q) => $q->where('status', $tab['status']))->count() }}
        </span>
    </a>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="filters-bar">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    <input type="text" name="search" class="form-input"
           placeholder="Cari order ID / nama / email..."
           value="{{ request('search') }}"
           style="flex:1; min-width:180px;">
    <select name="type" class="form-select" style="min-width:140px;">
        <option value="">Semua Tipe</option>
        <option value="premium_monthly" {{ request('type') === 'premium_monthly' ? 'selected' : '' }}>✦ Premium</option>
        <option value="token_purchase"  {{ request('type') === 'token_purchase'  ? 'selected' : '' }}>⚡ Token</option>
    </select>
    <input type="date" name="date_from" class="form-input" style="min-width:130px;"
           value="{{ request('date_from') }}">
    <input type="date" name="date_to" class="form-input" style="min-width:130px;"
           value="{{ request('date_to') }}">
    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

{{-- Table --}}
<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Tipe</th>
                    <th class="col-hide-mobile">Detail</th>
                    <th style="text-align:right">Total</th>
                    <th class="col-hide-mobile">Metode</th>
                    <th>Status</th>
                    <th class="col-hide-mobile">Waktu</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
            @php
                $sbg = [
                    'paid'      => 'badge-green',
                    'pending'   => 'badge-yellow',
                    'failed'    => 'badge-red',
                    'expired'   => 'badge-red',
                    'cancelled' => 'badge-gray',
                ];
            @endphp
            <tr>
                <td>
                    <a href="{{ route('admin.orders.show', $order) }}"
                       style="font-family:monospace; font-size:10px; color:var(--text-muted); text-decoration:none; letter-spacing:0.02em;">
                        {{ $order->order_id }}
                    </a>
                </td>
                <td>
                    @if($order->user)
                        <a href="{{ route('admin.users.show', $order->user) }}"
                           style="color:var(--text); text-decoration:none; font-weight:600; font-size:13px;">
                            {{ $order->user->name }}
                        </a>
                        <div class="text-xs text-muted truncate" style="max-width:160px;">{{ $order->user->email }}</div>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($order->isPremiumOrder())
                        <span class="type-premium">✦ Premium</span>
                    @else
                        <span class="type-token">⚡ Token</span>
                    @endif
                </td>
                <td class="text-xs text-muted col-hide-mobile">
                    @if($order->isPremiumOrder())
                        {{ $order->quantity }} bulan
                    @else
                        {{ number_format($order->token_amount) }} token
                    @endif
                </td>
                <td style="text-align:right;">
                    <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:13px; letter-spacing:-0.01em;">
                        Rp{{ number_format($order->amount, 0, ',', '.') }}
                    </span>
                </td>
                <td class="text-xs text-muted col-hide-mobile">
                    {{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}
                </td>
                <td>
                    <span class="badge {{ $sbg[$order->status] ?? 'badge-gray' }}">
                        {{ $order->getStatusLabel() }}
                    </span>
                </td>
                <td class="col-hide-mobile">
                    <div class="text-xs text-muted">{{ $order->created_at->format('d M Y') }}</div>
                    <div class="text-xs text-muted">{{ $order->created_at->format('H:i') }}</div>
                    @if($order->paid_at)
                        <div style="color:var(--success); font-size:10px; margin-top:2px;">
                            ✓ {{ $order->paid_at->format('H:i') }}
                        </div>
                    @endif
                </td>
                <td>
                    <div class="flex gap-1 justify-end">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-xs">Detail</a>
                        @if($order->isPending())
                            <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}"
                                  onsubmit="return confirm('Tandai order ini sebagai lunas?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-xs">✓ Lunas</button>
                            </form>
                            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
                                  onsubmit="return confirm('Batalkan order ini?')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-xs">✕</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:48px 20px;">
                    <div style="color:var(--text-muted); font-size:13px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 10px; opacity:0.2;">
                            <rect x="1" y="4" width="22" height="16" rx="2"/>
                            <path d="M1 10h22"/>
                        </svg>
                        Tidak ada order ditemukan.
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-row">
    <div class="text-xs text-muted">
        Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} order
    </div>
    {{ $orders->links() }}
</div>

@endsection