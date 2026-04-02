@extends('layouts.app')

@section('title', 'Status Pembayaran')

@section('breadcrumb')
<span style="font-size:15px; font-weight:600;">Status Pembayaran</span>
@endsection

@section('content')

<div style="max-width:520px;margin:0 auto;text-align:center;padding:60px 20px;">

    @if($order && $order->isPaid())
    {{-- SUCCESS --}}
    <div style="width:80px;height:80px;background:rgba(200,245,66,0.1);border:2px solid var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2.5">
            <path d="M20 6L9 17l-5-5"/>
        </svg>
    </div>
    <h1 style="font-size:28px;font-weight:800;margin-bottom:8px;">Pembayaran Berhasil!</h1>
    <p class="text-muted" style="margin-bottom:32px;">
        @if($order->isPremiumOrder())
            Akun kamu telah diupgrade ke <strong style="color:var(--accent)">Premium</strong>
            selama <strong>{{ $order->quantity }} bulan</strong>.
        @else
            <strong style="color:var(--accent)">{{ $order->token_amount }} token</strong>
            telah ditambahkan ke akunmu.
        @endif
    </p>

    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:24px;text-align:left;margin-bottom:32px;">
        <div style="display:flex;flex-direction:column;gap:10px;font-size:14px;">
            <div class="flex items-center justify-between">
                <span class="text-muted">Order ID</span>
                <span style="font-family:monospace;font-size:12px;">{{ $order->order_id }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Paket</span>
                <span style="font-weight:600;">{{ $order->getTypeLabel() }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Total Dibayar</span>
                <span style="font-weight:700;color:var(--accent);">Rp {{ number_format($order->amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Metode Bayar</span>
                <span>{{ strtoupper($order->payment_type ?? '—') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Waktu Bayar</span>
                <span>{{ $order->paid_at?->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;">
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
            Mulai Desain →
        </a>
        <a href="{{ route('payment.history') }}" class="btn btn-ghost btn-lg">
            Riwayat Order
        </a>
    </div>

    @elseif($order && $order->isPending())
    {{-- PENDING --}}
    <div style="width:80px;height:80px;background:rgba(255,165,2,0.1);border:2px solid var(--warning);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
        </svg>
    </div>
    <h1 style="font-size:28px;font-weight:800;margin-bottom:8px;">Menunggu Pembayaran</h1>
    <p class="text-muted" style="margin-bottom:24px;">
        Order <strong>{{ $order->order_id }}</strong> sedang menunggu konfirmasi pembayaran.
        Halaman ini akan otomatis update.
    </p>
    <div id="polling-status" style="margin-bottom:24px;font-size:13px;color:var(--text-muted);">
        <div class="spinner" style="margin:0 auto 8px;"></div>
        Mengecek status pembayaran...
    </div>
    <div style="display:flex;gap:12px;justify-content:center;">
        <a href="{{ route('payment.pricing') }}" class="btn btn-ghost">Kembali</a>
    </div>

    @else
    {{-- FAILED / UNKNOWN --}}
    <div style="width:80px;height:80px;background:rgba(255,71,87,0.1);border:2px solid var(--danger);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>
        </svg>
    </div>
    <h1 style="font-size:28px;font-weight:800;margin-bottom:8px;">
        {{ $order ? 'Pembayaran Gagal' : 'Order Tidak Ditemukan' }}
    </h1>
    <p class="text-muted" style="margin-bottom:32px;">
        {{ $order ? 'Status: ' . $order->getStatusLabel() : 'Silakan coba lagi dari halaman pricing.' }}
    </p>
    <a href="{{ route('payment.pricing') }}" class="btn btn-primary btn-lg">
        Coba Lagi
    </a>
    @endif

</div>

@if($order && $order->isPending())
@push('scripts')
<script>
// Poll status setiap 3 detik selama max 5 menit
let pollCount = 0;
const maxPoll = 100;
const orderId = '{{ $order->order_id }}';

const poll = setInterval(async () => {
    pollCount++;
    if (pollCount > maxPoll) {
        clearInterval(poll);
        document.getElementById('polling-status').innerHTML =
            '<span style="color:var(--warning)">Waktu habis. Silakan cek riwayat order.</span>';
        return;
    }

    try {
        const res  = await fetch('/payment/check/' + orderId, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (data.success && data.is_paid) {
            clearInterval(poll);
            window.location.reload();
        }
    } catch(e) {}
}, 3000);
</script>
@endpush
@endif

@endsection