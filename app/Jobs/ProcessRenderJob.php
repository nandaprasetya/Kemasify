<?php

namespace App\Jobs;

use App\Models\RenderJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessRenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(public RenderJob $job)
    {}

    public function handle(): void
    {
        Log::info('ProcessRenderJob: starting', ['job_id' => $this->job->id]);

        $this->job->update(['status' => 'processing', 'started_at' => now()]);

        $project = $this->job->designProject;

        try {
            /**
             * TODO: Implementasi render 3D yang sesungguhnya.
             *
             * Opsi integrasi:
             * 1. Three.js server-side via Puppeteer/Node.js
             * 2. Blender Python script (headless)
             * 3. External render API (Pixotronics, RenderAPI, dll)
             * 4. AWS Lambda + Three.js
             *
             * Untuk sementara: simulasi dengan placeholder render
             * yang meng-overlay desain flat di atas template mockup.
             */

            // Simulasi proses render (replace dengan implementasi nyata)
            sleep(2);

            // Gunakan desain flat sebagai "hasil render" (placeholder)
            $designPath = $project->design_file_path;

            if (!$designPath || !Storage::disk('public')->exists($designPath)) {
                throw new \Exception('File desain tidak ditemukan.');
            }

            // Salin ke folder render sebagai placeholder
            $renderPath  = 'renders/' . $project->user_id . '/' . $project->id . '-render.png';
            $previewPath = 'renders/' . $project->user_id . '/' . $project->id . '-preview.png';

            Storage::disk('public')->copy($designPath, $renderPath);
            Storage::disk('public')->copy($designPath, $previewPath);

            // Update project
            $project->markAsCompleted($renderPath, $previewPath);

            // Update render job
            $this->job->update([
                'status'           => 'completed',
                'output_path'      => $renderPath,
                'completed_at'     => now(),
            ]);

            Log::info('ProcessRenderJob: completed', ['job_id' => $this->job->id]);

        } catch (\Exception $e) {
            Log::error('ProcessRenderJob: failed', [
                'job_id' => $this->job->id,
                'error'  => $e->getMessage(),
            ]);

            $this->job->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);

            $project->markAsFailed();

            // Refund token
            $this->job->user->addTokens(
                amount:      $this->job->tokens_consumed,
                type:        'refund',
                description: 'Refund render gagal: ' . $this->job->id,
            );
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->job->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        $this->job->designProject?->markAsFailed();

        try {
            $this->job->user->addTokens(
                amount: $this->job->tokens_consumed,
                type: 'refund',
                description: 'Refund (render job gagal): ' . $this->job->id,
            );
        } catch (\Exception) {}
    }
}