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
        // Read role from query parameter, default to 'survey'
        $role = $request->query('role', 'survey');

        // Validate role is one of allowed values
        $role = in_array($role, ['survey', 'responden']) ? $role : 'survey';

        // Store in session for retrieval after OAuth callback
        $request->session()->put('google_auth_role', $role);
        $request->session()->save(); // Force session save before redirect

        return Socialite::driver('google')
            ->with(['state' => $role]) // Also pass role in OAuth state parameter as backup
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        // Try to get role from session first, then fall back to OAuth state parameter
        $role = $request->session()->pull('google_auth_role');

        // If session was lost (common with some server configs), use the state parameter
        if (!$role) {
            $state = $request->query('state', '');
            $role = in_array($state, ['survey', 'responden']) ? $state : 'survey';
        }

        $isResponden = ($role === 'responden');

        try {
            $googleUser = Socialite::driver('google')->user();

            // Find existing user by google_id or email
            $user = User::where('google_id', $googleUser->id)->first()
                ?? User::where('email', $googleUser->email)->first();

            if ($user) {
                return $this->handleExistingUser($user, $googleUser, $isResponden, $request);
            }

            return $this->handleNewUser($googleUser, $isResponden, $request);

        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }

    private function handleExistingUser(User $user, $googleUser, bool $isResponden, Request $request): RedirectResponse
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

        // Upgrade to responden if requested and not already
        if ($isResponden && !$user->is_responden) {
            $user->update(['is_responden' => true]);
            if (!$user->wallet) {
                Wallet::create(['user_id' => $user->id, 'balance' => 0, 'deposit_balance' => 0, 'reward_balance' => 0]);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Redirect based on CHOSEN role (not just account state)
        // If user explicitly chose responden tab, go to responden dashboard
        // If user chose survey tab, go to user dashboard (even if they also have responden role)
        if ($isResponden) {
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

        $dashboard = $isResponden ? 'responden.dashboard' : 'user.dashboard';
        $message = 'Registrasi dengan Google berhasil!';

        return redirect()->route($dashboard)->with('success', $message);
    }
}
