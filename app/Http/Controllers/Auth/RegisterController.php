<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // Menampilkan form register
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Proses registrasi user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|regex:/^08[0-9]{8,13}$/|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Check for referral code from form input or session
        $referrerId = null;
        $refCode = $request->input('referral_code') ?: $request->session()->pull('referral_code');
        if ($refCode) {
            $request->session()->forget('referral_code');
            $referrer = User::where('referral_code', $refCode)->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        }

        $isResponden = $request->input('role') === 'responden' || $request->boolean('is_responden');

        $user = User::create([
            'name' => $request->name ?? $request->nama,
            'email' => $request->email,
            'phone' => $request->phone ?? $request->whatsapp_number,
            'whatsapp_number' => $request->whatsapp_number ?? $request->phone,
            'password' => Hash::make($request->password),
            'referred_by_id' => $referrerId,
            'is_responden' => $isResponden,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        if ($isResponden) {
            \App\Models\Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $targetRoute = $isResponden ? 'responden.dashboard' : 'user.dashboard';

        return redirect()->route($targetRoute)
            ->with('success', 'Registrasi berhasil! Selamat datang di SurveyCenter.');
    }
}
