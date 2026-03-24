<?php

namespace App\Http\Controllers;

use App\Models\AiGenerationJob;
use App\Models\DesignProject;
use App\Services\GeminiService;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiGenerationController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService,
        private readonly TokenService  $tokenService,
    ) {}

    // ─── Request Generate ─────────────────────────────────────────────────────

    /**
     * User meminta generate desain AI.
     *
     * Free user  → job masuk queue, diproses background
     * Premium user → langsung diproses (sync atau high-priority queue)
     */
    public function generate(Request $request, string $slug)
    {
        $request->validate([
            'prompt'          => ['required', 'string', 'min:10', 'max:500'],
            'style'           => ['nullable', 'string', 'in:minimalist,bold,elegant,playful,modern,vintage,eco'],
            'color_palette'   => ['nullable', 'string', 'max:100'],
            'target_audience' => ['nullable', 'string', 'max:100'],
        ]);

        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        // ── Cek token ────────────────────────────────────────────────────────
        if (!$user->hasEnoughTokens(AiGenerationJob::TOKEN_COST)) {
            return response()->json([
                'success'          => false,
                'error'            => 'Token tidak cukup.',
                'token_balance'    => $user->token_balance,
                'token_needed'     => AiGenerationJob::TOKEN_COST,
                'refill_countdown' => $user->refillCountdown(),
            ], 402);
        }

        // ── Konsumsi token ────────────────────────────────────────────────────
        try {
            $user->consumeTokens(
                amount:      AiGenerationJob::TOKEN_COST,
                type:        'ai_generate',
                description: 'Generate desain AI untuk proyek: ' . $project->name,
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 402);
        }

        // ── Buat job ──────────────────────────────────────────────────────────
        $priority = $user->isPremium() ? 'high' : 'normal';

        $job = AiGenerationJob::create([
            'user_id'           => $user->id,
            'design_project_id' => $project->id,
            'prompt'            => $request->prompt,
            'style'             => $request->style,
            'color_palette'     => $request->color_palette,
            'target_audience'   => $request->target_audience,
            'priority'          => $priority,
            'status'            => 'pending',
            'queue_position'    => $this->getNextQueuePosition($priority),
            'queued_at'         => now(),
            'tokens_consumed'   => AiGenerationJob::TOKEN_COST,
        ]);

        // Update referensi token transaction
        $user->tokenTransactions()->latest()->first()->update([
            'reference_type' => AiGenerationJob::class,
            'reference_id'   => $job->id,
        ]);

        // ── Premium: langsung dispatch ke high-priority queue ─────────────────
        if ($user->isPremium()) {
            \App\Jobs\ProcessAiGenerationJob::dispatch($job)->onQueue('high');

            return response()->json([
                'success'  => true,
                'job_id'   => $job->id,
                'status'   => 'processing',
                'message'  => 'Desain sedang dibuat...',
                'is_queued' => false,
            ]);
        }

        // ── Free: masuk queue normal ──────────────────────────────────────────
        \App\Jobs\ProcessAiGenerationJob::dispatch($job)->onQueue('default');

        return response()->json([
            'success'        => true,
            'job_id'         => $job->id,
            'status'         => 'pending',
            'queue_position' => $job->queue_position,
            'message'        => 'Desain masuk antrian. Posisi: #' . $job->queue_position,
            'is_queued'      => true,
        ]);
    }

    // ─── Cek Status Job ───────────────────────────────────────────────────────

    public function status(int $jobId)
    {
        $user = Auth::user();
        $job  = AiGenerationJob::where('id', $jobId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $response = [
            'job_id'         => $job->id,
            'status'         => $job->status,
            'queue_position' => $job->queue_position,
        ];

        if ($job->isCompleted()) {
            $response['result_url'] = $job->result_url;
        }

        if ($job->isFailed()) {
            $response['error'] = 'Generate gagal. Token akan dikembalikan.';
        }

        return response()->json($response);
    }

    // ─── Batalkan Job ─────────────────────────────────────────────────────────

    public function cancel(int $jobId)
    {
        $user = Auth::user();
        $job  = AiGenerationJob::where('id', $jobId)
            ->where('user_id', $user->id)
            ->where('status', 'pending') // hanya bisa cancel jika masih pending
            ->firstOrFail();

        $job->update(['status' => 'cancelled']);

        // Refund token
        $user->addTokens(
            amount:      $job->tokens_consumed,
            type:        'refund',
            description: 'Refund generate dibatalkan',
        );

        return response()->json(['success' => true, 'message' => 'Job dibatalkan dan token dikembalikan.']);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function getNextQueuePosition(string $priority): int
    {
        return AiGenerationJob::where('status', 'pending')
            ->where('priority', $priority)
            ->max('queue_position') + 1 ?? 1;
    }
}