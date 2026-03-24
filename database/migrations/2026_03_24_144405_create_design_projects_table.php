<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel utama proyek desain milik user.
     * Setiap proyek terikat ke satu product model dan bisa punya
     * banyak layer desain serta hasil render.
     */
    public function up(): void
    {
        Schema::create('design_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_model_id')->constrained()->restrictOnDelete();

            $table->string('name');                                          // nama proyek oleh user
            $table->string('slug')->unique();                                // untuk URL

            // Status proyek
            $table->enum('status', [
                'draft',        // sedang diedit
                'rendering',    // sedang dirender 3D
                'completed',    // selesai
                'failed',       // render gagal
            ])->default('draft');

            // Desain canvas (disimpan sebagai JSON state Fabric.js / Konva)
            $table->longText('canvas_data')->nullable();                     // JSON state editor

            // File desain flat (hasil upload atau generate AI)
            $table->string('design_file_path')->nullable();                  // gambar flat PNG/SVG
            $table->string('design_source')->nullable();                     // 'upload' | 'ai_generated' | 'manual'

            // Hasil render 3D
            $table->string('render_preview_path')->nullable();               // thumbnail render
            $table->string('render_output_path')->nullable();                // file render final (.png / .webp)

            // Metadata
            $table->json('settings')->nullable();                            // warna background, efek, dll
            $table->boolean('is_public')->default(false);                    // bisa dibagikan publik
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_projects');
    }
};