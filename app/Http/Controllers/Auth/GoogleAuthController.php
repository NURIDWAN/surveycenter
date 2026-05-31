<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Exception;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Priority 1: Find by google_id (returning Google user)
            $user = User::where('google_id', $googleUser->id)->first();

            // Priority 2: Find by email (user registered via email, now logging in with Google)
            if (!$user) {
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Link Google account to existing user
                    $user->update([
                        'google_id' => $googleUser->id,
                        'google_avatar' => $googleUser->avatar,
                    ]);
                }
            }

            if ($user) {
                // Block admin accounts from Google login
                if ($user->is_admin) {
                    return redirect()->route('login')
                        ->withErrors(['email' => 'Akun admin tidak dapat login melalui Google.']);
                }

                Auth::login($user);
                $request->session()->regenerate();

                ActivityLog::log('login', 'User logged in via Google: ' . $user->email, [
                    'role' => 'user',
                    'method' => 'google',
                ]);

                return redirect()->intended(route('user.dashboard'));
            }

            // Register new user
            $referrerId = null;
            $refCode = $request->session()->pull('referral_code');
            if ($refCode) {
                $referrer = User::where('referral_code', $refCode)->first();
                if ($referrer) {
                    $referrerId = $referrer->id;
                }
            }

            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
                'password' => Str::random(32),
                'phone' => null,
                'referred_by_id' => $referrerId,
            ]);

            $newUser->forceFill(['email_verified_at' => now()])->save();

            Auth::login($newUser);
            $request->session()->regenerate();

            ActivityLog::log('register', 'User registered via Google: ' . $newUser->email, [
                'role' => 'user',
                'method' => 'google',
            ]);

            return redirect()->route('user.dashboard')
                ->with('success', 'Registrasi dengan Google berhasil! Silakan lengkapi nomor telepon di menu Profil.');

        } catch (Exception $e) {
            ActivityLog::log('login_failed', 'Failed Google login attempt', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal login menggunakan Google. Silakan coba lagi.']);
        }
    }
}
