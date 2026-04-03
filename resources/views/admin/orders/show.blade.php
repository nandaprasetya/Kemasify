@extends('admin.layouts.admin')
@section('page-title', 'Detail Order: ' . $order->order_id)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <h2 style="font-size:18px;font-weight:800;">Detail Order</h2>
    @php
    $sbg = ['paid'=>'badge-green','pending'=>'badge-yellow','failed'=>'badge-red','expired'=>'badge-red','cancelled'=>'badge-gray'];
    @endphp
    <span class="badge {{ $sbg[$order->status] ?? 'badge-gray' }}" style="font-size:13px;padding:4px 12px;">
        {{ $order->getStatusLabel() }}
    </span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

    {{-- Order info --}}
    <div class="card">
        <h3 style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:16px;">
            Informasi Order
        </h3>
        <div style="display:flex;flex-direction:column;gap:11px;font-size:13px;">
            <div class="flex items-center justify-between">
                <span class="text-muted">Order ID</span>
                <span style="font-family:monospace;font-size:12px;">{{ $order->order_id }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Tipe</span>
                @if($order->isPremiumOrder())
                    <span style="color:#f8b803;font-weight:600;">✦ {{ $order->getTypeLabel() }}</span>
                @else
                    <span style="color:var(--accent);font-weight:600;">🪙 {{ $order->getTypeLabel() }}</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Detail</span>
                <span>
                    @if($order->isPremiumOrder())
                        {{ $order->quantity }} bulan berlangganan
                    @else
                        {{ number_format($order->token_amount) }} token
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Total Bayar</span>
                <span style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:var(--accent);">
                    Rp{{ number_format($order->amount, 0, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Metode Pembayaran</span>
                <span>{{ $order->payment_type ? strtoupper($order->payment_type) : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Transaction ID</span>
                <span style="font-family:monospace;font-size:11px;color:var(--text-muted);">
                    {{ $order->transaction_id ?? '—' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="card">
        <h3 style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:16px;">
            Timeline
        </h3>
        <div style="display:flex;flex-direction:column;gap:14px;">
            @php
            $timeline = [
                ['label'=>'Order Dibuat',    'time'=>$order->created_at,  'color'=>'var(--text-muted)'],
                ['label'=>'Pembayaran Lunas','time'=>$order->paid_at,     'color'=>'var(--accent)'],
                ['label'=>'Kadaluarsa',      'time'=>$order->expired_at,  'color'=>'var(--warning)'],
            ];
            @endphp
            @foreach($timeline as $t)
            <div style="display:flex;align-items:center;gap:12px;font-size:13px;">
                <div style="width:10px;height:10px;border-radius:50%;flex-shrink:0;
                    background:{{ $t['time'] ? $t['color'] : 'var(--bg3)' }};
                    border:1px solid {{ $t['time'] ? $t['color'] : 'var(--border)' }};
                    box-shadow: {{ $t['time'] ? '0 0 8px '.$t['color'].'66' : 'none' }};"></div>
                <span style="width:140px;color:{{ $t['time'] ? 'var(--text)' : 'var(--text-muted)' }};">
                    {{ $t['label'] }}
                </span>
                <span class="text-muted">
                    {{ $t['time'] ? $t['time']->format('d M Y H:i:s') : '—' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- User info --}}
@if($order->user)
<div class="card mb-4">
    <h3 style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:16px;">
        Informasi User
    </h3>
    <div style="display:flex;align-items:center;gap:20px;">
        <div style="width:52px;height:52px;background:var(--accent);border-radius:12px;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:20px;color:#0d0d0f;flex-shrink:0;">
            {{ strtoupper(substr($order->user->name,0,1)) }}
        </div>
        <div style="flex:1;">
            <div style="font-weight:700;font-size:15px;margin-bottom:3px;">{{ $order->user->name }}</div>
            <div style="font-size:13px;color:var(--text-muted);">{{ $order->user->email }}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;font-size:13px;">
            @if($order->user->isPremium())
                <span class="badge badge-premium">✦ Premium</span>
            @else
                <span class="badge badge-gray">Free</span>
            @endif
            <span class="text-muted">Token: <strong style="color:var(--accent);">{{ $order->user->token_balance }}</strong></span>
        </div>
        <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-ghost btn-sm">
            Lihat Profil →
        </a>
    </div>
</div>
@endif

{{-- Midtrans response --}}
@if($order->midtrans_response)
<div class="card mb-4">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <h3 style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.07em;">
            Response Midtrans
        </h3>
        <button onclick="toggleRaw()" class="btn btn-ghost btn-xs">Toggle Raw JSON</button>
    </div>

    {{-- Summary --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
        @foreach(['transaction_status','payment_type','fraud_status'] as $field)
        @if(isset($order->midtrans_response[$field]))
        <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:12px;">
            <div style="font-size:10px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:5px;">
                {{ str_replace('_',' ',$field) }}
            </div>
            <div style="font-size:13px;font-weight:600;">{{ $order->midtrans_response[$field] }}</div>
        </div>
        @endif
        @endforeach
    </div>

    <pre id="raw-json" style="display:none;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px;font-size:11px;color:var(--text-muted);overflow-x:auto;white-space:pre-wrap;word-break:break-all;">{{ json_encode($order->midtrans_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endif

{{-- Actions --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;">
    @if($order->isPending())
    <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}"
        onsubmit="return confirm('Tandai order ini sebagai lunas dan aktifkan benefit?')">
        @csrf
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            Tandai Lunas & Aktifkan
        </button>
    </form>
    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
        onsubmit="return confirm('Batalkan order ini?')">
        @csrf
        <button type="submit" class="btn btn-danger">Batalkan Order</button>
    </form>
    @endif

    @if($order->isPaid())
    <div class="alert alert-success" style="margin:0;padding:10px 14px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        Order lunas — benefit telah diberikan ke user.
    </div>
    @endif
</div>

@push('scripts')
<script>
function toggleRaw() {
    const el = document.getElementById('raw-json');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush

@endsection