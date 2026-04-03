<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password',
        'token_balance', 'token_total_earned',
        'token_last_refill_at', 'token_next_refill_at',
        'plan', 'plan_expires_at',
        'provider', 'provider_id', 'avatar',
        'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'token_last_refill_at' => 'datetime',
            'token_next_refill_at' => 'datetime',
            'plan_expires_at'      => 'datetime',
            'password'             => 'hashed',
            'is_admin'             => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function designProjects()
    {
        return $this->hasMany(DesignProject::class);
    }

    public function aiGenerationJobs()
    {
        return $this->hasMany(AiGenerationJob::class);
    }

    public function renderJobs()
    {
        return $this->hasMany(RenderJob::class);
    }

    public function tokenTransactions()
    {
        return $this->hasMany(TokenTransaction::class)->latest();
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class)->latest();
    }

    // ─── Plan Helpers ─────────────────────────────────────────────────────────

    public function isPremium(): bool
    {
        if ($this->plan !== 'premium') return false;
        if ($this->plan_expires_at && $this->plan_expires_at->isPast()) return false;
        return true;
    }

    public function isFree(): bool
    {
        return !$this->isPremium();
    }

    // ─── Token Helpers ────────────────────────────────────────────────────────

    public function hasEnoughTokens(int $required): bool
    {
        return $this->token_balance >= $required;
    }

    public function consumeTokens(int $amount, string $type, string $description = '', ?string $refType = null, ?int $refId = null): TokenTransaction
    {
        if (!$this->hasEnoughTokens($amount)) {
            throw new \Exception('Token tidak cukup. Saldo saat ini: ' . $this->token_balance);
        }

        return \DB::transaction(function () use ($amount, $type, $description, $refType, $refId) {
            $balanceBefore = $this->token_balance;
            $this->decrement('token_balance', $amount);

            return $this->tokenTransactions()->create([
                'type'           => $type,
                'amount'         => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $this->fresh()->token_balance,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => $description,
            ]);
        });
    }

    public function addTokens(int $amount, string $type, string $description = ''): TokenTransaction
    {
        return \DB::transaction(function () use ($amount, $type, $description) {
            $balanceBefore = $this->token_balance;
            $this->increment('token_balance', $amount);
            if ($amount > 0) $this->increment('token_total_earned', $amount);

            return $this->tokenTransactions()->create([
                'type'           => $type,
                'amount'         => +$amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $this->fresh()->token_balance,
                'description'    => $description,
            ]);
        });
    }

    public function canRefill(): bool
    {
        if (!$this->token_next_refill_at) return true;
        return now()->gte($this->token_next_refill_at);
    }

    public function refillCountdown(): ?string
    {
        if ($this->canRefill()) return null;
        return $this->token_next_refill_at->diffForHumans(null, true);
    }

    public function performRefill(): ?TokenTransaction
    {
        if (!$this->canRefill()) return null;

        return \DB::transaction(function () {
            $amount = config('packaging.tokens.free_refill_amount', 50);

            $this->update([
                'token_balance'        => $amount,
                'token_last_refill_at' => now(),
                'token_next_refill_at' => now()->addHours(24),
            ]);

            return $this->tokenTransactions()->create([
                'type'           => 'refill',
                'amount'         => $amount,
                'balance_before' => 0,
                'balance_after'  => $amount,
                'description'    => 'Refill otomatis 24 jam',
            ]);
        });
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePremium($query)
    {
        return $query->where('plan', 'premium')
            ->where(fn($q) => $q->whereNull('plan_expires_at')->orWhere('plan_expires_at', '>', now()));
    }

    public function scopeFree($query)
    {
        return $query->where('plan', 'free');
    }

    public function scopeEligibleForRefill($query)
    {
        return $query->where(fn($q) =>
            $q->whereNull('token_next_refill_at')
              ->orWhere('token_next_refill_at', '<=', now())
        );
    }
}