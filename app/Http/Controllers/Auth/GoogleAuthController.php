<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect ke halaman login Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google setelah login.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->withErrors(['google' => 'Login dengan Google gagal, coba lagi.']);
        }


        // Cari atau buat user berdasarkan google_id atau email
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'          => $googleUser->getName(),
                'provider'      => 'google',
                'provider_id'   => $googleUser->getId(),
                'avatar'        => $googleUser->getAvatar(),
                // Password null karena login via Google
                'password'      => bcrypt(\Illuminate\Support\Str::random(24)),
            ]
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}