@extends('admin.layouts.admin')
@section('page-title', 'Detail Order')

@push('styles')
<style>
    /* ── Detail grid ── */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    /* ── Info rows inside card ── */
    .info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 0;
        border-bottom: 1px solid rgba(255,255,255,0.035);
        gap: 12px;
        font-size: 13px;
    }
    .info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .info-row:first-child { padding-top: 0; }
    .info-row-label { font-size: 11px; color: var(--text-muted); font-weight: 500; white-space: nowrap; }
    .info-row-value { font-size: 13px; font-weight: 500; text-align: right; }

    /* ── Timeline ── */
    .timeline { display: flex; flex-direction: column; gap: 0; }
    .timeline-item {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 10px 0; position: relative;
    }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-line {
        position: absolute;
        left: 7px; top: 28px; bottom: -10px;
        width: 1px;
        background: var(--border);
    }
    .timeline-item:last-child .timeline-line { display: none; }
    .timeline-dot {
        width: 16px; height: 16px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        margin-top: 1px;
        position: relative; z-index: 1;
    }
    .timeline-dot.done {
        border: 2px solid currentColor;
    }
    .timeline-dot.done::after {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
    }
    .timeline-dot.empty {
        border: 1px dashed var(--border-hover);
        background: var(--bg3);
    }
    .timeline-content { flex: 1; min-width: 0; }
    .timeline-label { font-size: 12px; font-weight: 600; margin-bottom: 2px; }
    .timeline-time { font-size: 11px; color: var(--text-muted); }

    /* ── User card ── */
    .user-card {
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .user-avatar-lg {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, var(--accent) 0%, #6030c0 100%);
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Syne', sans-serif; font-weight: 800;
        font-size: 18px; color: #fff;
        flex-shrink: 0;
        box-shadow: 0 0 20px var(--accent-glow);
    }
    .user-meta { flex: 1; min-width: 0; }
    .user-name { font-weight: 700; font-size: 15px; margin-bottom: 2px; }
    .user-email { font-size: 12px; color: var(--text-muted); }
    .user-actions { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }

    /* ── Midtrans response ── */
    .midtrans-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }
    .midtrans-field {
        background: var(--bg3);
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        padding: 10px 12px;
    }
    .midtrans-key {
        font-size: 9px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--text-muted); margin-bottom: 4px;
    }
    .midtrans-val { font-size: 12px; font-weight: 600; }

    .raw-json-wrap {
        background: var(--bg3);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px;
        font-size: 10px;
        color: var(--text-muted);
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-all;
        line-height: 1.6;
        display: none;
        font-family: monospace;
    }

    /* ── Amount highlight ── */
    .amount-large {
        font-family: 'Syne', sans-serif;
        font-size: 22px; font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--accent-bright);
    }

    /* ── Card section title ── */
    .card-section-title {
        font-size: 9px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.1em;
        color: var(--text-muted);
        margin-bottom: 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .card-section-title svg { opacity: 0.5; }

    /* ── Action bar ── */
    .action-bar {
        display: flex; gap: 10px; flex-wrap: wrap;
        align-items: center;
        padding: 16px 20px;
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }

    /* ── Responsive ── */
    @media (max-width: 720px) {
        .detail-grid { grid-template-columns: 1fr; }
        .midtrans-grid { grid-template-columns: 1fr 1fr; }
        .user-actions { flex-direction: row; align-items: center; }
    }
    @media (max-width: 440px) {
        .midtrans-grid { grid-template-columns: 1fr; }
        .user-card { gap: 12px; }
    }
</style>
@endpush

@section('content')

{{-- ════════════════ HEADER ════════════════ --}}
<div class="flex items-center gap-3 mb-6" style="flex-wrap:wrap;">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali
    </a>
    <h2 style="font-size:17px; font-weight:800; font-family:'Syne',sans-serif; letter-spacing:-0.02em;">
        Detail Order
    </h2>
    @php
        $sbg = [
            'paid'      => 'badge-green',
            'pending'   => 'badge-yellow',
            'failed'    => 'badge-red',
            'expired'   => 'badge-red',
            'cancelled' => 'badge-gray',
        ];
    @endphp
    <span class="badge {{ $sbg[$order->status] ?? 'badge-gray' }}" style="font-size:11px; padding:4px 12px;">
        {{ $order->getStatusLabel() }}
    </span>
    <code style="margin-left:auto; font-size:10px; color:var(--text-muted); background:var(--bg3); border:1px solid var(--border); padding:3px 9px; border-radius:var(--radius-xs); letter-spacing:0.04em;">
        {{ $order->order_id }}
    </code>
</div>

{{-- ════════════════ DETAIL GRID ════════════════ --}}
<div class="detail-grid">

    {{-- Order Info --}}
    <div class="card">
        <div class="card-section-title">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
            Informasi Order
        </div>
        <div class="info-row">
            <span class="info-row-label">Tipe</span>
            <div class="info-row-value">
                @if($order->isPremiumOrder())
                    <span style="color:#f8b803; font-weight:700;">✦ {{ $order->getTypeLabel() }}</span>
                @else
                    <span style="color:var(--accent-bright); font-weight:700;">⚡ {{ $order->getTypeLabel() }}</span>
                @endif
            </div>
        </div>
        <div class="info-row">
            <span class="info-row-label">Detail</span>
            <div class="info-row-value">
                @if($order->isPremiumOrder())
                    {{ $order->quantity }} bulan berlangganan
                @else
                    {{ number_format($order->token_amount) }} token
                @endif
            </div>
        </div>
        <div class="info-row">
            <span class="info-row-label">Total Bayar</span>
            <div class="info-row-value">
                <span class="amount-large">Rp{{ number_format($order->amount, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="info-row">
            <span class="info-row-label">Metode Pembayaran</span>
            <div class="info-row-value" style="color:var(--text-dim);">
                {{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}
            </div>
        </div>
        <div class="info-row">
            <span class="info-row-label">Transaction ID</span>
            <div class="info-row-value">
                <code style="font-size:10px; color:var(--text-muted); letter-spacing:0.02em;">
                    {{ $order->transaction_id ?? '—' }}
                </code>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="card">
        <div class="card-section-title">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Timeline
        </div>
        @php
            $timeline = [
                [
                    'label'  => 'Order Dibuat',
                    'time'   => $order->created_at,
                    'color'  => 'var(--text-dim)',
                    'done'   => true,
                ],
                [
                    'label'  => 'Pembayaran Lunas',
                    'time'   => $order->paid_at,
                    'color'  => 'var(--success)',
                    'done'   => (bool) $order->paid_at,
                ],
                [
                    'label'  => 'Kadaluarsa',
                    'time'   => $order->expired_at,
                    'color'  => 'var(--warning)',
                    'done'   => (bool) $order->expired_at,
                ],
            ];
        @endphp
        <div class="timeline">
            @foreach($timeline as $i => $t)
            <div class="timeline-item">
                <div class="timeline-line"></div>
                <div class="timeline-dot {{ $t['done'] ? 'done' : 'empty' }}"
                     style="{{ $t['done'] ? 'color:'.$t['color'].'; box-shadow:0 0 8px '.$t['color'].'55;' : '' }}">
                </div>
                <div class="timeline-content">
                    <div class="timeline-label" style="color:{{ $t['done'] ? 'var(--text)' : 'var(--text-muted)' }};">
                        {{ $t['label'] }}
                    </div>
                    <div class="timeline-time">
                        @if($t['time'])
                            {{ $t['time']->format('d M Y') }}
                            <span style="color:var(--accent-bright); font-weight:600;">{{ $t['time']->format('H:i:s') }}</span>
                        @else
                            <span style="opacity:.5;">—</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ════════════════ USER INFO ════════════════ --}}
@if($order->user)
<div class="card mb-4">
    <div class="card-section-title">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Informasi User
    </div>
    <div class="user-card">
        <div class="user-avatar-lg">{{ strtoupper(substr($order->user->name, 0, 1)) }}</div>
        <div class="user-meta">
            <div class="user-name">{{ $order->user->name }}</div>
            <div class="user-email">{{ $order->user->email }}</div>
        </div>
        <div class="user-actions">
            @if($order->user->isPremium())
                <span class="badge badge-premium">✦ Premium</span>
            @else
                <span class="badge badge-gray">Free</span>
            @endif
            <span class="text-xs text-muted">
                Token: <strong style="color:var(--accent-bright); font-family:'Syne',sans-serif;">{{ number_format($order->user->token_balance) }}</strong>
            </span>
        </div>
        <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-ghost btn-sm">
            Lihat Profil
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>
@endif

{{-- ════════════════ MIDTRANS RESPONSE ════════════════ --}}
@if($order->midtrans_response)
<div class="card mb-4">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; gap:12px; flex-wrap:wrap;">
        <div class="card-section-title" style="margin-bottom:0;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Response Midtrans
        </div>
        <button onclick="toggleRaw()" class="btn btn-ghost btn-xs" id="toggleBtn">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            Lihat Raw JSON
        </button>
    </div>

    <div class="midtrans-grid">
        @foreach(['transaction_status', 'payment_type', 'fraud_status'] as $field)
        @if(isset($order->midtrans_response[$field]))
        <div class="midtrans-field">
            <div class="midtrans-key">{{ str_replace('_', ' ', $field) }}</div>
            <div class="midtrans-val"
                 style="color:{{ $field === 'transaction_status' && $order->midtrans_response[$field] === 'settlement' ? 'var(--success)' : 'var(--text)' }}">
                {{ $order->midtrans_response[$field] }}
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <div class="raw-json-wrap" id="rawJson">{{ json_encode($order->midtrans_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</div>
</div>
@endif

{{-- ════════════════ ACTIONS ════════════════ --}}
<div class="action-bar">
    @if($order->isPending())
        <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}"
              onsubmit="return confirm('Tandai order ini sebagai lunas dan aktifkan benefit?')">
            @csrf
            <button type="submit" class="btn btn-success">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                Tandai Lunas & Aktifkan
            </button>
        </form>
        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
              onsubmit="return confirm('Batalkan order ini? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            <button type="submit" class="btn btn-danger">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Batalkan Order
            </button>
        </form>
    @endif

    @if($order->isPaid())
        <div class="alert alert-success" style="margin:0; flex:1;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            Order lunas — benefit telah diberikan ke user.
        </div>
    @endif

    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm" style="margin-left:auto;">
        ← Kembali ke Daftar
    </a>
</div>

@push('scripts')
<script>
function toggleRaw() {
    const el  = document.getElementById('rawJson');
    const btn = document.getElementById('toggleBtn');
    const show = el.style.display !== 'block';
    el.style.display  = show ? 'block' : 'none';
    btn.innerHTML = show
        ? '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> Sembunyikan JSON'
        : '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> Lihat Raw JSON';
}
</script>
@endpush

@endsection