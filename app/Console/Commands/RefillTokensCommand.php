<?php

namespace App\Console\Commands;

use App\Services\TokenService;
use Illuminate\Console\Command;

class RefillTokensCommand extends Command
{
    protected $signature   = 'tokens:refill {--dry-run : Tampilkan siapa yang akan di-refill tanpa eksekusi}';
    protected $description = 'Refill token untuk semua free user yang sudah waktunya';

    public function handle(TokenService $tokenService): int
    {
        if ($this->option('dry-run')) {
            $users = \App\Models\User::eligibleForRefill()->where('plan', 'free')->get();
            $this->table(
                ['ID', 'Name', 'Email', 'Balance', 'Next Refill'],
                $users->map(fn($u) => [
                    $u->id, $u->name, $u->email,
                    $u->token_balance,
                    $u->token_next_refill_at?->format('d M Y H:i') ?? 'Sekarang',
                ])
            );
            $this->info("Dry run: {$users->count()} user akan di-refill.");
            return self::SUCCESS;
        }

        $this->info('Memulai refill token...');
        $count = $tokenService->refillEligibleUsers($this);
        $this->info("✓ Selesai. {$count} user di-refill.");

        return self::SUCCESS;
    }
}