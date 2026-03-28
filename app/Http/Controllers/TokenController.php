<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends Controller
{
    /**
     * Halaman info token & history transaksi user
     */
    public function index()
    {
        $user         = Auth::user();
        $transactions = $user->tokenTransactions()->paginate(20);

        return view('tokens.index', compact('user', 'transactions'));
    }

    /**
     * Cek status token via API (dipanggil frontend secara polling)
     */
    public function status()
    {
        $user = Auth::user()->fresh();

        return response()->json([
            'token_balance'    => $user->token_balance,
            'can_refill'       => $user->canRefill(),
            'refill_countdown' => $user->refillCountdown(),
            'plan'             => $user->plan,
            'is_premium'       => $user->isPremium(),
        ]);
    }

    /**
     * Manual refill (jika sudah waktunya)
     * Untuk user free: hanya bisa refill jika sudah 24 jam
     */
    public function refill()
    {
        $user = Auth::user();

        if (!$user->canRefill()) {
            return response()->json([
                'success'          => false,
                'error'            => 'Belum bisa refill. Coba lagi dalam ' . $user->refillCountdown(),
                'refill_countdown' => $user->refillCountdown(),
            ], 429);
        }

        $transaction = $user->performRefill();

        return response()->json([
            'success'          => true,
            'message'          => 'Token berhasil diisi ulang!',
            'token_balance'    => $user->fresh()->token_balance,
            'refill_countdown' => $user->fresh()->refillCountdown(),
        ]);
    }
}