<?php

namespace App\Http\Controllers;

use App\Models\DesignProject;
use App\Models\RenderJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RencerController extends Controller
{
    // Token cost for 3D render
    const TOKEN_COST = 50;

    /**
     * User premium meminta render 3D.
     */
    public function requestRender(string $slug)
    {
        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        // Gate: hanya premium
        if ($user->isFree()) {
            return response()->json([
                'success' => false,
                'error'   => 'Fitur render 3D hanya tersedia untuk pengguna premium.',
            ], 403);
        }

        // Harus ada desain
        if (!$project->design_file_path) {
            return response()->json([
                'success' => false,
                'error'   => 'Upload atau generate desain terlebih dahulu.',
            ], 422);
        }

        // Cek token
        if (!$user->hasEnoughTokens(self::TOKEN_COST)) {
            return response()->json([
                'success'          => false,
                'error'            => 'Token tidak cukup untuk render.',
                'token_balance'    => $user->token_balance,
                'token_needed'     => self::TOKEN_COST,
                'refill_countdown' => $user->refillCountdown(),
            ], 402);
        }

        // Konsumsi token
        try {
            $user->consumeTokens(
                amount:      self::TOKEN_COST,
                type:        'render',
                description: 'Render 3D untuk proyek: ' . $project->name,
            );
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 402);
        }

        // Tandai project sedang rendering
        $project->markAsRendering();

        // Buat render job
        $renderJob = RenderJob::create([
            'user_id'           => $user->id,
            'design_project_id' => $project->id,
            'status'            => 'pending',
            'tokens_consumed'   => self::TOKEN_COST,
            'queued_at'         => now(),
        ]);

        // Dispatch job ke queue
        \App\Jobs\ProcessRenderJob::dispatch($renderJob)->onQueue('high');

        return response()->json([
            'success'       => true,
            'job_id'        => $renderJob->id,
            'status'        => 'pending',
            'message'       => 'Render dimulai. Hasil akan tersedia dalam beberapa menit.',
            'token_balance' => $user->fresh()->token_balance,
        ]);
    }

    /**
     * Cek status render job.
     */
    public function status(int $jobId)
    {
        $user = Auth::user();
        $job  = RenderJob::where('id', $jobId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $response = [
            'job_id' => $job->id,
            'status' => $job->status,
        ];

        if ($job->status === 'completed') {
            $response['render_url']   = $job->project?->render_url;
            $response['preview_url']  = $job->project?->render_preview_path
                ? asset('storage/'.$job->project->render_preview_path)
                : null;
        }

        if ($job->status === 'failed') {
            $response['error'] = $job->error_message ?? 'Render gagal. Token akan dikembalikan.';
        }

        return response()->json($response);
    }

    /**
     * Download hasil render (premium only).
     */
    public function download(string $slug)
    {
        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)->forUser($user->id)->firstOrFail();

        if ($user->isFree()) {
            abort(403, 'Download render hanya untuk pengguna premium.');
        }

        if (!$project->render_output_path) {
            abort(404, 'Render belum tersedia.');
        }

        return Storage::disk('public')->download(
            $project->render_output_path,
            $project->name . '-render.png'
        );
    }
}