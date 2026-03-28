<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TokenTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // ─── Type labels ──────────────────────────────────────────────────────────

    public static array $typeLabels = [
        'refill'      => 'Refill',
        'ai_generate' => 'AI Generate',
        'render'      => 'Render 3D',
        'refund'      => 'Refund',
        'bonus'       => 'Bonus',
        'purchase'    => 'Pembelian',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? ucfirst($this->type);
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }
}