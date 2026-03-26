<?php

namespace App\Http\Controllers;

use App\Models\DesignProject;
use App\Models\RenderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RenderController extends Controller
{
    // ─── Request Render 3D ────────────────────────────────────────────────────

    /**
     * Mengonsumsi 50 token. Premium = high priority (langsung), free = queue.
     */
    public function render(Request $request, string $slug)
    {
        $request->validate([
            'output_format'    => ['nullable', 'in:png,webp,jpg'],
            'output_resolution' => ['nullable', 'in:1920x1080,2560x1440,3840x2160'],
            'render_settings'  => ['nullable', 'array'],
        ]);

        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        // ── Validasi: proyek harus punya desain ───────────────────────────────
        if (!$project->design_file_path && !$project->canvas_data) {
            return response()->json([
                'success' => false,
                'error'   => 'Proyek belum memiliki desain. Upload atau buat desain terlebih dahulu.',
            ], 422);
        }

        // ── Cek token ─────────────────────────────────────────────────────────
        if (!$user->hasEnoughTokens(RenderJob::TOKEN_COST)) {
            return response()->json([
                'success'          => false,
                'error'            => 'Token tidak cukup untuk render. Dibutuhkan ' . RenderJob::TOKEN_COST . ' token.',
                'token_balance'    => $user->token_balance,
                'token_needed'     => RenderJob::TOKEN_COST,
                'refill_countdown' => $user->refillCountdown(),
            ], 402);
        }

        // ── Cek apakah ada render yang masih berjalan ─────────────────────────
        $activeRender = $project->renderJobs()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($activeRender) {
            return response()->json([
                'success' => false,
                'error'   => 'Ada render yang masih berjalan untuk proyek ini.',
            ], 409);
        }

        // ── Konsumsi token ─────────────────────────────────────────────────────
        try {
            $user->consumeTokens(
                amount:      RenderJob::TOKEN_COST,
                type:        'render_3d',
                description: 'Render 3D: ' . $project->name,
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 402);
        }

        // ── Tandai proyek sedang dirender ─────────────────────────────────────
        $project->markAsRendering();

        // ── Buat render job ───────────────────────────────────────────────────
        $priority = $user->isPremium() ? 'high' : 'normal';

        $renderJob = RenderJob::create([
            'user_id'           => $user->id,
            'design_project_id' => $project->id,
            'render_engine'     => 'internal',
            'render_settings'   => $request->render_settings ?? [],
            'output_format'     => $request->output_format ?? 'png',
            'output_resolution' => $request->output_resolution ?? '1920x1080',
            'priority'          => $priority,
            'status'            => 'pending',
            'queue_position'    => $this->getNextQueuePosition($priority),
            'queued_at'         => now(),
            'tokens_consumed'   => RenderJob::TOKEN_COST,
        ]);

        // Update referensi di token transaction
        $user->tokenTransactions()->latest()->first()->update([
            'reference_type' => RenderJob::class,
            'reference_id'   => $renderJob->id,
        ]);

        // ── Dispatch job ke queue ─────────────────────────────────────────────
        $queue = $user->isPremium() ? 'high' : 'default';
        \App\Jobs\ProcessRenderJob::dispatch($renderJob)->onQueue($queue);

        return response()->json([
            'success'        => true,
            'job_id'         => $renderJob->id,
            'status'         => 'pending',
            'queue_position' => $renderJob->queue_position,
            'is_premium'     => $user->isPremium(),
            'message'        => $user->isPremium()
                ? 'Render dimulai, mohon tunggu...'
                : 'Render masuk antrian. Posisi: #' . $renderJob->queue_position,
        ]);
    }

    // ─── Cek Status Render ────────────────────────────────────────────────────

    public function status(int $jobId)
    {
        $user      = Auth::user();
        $renderJob = RenderJob::where('id', $jobId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $response = [
            'job_id'         => $renderJob->id,
            'status'         => $renderJob->status,
            'queue_position' => $renderJob->queue_position,
        ];

        if ($renderJob->isCompleted()) {
            $response['preview_url']  = $renderJob->preview_path ? asset('storage/' . $renderJob->preview_path) : null;
            $response['can_download'] = $user->isPremium(); // hanya premium bisa download
        }

        if ($renderJob->status === 'failed') {
            $response['error'] = 'Render gagal. Token akan dikembalikan.';
        }

        return response()->json($response);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function getNextQueuePosition(string $priority): int
    {
        return RenderJob::where('status', 'pending')
            ->where('priority', $priority)
            ->max('queue_position') + 1 ?? 1;
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// ExportController
// ─────────────────────────────────────────────────────────────────────────────


namespace App\Http\Controllers;

use App\Models\DesignProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Download desain flat (PNG) — GRATIS untuk semua user
     */
    public function downloadDesign(string $slug): StreamedResponse
    {
        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        if (!$project->design_file_path || !Storage::disk('public')->exists($project->design_file_path)) {
            abort(404, 'File desain tidak ditemukan.');
        }

        $filename = \Str::slug($project->name) . '-design.' . pathinfo($project->design_file_path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($project->design_file_path, $filename);
    }

    /**
     * Download hasil render 3D — PREMIUM ONLY
     */
    public function downloadRender(string $slug): StreamedResponse
    {
        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        // ── Premium gate ──────────────────────────────────────────────────────
        if ($user->isFree()) {
            abort(403, 'Download hasil render 3D hanya tersedia untuk pengguna premium.');
        }

        if (!$project->render_output_path || !Storage::disk('public')->exists($project->render_output_path)) {
            abort(404, 'File render tidak ditemukan. Pastikan render sudah selesai.');
        }

        $filename = \Str::slug($project->name) . '-render.' . pathinfo($project->render_output_path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($project->render_output_path, $filename);
    }
}