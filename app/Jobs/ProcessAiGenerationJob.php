<?php

namespace App\Jobs;

use App\Models\AiGenerationJob;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries   = 2;

    public function __construct(public AiGenerationJob $job)
    {}

    public function handle(GeminiService $geminiService): void
    {
        Log::info('ProcessAiGenerationJob: starting', ['job_id' => $this->job->id]);

        $this->job->update([
            'status'     => 'processing',
            'started_at' => now(),
        ]);

        try {
            $imagePath = $geminiService->generatePackagingDesign($this->job);

            $this->job->update([
                'status'            => 'completed',
                'result_image_path' => $imagePath,
                'completed_at'      => now(),
                'queue_position'    => null,
            ]);

            // Update proyek
            $this->job->designProject->update([
                'design_file_path' => $imagePath,
                'design_source'    => 'ai_generated',
            ]);

            Log::info('ProcessAiGenerationJob: completed', ['job_id' => $this->job->id, 'path' => $imagePath]);

        } catch (\Exception $e) {
            Log::error('ProcessAiGenerationJob: failed', [
                'job_id' => $this->job->id,
                'error'  => $e->getMessage(),
            ]);

            $this->job->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);

            // Refund token
            $this->job->user->addTokens(
                amount:      $this->job->tokens_consumed,
                type:        'refund',
                description: 'Refund gagal generate: ' . $this->job->id,
            );
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessAiGenerationJob: job failed completely', [
            'job_id' => $this->job->id,
            'error'  => $e->getMessage(),
        ]);

        $this->job->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

        // Refund
        try {
            $this->job->user->addTokens(
                amount: $this->job->tokens_consumed,
                type: 'refund',
                description: 'Refund (job gagal): ' . $this->job->id,
            );
        } catch (\Exception) {}
    }
}