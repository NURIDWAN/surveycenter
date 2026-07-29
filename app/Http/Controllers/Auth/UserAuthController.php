<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm(Request $request)
    {
        $articles = Article::published()->latest()->take(4)->get();
        $redirect = $request->query('redirect');

        return view('auth.login', compact('articles', 'redirect'));
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'redirect' => ['nullable', 'string', 'max:2048'],
        ]);

        $redirect = $credentials['redirect'] ?? null;
        unset($credentials['redirect']);

        if (Auth::attempt(array_merge($credentials, ['is_admin' => 0]))) {
            $user = Auth::user();

            if ($user->is_responden) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                ActivityLog::log('login_rejected_role', 'Responden user attempted login on Pembuat Survey tab: ' . $user->email, [
                    'role' => 'user',
                ]);

                return back()->withErrors([
                    'email' => 'Akun ini terdaftar sebagai Responden. Silakan masuk via pilihan \'Isi Survey & Dapatkan Saldo\'.',
                ])->onlyInput('email', 'redirect');
            }

            $request->session()->regenerate();

            ActivityLog::log('login', 'User logged in: ' . Auth::user()->email, [
                'role' => 'user',
            ]);

            if ($redirect && $this->isSafeRedirect($redirect, $request)) {
                return redirect()->to($redirect);
            }

            return redirect()->intended(route('user.dashboard'));
        }

        ActivityLog::log('login_failed', 'Failed user login attempt: ' . $request->email, [
            'email' => $request->email,
        ]);

        return back()->withErrors([
            'email' => 'Email atau password salah, atau akun ini bukan akun user biasa.',
        ])->onlyInput('email', 'redirect');
    }

    // Proses logout
    public function logout(Request $request)
    {
        $user = Auth::user();

        ActivityLog::log('logout', 'User logged out: ' . ($user?->email ?? 'unknown'), [
            'role' => 'user',
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function isSafeRedirect(string $url, Request $request): bool
    {
        if (Str::startsWith($url, '/')) {
            return true;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $targetHost = parse_url($url, PHP_URL_HOST);
        $currentHost = parse_url($request->root(), PHP_URL_HOST);

        return $targetHost !== null && $currentHost !== null && strcasecmp($targetHost, $currentHost) === 0;
    }
}

