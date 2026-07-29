<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Exception;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $role = $request->query('role', 'survey');
        $role = in_array($role, ['survey', 'responden']) ? $role : 'survey';

        $request->session()->put('google_auth_role', $role);
        $request->session()->save();

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $role = $request->session()->pull('google_auth_role', 'survey');
        $isResponden = ($role === 'responden');

        try {
            $googleUser = Socialite::driver('google')->user();

            // Find existing user by google_id or email
            $user = User::where('google_id', $googleUser->id)->first()
                ?? User::where('email', $googleUser->email)->first();

            if ($user) {
                return $this->handleExistingUser($user, $googleUser, $request);
            }

            return $this->handleNewUser($googleUser, $isResponden, $request);

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth error', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }

    private function handleExistingUser(User $user, $googleUser, Request $request): RedirectResponse
    {
        // Block admin accounts
        if ($user->is_admin) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun admin tidak dapat login melalui Google.']);
        }

        // Link Google account if not already linked
        if (!$user->google_id) {
            $user->update([
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->is_responden) {
            return redirect()->route('responden.dashboard');
        }

        return redirect()->intended(route('user.dashboard'));
    }

    private function handleNewUser($googleUser, bool $isResponden, Request $request): RedirectResponse
    {
        $referrerId = null;
        $refCode = $request->session()->pull('referral_code');
        if ($refCode) {
            $referrer = User::where('referral_code', $refCode)->first();
            $referrerId = $referrer?->id;
        }

        $newUser = User::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'google_avatar' => $googleUser->avatar,
            'password' => Str::random(32),
            'phone' => null,
            'referred_by_id' => $referrerId,
            'is_responden' => $isResponden,
        ]);

        $newUser->forceFill(['email_verified_at' => now()])->save();

        if ($isResponden) {
            Wallet::create(['user_id' => $newUser->id, 'balance' => 0]);
        }

        Auth::login($newUser);
        $request->session()->regenerate();

        $targetRoute = $isResponden ? 'responden.dashboard' : 'user.dashboard';

        return redirect()->route($targetRoute)->with('success', 'Registrasi dengan Google berhasil!');
    }
}
