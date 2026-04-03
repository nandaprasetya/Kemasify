@extends('admin.layouts.admin')
@section('page-title', 'Orders & Payments')

@push('styles')
<style>
.rev-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.rev-card {
    background: var(--bg2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
}
.rev-card::after {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 60px; height: 60px;
    border-radius: 50%;
    opacity: 0.06;
}
.rev-card.green::after { background: var(--accent); }
.rev-card.yellow::after { background: var(--warning); }
.rev-card.blue::after { background: var(--info); }
.rev-card.red::after { background: var(--danger); }
.rev-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
}
.rev-icon.green { background: rgba(200,245,66,0.12); }
.rev-icon.yellow { background: rgba(255,165,2,0.12); }
.rev-icon.blue { background: rgba(61,156,245,0.12); }
.rev-icon.red { background: rgba(255,71,87,0.12); }
.rev-label { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em; }
.rev-value { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; line-height: 1; }
.rev-sub { font-size: 11px; color: var(--text-muted); margin-top: 5px; }

/* Revenue split */
.rev-split {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 14px; margin-bottom: 24px;
}
.rev-split-card {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 20px 22px;
    display: flex; align-items: center; gap: 16px;
}
.rev-split-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    font-size: 20px;
}
.rev-split-label { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
.rev-split-value { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; }
.rev-split-count { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

/* Chart */
.chart-card {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 22px; margin-bottom: 24px;
}
.chart-bars {
    display: flex; align-items: flex-end; gap: 8px; height: 80px;
}
.chart-bar-wrap {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; gap: 5px;
}
.chart-bar {
    width: 100%; background: var(--accent); border-radius: 4px 4px 0 0;
    min-height: 3px; transition: height 0.3s;
    position: relative;
}
.chart-bar.zero { background: var(--bg3); }
.chart-bar-val { font-size: 10px; color: var(--text-muted); white-space: nowrap; }
.chart-bar-date { font-size: 10px; color: var(--text-muted); }

/* Status tabs */
.status-tabs {
    display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap;
}
.status-tab {
    padding: 6px 14px; border-radius: var(--radius-sm);
    border: 1px solid var(--border); background: transparent;
    font-size: 12px; font-weight: 600; cursor: pointer;
    text-decoration: none; color: var(--text-muted);
    transition: all 0.12s; display: flex; align-items: center; gap: 6px;
    font-family: 'DM Sans', sans-serif;
}
.status-tab:hover { border-color: var(--border-hover); color: var(--text); }
.status-tab.active-paid    { border-color: var(--accent); background: rgba(200,245,66,0.08); color: var(--accent); }
.status-tab.active-pending { border-color: var(--warning); background: rgba(255,165,2,0.08); color: var(--warning); }
.status-tab.active-failed  { border-color: var(--danger); background: rgba(255,71,87,0.08); color: var(--danger); }
.status-tab.active-all     { border-color: var(--border-hover); background: var(--bg3); color: var(--text); }

/* Type badge */
.type-badge-premium {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 99px;
    background: linear-gradient(135deg, rgba(248,184,3,0.15), rgba(255,107,53,0.15));
    border: 1px solid rgba(248,184,3,0.25);
    color: #f8b803; font-size: 11px; font-weight: 700;
}
.type-badge-token {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 99px;
    background: rgba(200,245,66,0.08);
    border: 1px solid rgba(200,245,66,0.2);
    color: var(--accent); font-size: 11px; font-weight: 700;
}
</style>
@endpush

@section('content')

{{-- ─── Revenue Stats ──────────────────────────────────────────────────────── --}}
<div class="rev-grid">
    <div class="rev-card green">
        <div class="rev-icon green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="rev-label">Total Revenue</div>
        <div class="rev-value" style="color:var(--accent);">
            Rp{{ number_format($stats['total_revenue'], 0, ',', '.') }}
        </div>
        <div class="rev-sub">{{ $stats['paid_orders'] }} transaksi lunas</div>
    </div>
    <div class="rev-card yellow">
        <div class="rev-icon yellow">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
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
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        </div>
        <div class="rev-label">Premium Sales</div>
        <div class="rev-value">{{ $stats['premium_sales'] }}</div>
        <div class="rev-sub">Rp{{ number_format($stats['premium_revenue'], 0, ',', '.') }}</div>
    </div>
    <div class="rev-card red">
        <div class="rev-icon red">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
        </div>
        <div class="rev-label">Token Sales</div>
        <div class="rev-value">{{ $stats['token_sales'] }}</div>
        <div class="rev-sub">Rp{{ number_format($stats['token_revenue'], 0, ',', '.') }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:24px;">
    {{-- Revenue split --}}
    <div style="display:flex; flex-direction:column; gap:12px;">
        <div class="rev-split-card">
            <div class="rev-split-icon" style="background:linear-gradient(135deg,rgba(248,184,3,0.15),rgba(255,107,53,0.15));">✦</div>
            <div>
                <div class="rev-split-label">Revenue Premium</div>
                <div class="rev-split-value" style="color:#f8b803;">
                    Rp{{ number_format($stats['premium_revenue'], 0, ',', '.') }}
                </div>
                <div class="rev-split-count">{{ $stats['premium_sales'] }} order · avg Rp{{ $stats['premium_sales'] > 0 ? number_format($stats['premium_revenue'] / $stats['premium_sales'], 0, ',', '.') : 0 }}</div>
            </div>
        </div>
        <div class="rev-split-card">
            <div class="rev-split-icon" style="background:rgba(200,245,66,0.1);">🪙</div>
            <div>
                <div class="rev-split-label">Revenue Token</div>
                <div class="rev-split-value" style="color:var(--accent);">
                    Rp{{ number_format($stats['token_revenue'], 0, ',', '.') }}
                </div>
                <div class="rev-split-count">{{ $stats['token_sales'] }} order · avg Rp{{ $stats['token_sales'] > 0 ? number_format($stats['token_revenue'] / $stats['token_sales'], 0, ',', '.') : 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Revenue chart 7 hari --}}
    <div class="chart-card">
        <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:16px;">
            Revenue 7 Hari Terakhir
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
                    style="height:{{ $maxRev > 0 ? max(3, round($rev/$maxRev*64)) : 3 }}px;">
                </div>
                <div class="chart-bar-date">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ─── Filters & Table ────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-3">
    <h3 style="font-size:15px;font-weight:700;">Daftar Order</h3>
    <span class="text-sm text-muted">{{ $orders->total() }} total</span>
</div>

{{-- Status tabs --}}
<div class="status-tabs">
    @php
    $currentStatus = request('status', '');
    $currentType   = request('type', '');
    $tabs = [
        ['label'=>'Semua',    'status'=>'',         'class'=>'active-all'],
        ['label'=>'Lunas',    'status'=>'paid',      'class'=>'active-paid'],
        ['label'=>'Pending',  'status'=>'pending',   'class'=>'active-pending'],
        ['label'=>'Gagal',    'status'=>'failed',    'class'=>'active-failed'],
        ['label'=>'Kadaluarsa','status'=>'expired',  'class'=>'active-failed'],
    ];
    @endphp
    @foreach($tabs as $tab)
    <a href="{{ route('admin.orders.index', array_merge(request()->except(['status','page']), ['status'=>$tab['status']])) }}"
        class="status-tab {{ $currentStatus === $tab['status'] ? $tab['class'] : '' }}">
        {{ $tab['label'] }}
        <span style="opacity:0.7;">
            ({{ \App\Models\Order::when($tab['status'], fn($q) => $q->where('status', $tab['status']))->count() }})
        </span>
    </a>
    @endforeach
</div>

{{-- Search & Filter --}}
<form method="GET" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input type="text" name="search" class="form-input" placeholder="Cari order ID / nama / email..."
        value="{{ request('search') }}" style="flex:1;min-width:200px;">
    <select name="type" class="form-select" style="width:160px;">
        <option value="">Semua Tipe</option>
        <option value="premium_monthly" {{ request('type')==='premium_monthly' ? 'selected':'' }}>Premium</option>
        <option value="token_purchase"  {{ request('type')==='token_purchase'  ? 'selected':'' }}>Token</option>
    </select>
    <input type="date" name="date_from" class="form-input" style="width:140px;" value="{{ request('date_from') }}" placeholder="Dari">
    <input type="date" name="date_to"   class="form-input" style="width:140px;" value="{{ request('date_to') }}" placeholder="Sampai">
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Reset</a>
</form>

{{-- Table --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Tipe</th>
                <th>Detail</th>
                <th style="text-align:right">Total</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Waktu</th>
                <th style="text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
        <tr>
            {{-- Order ID --}}
            <td>
                <a href="{{ route('admin.orders.show', $order) }}"
                    style="font-family:monospace;font-size:11px;color:var(--text-muted);text-decoration:none;">
                    {{ $order->order_id }}
                </a>
            </td>

            {{-- User --}}
            <td>
                @if($order->user)
                <a href="{{ route('admin.users.show', $order->user) }}"
                    style="color:var(--text);text-decoration:none;font-weight:500;font-size:13px;">
                    {{ $order->user->name }}
                </a>
                <div style="font-size:11px;color:var(--text-muted);">{{ $order->user->email }}</div>
                @else
                <span class="text-muted">—</span>
                @endif
            </td>

            {{-- Tipe --}}
            <td>
                @if($order->isPremiumOrder())
                    <span class="type-badge-premium">✦ Premium</span>
                @else
                    <span class="type-badge-token">🪙 Token</span>
                @endif
            </td>

            {{-- Detail --}}
            <td style="font-size:12px;color:var(--text-muted);">
                @if($order->isPremiumOrder())
                    {{ $order->quantity }} bulan
                @else
                    {{ number_format($order->token_amount) }} token
                @endif
            </td>

            {{-- Total --}}
            <td style="text-align:right;font-weight:700;font-size:14px;">
                Rp{{ number_format($order->amount, 0, ',', '.') }}
            </td>

            {{-- Metode --}}
            <td style="font-size:12px;color:var(--text-muted);">
                {{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}
            </td>

            {{-- Status --}}
            <td>
                @php
                $sbg = [
                    'paid'      => 'badge-green',
                    'pending'   => 'badge-yellow',
                    'failed'    => 'badge-red',
                    'expired'   => 'badge-red',
                    'cancelled' => 'badge-gray',
                ];
                @endphp
                <span class="badge {{ $sbg[$order->status] ?? 'badge-gray' }}">
                    {{ $order->getStatusLabel() }}
                </span>
            </td>

            {{-- Waktu --}}
            <td style="font-size:12px;color:var(--text-muted);">
                {{ $order->created_at->format('d M Y') }}
                <div>{{ $order->created_at->format('H:i') }}</div>
                @if($order->paid_at)
                <div style="color:var(--accent);font-size:10px;">Lunas {{ $order->paid_at->format('H:i') }}</div>
                @endif
            </td>

            {{-- Aksi --}}
            <td>
                <div style="display:flex;gap:4px;justify-content:flex-end;">
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-xs">Detail</a>
                    @if($order->isPending())
                    <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}"
                        onsubmit="return confirm('Tandai order ini sebagai lunas?')">
                        @csrf
                        <button type="submit" class="btn btn-info btn-xs">✓ Lunas</button>
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
            <td colspan="9" style="text-align:center;padding:48px;color:var(--text-muted);">
                Tidak ada order ditemukan.
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $orders->links() }}</div>

@endsection