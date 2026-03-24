<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan template 3D model produk yang tersedia
     * (box, bottle, pouch, can, tube, dll)
     */
    public function up(): void
    {
        Schema::create('product_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                          // "Cardboard Box", "Bottle 500ml"
            $table->string('slug')->unique();                                // "cardboard-box", "bottle-500ml"
            $table->string('category');                                      // box, bottle, pouch, can, tube
            $table->text('description')->nullable();
            $table->string('thumbnail_path');                                // preview image
            $table->string('model_3d_path')->nullable();                     // .glb / .obj file untuk render
            $table->json('dimensions')->nullable();                          // {"width": 10, "height": 15, "depth": 5} cm
            $table->json('printable_areas')->nullable();                     // area yang bisa didesain [{name, x, y, w, h}]
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);                   // model eksklusif premium
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_models');
    }
};