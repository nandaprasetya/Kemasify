<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'quantity', 'amount', 'token_amount',
        'status', 'order_id', 'snap_token',
        'payment_type', 'transaction_id', 'midtrans_response',
        'paid_at', 'expired_at',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'paid_at'           => 'datetime',
        'expired_at'        => 'datetime',
    ];

    // ─── Pricing constants ────────────────────────────────────────────────────
    const PREMIUM_PRICE      = 50000;  // Rp 50.000/bulan
    const TOKEN_PRICE        = 100;    // Rp 100/token
    const TOKEN_MIN_PURCHASE = 10;     // minimal beli 10 token
    const TOKEN_MAX_PURCHASE = 1000;   // maksimal beli 1000 token

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPaid(): bool      { return $this->status === 'paid'; }
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isExpired(): bool   { return $this->status === 'expired'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }

    public function isPremiumOrder(): bool { return $this->type === 'premium_monthly'; }
    public function isTokenOrder(): bool   { return $this->type === 'token_purchase'; }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'premium_monthly' => 'Premium ' . $this->quantity . ' Bulan',
            'token_purchase'  => $this->token_amount . ' Token',
            default           => $this->type,
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Menunggu Pembayaran',
            'paid'      => 'Lunas',
            'failed'    => 'Gagal',
            'expired'   => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            default     => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'paid'      => 'var(--accent)',
            'pending'   => 'var(--warning)',
            'failed',
            'expired',
            'cancelled' => 'var(--danger)',
            default     => 'var(--text-muted)',
        };
    }

    // Generate unique order ID
    public static function generateOrderId(int $userId): string
    {
        return 'KMS-' . $userId . '-' . time() . '-' . strtoupper(\Str::random(4));
    }
}