<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail seluruh transaksi token user.
     * Digunakan untuk history, debugging, dan dispute.
     */
    public function up(): void
    {
        Schema::create('token_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Jenis transaksi
            $table->enum('type', [
                'refill',           // otomatis 24 jam
                'purchase',         // beli token (premium)
                'ai_generate',      // -10 untuk generate
                'render_3d',        // -50 untuk render
                'bonus',            // bonus dari promo/event
                'refund',           // refund jika gagal
                'admin_adjustment', // koreksi manual oleh admin
            ]);

            // Jumlah (positif = masuk, negatif = keluar)
            $table->integer('amount');                                       // +50, -10, -50, dll
            $table->integer('balance_before');                               // saldo sebelum transaksi
            $table->integer('balance_after');                                // saldo setelah transaksi

            // Referensi ke job terkait
            $table->string('reference_type')->nullable();                    // 'AiGenerationJob' | 'RenderJob'
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('description')->nullable();                         // keterangan tambahan
            $table->json('metadata')->nullable();                            // data tambahan (invoice, dll)

            $table->timestamps();

            $table->index('user_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_transactions');
    }
};