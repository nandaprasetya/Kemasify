<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk tracking job render 3D packaging.
     * Mengonsumsi 50 token. Download hasil hanya untuk premium.
     */
    public function up(): void
    {
        Schema::create('render_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('design_project_id')->constrained()->cascadeOnDelete();

            // Konfigurasi render
            $table->string('render_engine')->default('internal');            // internal atau API eksternal
            $table->json('render_settings')->nullable();                     // lighting, angle, background, dll
            $table->enum('output_format', ['png', 'webp', 'jpg'])->default('png');
            $table->string('output_resolution')->default('1920x1080');

            // Queue
            $table->enum('priority', ['normal', 'high'])->default('normal'); // normal=free, high=premium
            $table->integer('queue_position')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('render_duration_seconds')->nullable();          // berapa lama render berlangsung

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
            ])->default('pending');

            // Hasil
            $table->string('result_path')->nullable();                       // path hasil render
            $table->string('preview_path')->nullable();                      // preview thumbnail
            $table->text('error_message')->nullable();

            // Token
            $table->integer('tokens_consumed')->default(50);

            $table->timestamps();

            $table->index(['status', 'priority', 'queued_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('render_jobs');
    }
};