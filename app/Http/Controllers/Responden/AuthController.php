<?php

namespace App\Http\Controllers\Responden;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRespondenRegistrationRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    /**
     * Handle respondent registration.
     */
    public function register(StoreRespondenRegistrationRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'whatsapp_number' => $request->whatsapp_number,
            'is_responden' => true,
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('responden.dashboard')
            ->with('success', 'Registrasi berhasil! Selamat datang di Survey Center Indonesia.');
    }

    /**
     * Handle respondent login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // If already logged in, logout first
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_responden) {
                // Auto-enable responden role for existing users
                $user->update(['is_responden' => true]);

                // Create wallet if not exists
                if (!$user->wallet) {
                    \App\Models\Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                    ]);
                }
            }

            $request->session()->regenerate();

            return redirect()->route('responden.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle respondent logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
