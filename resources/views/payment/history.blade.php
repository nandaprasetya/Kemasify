@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')

@section('breadcrumb')
<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('payment.pricing') }}" style="color:var(--text-muted);text-decoration:none;">Upgrade</a>
    <span class="text-muted">/</span>
    <span style="font-weight:600;">Riwayat Pembayaran</span>
</div>
@endsection

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 style="font-size:22px;font-weight:800;">Riwayat Pembayaran</h1>
    <a href="{{ route('payment.pricing') }}" class="btn btn-primary">
        + Beli Lagi
    </a>
</div>

@if($orders->isEmpty())
<div style="text-align:center;padding:80px;background:var(--bg2);border:1px dashed var(--border);border-radius:var(--radius);">
    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
        style="display:block;margin:0 auto 16px;opacity:0.3;">
        <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
    </svg>
    <p class="text-muted" style="margin-bottom:16px;">Belum ada riwayat pembayaran.</p>
    <a href="{{ route('payment.pricing') }}" class="btn btn-primary">Upgrade Sekarang</a>
</div>
@else
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:var(--bg3);">
                <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Order ID</th>
                <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Paket</th>
                <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Total</th>
                <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Metode</th>
                <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Status</th>
                <th style="text-align:left;padding:12px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);border-bottom:1px solid var(--border);">Tanggal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
        <tr style="border-bottom:1px solid rgba(255,255,255,0.03);">
            <td style="padding:14px 16px;font-family:monospace;font-size:12px;color:var(--text-muted);">
                {{ $order->order_id }}
            </td>
            <td style="padding:14px 16px;font-size:14px;font-weight:600;">
                @if($order->isPremiumOrder())
                <span class="badge badge-premium" style="font-size:11px;">✦ {{ $order->getTypeLabel() }}</span>
                @else
                <span style="color:var(--accent);">🪙 {{ $order->getTypeLabel() }}</span>
                @endif
            </td>
            <td style="padding:14px 16px;font-size:14px;font-weight:700;">
                Rp {{ number_format($order->amount, 0, ',', '.') }}
            </td>
            <td style="padding:14px 16px;font-size:13px;color:var(--text-muted);">
                {{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}
            </td>
            <td style="padding:14px 16px;">
                <span style="color:{{ $order->getStatusColor() }};font-size:12px;font-weight:700;">
                    {{ $order->getStatusLabel() }}
                </span>
            </td>
            <td style="padding:14px 16px;font-size:13px;color:var(--text-muted);">
                {{ $order->created_at->format('d M Y H:i') }}
                @if($order->paid_at)
                <div style="font-size:11px;color:var(--accent);">Lunas: {{ $order->paid_at->format('H:i') }}</div>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:20px;">
    {{ $orders->links() }}
</div>
@endif

@endsection