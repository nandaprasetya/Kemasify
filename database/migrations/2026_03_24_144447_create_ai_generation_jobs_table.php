<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk tracking job generate desain via Gemini API.
     * User free masuk queue, premium langsung diproses.
     */
    public function up(): void
    {
        Schema::create('ai_generation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('design_project_id')->constrained()->cascadeOnDelete();

            // Prompt dan parameter
            $table->text('prompt');                                          // deskripsi desain dari user
            $table->string('style')->nullable();                             // minimalist, bold, elegant, dll
            $table->string('color_palette')->nullable();                     // primary colors yang diinginkan
            $table->string('target_audience')->nullable();                   // kids, premium, eco, dll
            $table->json('additional_params')->nullable();                   // parameter tambahan

            // Queue & priority
            $table->enum('priority', ['normal', 'high'])->default('normal'); // normal=free, high=premium
            $table->integer('queue_position')->nullable();                   // posisi dalam antrian
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Status
            $table->enum('status', [
                'pending',      // menunggu di queue
                'processing',   // sedang diproses oleh Gemini
                'completed',    // berhasil
                'failed',       // gagal
                'cancelled',    // dibatalkan user
            ])->default('pending');

            // Hasil
            $table->string('result_image_path')->nullable();                 // path hasil generate
            $table->json('gemini_response')->nullable();                     // raw response API (untuk debug)
            $table->text('error_message')->nullable();                       // pesan error jika gagal

            // Token
            $table->integer('tokens_consumed')->default(10);                 // selalu 10 untuk generate

            $table->timestamps();

            $table->index(['status', 'priority', 'queued_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_jobs');
    }
};