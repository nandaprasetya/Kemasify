<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tipe order
            $table->enum('type', ['premium_monthly', 'token_purchase']);

            // Detail
            $table->integer('quantity')->default(1);     // bulan (premium) atau jumlah token
            $table->integer('amount');                   // total harga dalam rupiah
            $table->integer('token_amount')->default(0); // token yang akan diberikan

            // Status
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'cancelled',
            ])->default('pending');

            // Midtrans
            $table->string('order_id')->unique();        
            $table->string('snap_token')->nullable();    
            $table->string('payment_type')->nullable();  
            $table->string('transaction_id')->nullable();
            $table->json('midtrans_response')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};