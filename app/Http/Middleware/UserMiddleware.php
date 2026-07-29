<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->is_responden) {
            return redirect()->route('responden.dashboard')
                ->with('error', 'Akses ditolak. Akun Anda terdaftar sebagai Responden dan tidak dapat mengakses Dashboard Pembuat Survey.');
        }

        return $next($request);
    }
}
