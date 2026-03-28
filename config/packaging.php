<?php

/**
 * Kemasify — Konfigurasi platform packaging AI.
 *
 * Semua nilai bisa di-override lewat .env
 */

return [

    // ─── Token ────────────────────────────────────────────────────────────────
    'tokens' => [
        // Jumlah token yang diberikan saat user pertama kali daftar
        'free_initial_amount' => env('TOKEN_FREE_INITIAL', 50),

        // Jumlah token yang diisi ulang setiap 24 jam (untuk free user)
        'free_refill_amount'  => env('TOKEN_FREE_REFILL', 50),

        // Interval refill dalam jam
        'refill_interval_hours' => env('TOKEN_REFILL_HOURS', 24),

        // Biaya per aksi
        'cost_ai_generate' => env('TOKEN_COST_AI_GENERATE', 10),
        'cost_render_3d'   => env('TOKEN_COST_RENDER', 50),
    ],

    // ─── Queue ────────────────────────────────────────────────────────────────
    'queues' => [
        'premium' => 'high',
        'free'    => 'default',
    ],

    // ─── File limits ──────────────────────────────────────────────────────────
    'upload' => [
        'max_size_kb'   => env('UPLOAD_MAX_SIZE_KB', 10240),  // 10MB
        'allowed_types' => ['png', 'jpg', 'jpeg', 'svg', 'pdf'],
    ],

    // ─── AI (Gemini) ──────────────────────────────────────────────────────────
    'ai' => [
        'styles' => [
            'minimalist', 'bold', 'elegant', 'playful',
            'modern', 'vintage', 'eco',
        ],
    ],

];