<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TokenService
{
    /**
     * Refill token semua free user yang sudah waktunya.
     * Dipanggil dari Scheduler setiap jam.
     *
     * @return int Jumlah user yang di-refill
     */
    public function refillEligibleUsers(?Command $command = null): int
    {
        $users = User::eligibleForRefill()
            ->where('plan', 'free')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            try {
                $transaction = $user->performRefill();
                if ($transaction) {
                    $count++;
                    Log::info('TokenService: refilled user', ['user_id' => $user->id]);
                }
            } catch (\Exception $e) {
                Log::error('TokenService: refill failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $command?->info("Refilled {$count} users.");
        return $count;
    }

    /**
     * Berikan bonus token ke user tertentu.
     */
    public function giveBonus(User $user, int $amount, string $reason = 'Bonus token'): void
    {
        $user->addTokens(
            amount:      $amount,
            type:        'bonus',
            description: $reason,
        );

        Log::info('TokenService: bonus given', [
            'user_id' => $user->id,
            'amount'  => $amount,
            'reason'  => $reason,
        ]);
    }

    /**
     * Upgrade user ke premium.
     */
    public function upgradeToPremium(User $user, int $days = 30): void
    {
        $expiresAt = now()->addDays($days);

        $user->update([
            'plan'            => 'premium',
            'plan_expires_at' => $expiresAt,
        ]);

        // Catat transaksi
        $user->addTokens(
            amount:      0,
            type:        'purchase',
            description: "Upgrade Premium {$days} hari hingga " . $expiresAt->format('d M Y'),
        );

        Log::info('TokenService: user upgraded to premium', [
            'user_id'    => $user->id,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Downgrade user kembali ke free (expired plan).
     */
    public function downgradeToFree(User $user): void
    {
        $user->update([
            'plan'            => 'free',
            'plan_expires_at' => null,
        ]);

        Log::info('TokenService: user downgraded to free', ['user_id' => $user->id]);
    }

    /**
     * Cek dan downgrade semua user premium yang sudah expired.
     */
    public function checkExpiredPlans(): int
    {
        $expiredUsers = User::where('plan', 'premium')
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', now())
            ->get();

        foreach ($expiredUsers as $user) {
            $this->downgradeToFree($user);
        }

        return $expiredUsers->count();
    }
}