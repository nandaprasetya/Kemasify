<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Token system
            $table->integer('token_balance')->default(50);          // saldo token saat ini
            $table->integer('token_total_earned')->default(50);      // total token pernah diterima
            $table->timestamp('token_last_refill_at')->nullable();   // kapan terakhir refill
            $table->timestamp('token_next_refill_at')->nullable();   // kapan bisa refill berikutnya

            // Plan
            $table->enum('plan', ['free', 'premium'])->default('free');
            $table->timestamp('plan_expires_at')->nullable();        // null = tidak expire (lifetime) atau free

            // Provider OAuth (opsional)
            $table->string('provider')->nullable();                  // google, github, dll
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};