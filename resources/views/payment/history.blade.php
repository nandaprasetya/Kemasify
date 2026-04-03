@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')

@section('breadcrumb')
<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('payment.pricing') }}" style="color:var(--text-muted);text-decoration:none;">Upgrade</a>
    <span class="text-muted">/</span>
    <span style="font-weight:600;">Riwayat Pembayaran</span>
</div>
@endsection

@push('styles')
<style>
/* ─── History Table ─────────────────────────────────────────────── */
.history-wrap {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

/* Desktop table */
.history-table {
    width: 100%;
    border-collapse: collapse;
}
.history-table thead tr {
    background: var(--bg-card-hover);
}
.history-table th {
    text-align: left;
    padding: 12px 16px;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.07em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.history-table td {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    vertical-align: middle;
}
.history-table tbody tr:last-child td { border-bottom: none; }
.history-table tbody tr { transition: background 0.1s; }
.history-table tbody tr:hover { background: var(--bg-card-hover); }

/* Mobile cards (hidden on desktop) */
.history-cards { display: none; }
.history-card {
    padding: 16px;
    border-bottom: 1px solid var(--border);
}
.history-card:last-child { border-bottom: none; }
.history-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 10px;
}
.history-card-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
    font-size: 12px;
}
.history-card-meta-item { display: flex; flex-direction: column; gap: 2px; }
.history-card-meta-label { color: var(--text-muted); font-size: 11px; }
.history-card-meta-val { font-weight: 600; font-size: 13px; }

/* Empty state */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: var(--bg-card);
    border: 1px dashed var(--border);
    border-radius: var(--radius-lg);
}

/* Page header */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 12px;
    flex-wrap: wrap;
}

/* ─── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .history-table { display: none; }
    .history-cards { display: block; }
    .page-header h1 { font-size: 18px; }
    .empty-state { padding: 48px 16px; }
}
</style>
@endpush

@section('content')

<div class="page-header">
    <h1 style="font-size:22px;font-weight:800;">Riwayat Pembayaran</h1>
    <a href="{{ route('payment.pricing') }}" class="btn btn-primary">
        + Beli Lagi
    </a>
</div>

@if($orders->isEmpty())
<div class="empty-state">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
        style="display:block;margin:0 auto 16px;opacity:0.25;">
        <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
    </svg>
    <p class="text-muted" style="margin-bottom:16px;">Belum ada riwayat pembayaran.</p>
    <a href="{{ route('payment.pricing') }}" class="btn btn-primary">Upgrade Sekarang</a>
</div>

@else

{{-- ── Desktop Table ─────────────────────────────────────────────── --}}
<div class="history-wrap">
    <table class="history-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Paket</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
        <tr>
            <td style="font-family:monospace;font-size:12px;color:var(--text-muted);">
                {{ $order->order_id }}
            </td>
            <td style="font-size:14px;font-weight:600;">
                @if($order->isPremiumOrder())
                    <span class="badge badge-premium" style="font-size:11px;">✦ {{ $order->getTypeLabel() }}</span>
                @else
                    <span style="color:var(--purple);">🪙 {{ $order->getTypeLabel() }}</span>
                @endif
            </td>
            <td style="font-size:14px;font-weight:700;">
                Rp {{ number_format($order->amount, 0, ',', '.') }}
            </td>
            <td style="font-size:13px;color:var(--text-muted);">
                {{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}
            </td>
            <td>
                <span style="color:{{ $order->getStatusColor() }};font-size:12px;font-weight:700;">
                    {{ $order->getStatusLabel() }}
                </span>
            </td>
            <td style="font-size:13px;color:var(--text-muted);">
                {{ $order->created_at->format('d M Y H:i') }}
                @if($order->paid_at)
                <div style="font-size:11px;color:var(--purple);margin-top:2px;">
                    Lunas: {{ $order->paid_at->format('H:i') }}
                </div>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    {{-- ── Mobile Cards ──────────────────────────────────────────── --}}
    <div class="history-cards">
        @foreach($orders as $order)
        <div class="history-card">
            <div class="history-card-top">
                <div>
                    @if($order->isPremiumOrder())
                        <span class="badge badge-premium" style="font-size:11px;margin-bottom:6px;display:inline-flex;">✦ {{ $order->getTypeLabel() }}</span>
                    @else
                        <span style="color:var(--purple);font-weight:600;font-size:14px;">🪙 {{ $order->getTypeLabel() }}</span>
                    @endif
                    <div style="font-family:monospace;font-size:11px;color:var(--text-muted);margin-top:4px;">
                        {{ $order->order_id }}
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:15px;font-weight:800;font-family:'Syne',sans-serif;">
                        Rp {{ number_format($order->amount, 0, ',', '.') }}
                    </div>
                    <div style="margin-top:4px;">
                        <span style="color:{{ $order->getStatusColor() }};font-size:11px;font-weight:700;">
                            {{ $order->getStatusLabel() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="history-card-meta">
                <div class="history-card-meta-item">
                    <span class="history-card-meta-label">Metode</span>
                    <span class="history-card-meta-val">
                        {{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}
                    </span>
                </div>
                <div class="history-card-meta-item">
                    <span class="history-card-meta-label">Tanggal</span>
                    <span class="history-card-meta-val">{{ $order->created_at->format('d M Y') }}</span>
                </div>
                @if($order->paid_at)
                <div class="history-card-meta-item">
                    <span class="history-card-meta-label">Lunas</span>
                    <span class="history-card-meta-val" style="color:var(--purple);">
                        {{ $order->paid_at->format('H:i') }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<div style="margin-top:20px;">
    {{ $orders->links() }}
</div>
@endif

@endsection