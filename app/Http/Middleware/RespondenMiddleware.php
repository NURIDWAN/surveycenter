<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RespondenMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login', ['role' => 'responden']);
        }

        $user = Auth::user();

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->is_responden) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Akses ditolak. Akun Anda terdaftar sebagai Pembuat Survey dan tidak dapat mengakses Dashboard Responden.');
        }

        return $next($request);
    }
}
