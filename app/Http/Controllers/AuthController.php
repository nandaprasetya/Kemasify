<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ─── Show Forms ───────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // ─── Register ─────────────────────────────────────────────────────────────

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $freeTokens = config('packaging.tokens.free_initial_amount', 50);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'token_balance'        => $freeTokens,
            'token_total_earned'   => $freeTokens,
            'token_last_refill_at' => now(),
            'token_next_refill_at' => now()->addHours(24),
            'plan'                 => 'free',
        ]);

        // Catat transaksi awal
        $user->tokenTransactions()->create([
            'type'           => 'refill',
            'amount'         => $freeTokens,
            'balance_before' => 0,
            'balance_after'  => $freeTokens,
            'description'    => 'Token awal pendaftaran',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Selamat datang! Kamu mendapat ' . $freeTokens . ' token gratis.');
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        // ← Tambah ini: admin redirect ke admin panel
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}