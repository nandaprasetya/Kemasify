<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount',
        'balance_before', 'balance_after',
        'reference_type', 'reference_id',
        'description', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }
}