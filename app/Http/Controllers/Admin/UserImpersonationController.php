<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserImpersonationController extends Controller
{
    public function impersonate(Request $request, User $user)
    {
        $admin = Auth::user();

        if (!$admin || !$admin->is_admin) {
            abort(403, 'Unauthorized');
        }

        if ($request->session()->has('impersonator_admin_id')) {
            return back()->with('error', 'Anda sudah berada dalam mode login sebagai user.');
        }

        if ((int) $user->id === (int) $admin->id) {
            return back()->with('error', 'Anda sudah login dengan akun ini.');
        }

        if ((bool) $user->is_admin) {
            return back()->with('error', 'Tidak dapat login sebagai akun admin lain.');
        }

        $impersonatorId = (int) $admin->id;
        $impersonatorName = (string) $admin->name;

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('impersonator_admin_id', $impersonatorId);
        $request->session()->put('impersonator_admin_name', $impersonatorName);

        $targetRoute = $user->is_responden ? 'responden.dashboard' : 'user.dashboard';
        return redirect()->route($targetRoute)
            ->with('success', 'Berhasil login sebagai user ' . $user->name . '.');
    }

    public function stop(Request $request)
    {
        $impersonatorId = (int) $request->session()->get('impersonator_admin_id');

        if ($impersonatorId <= 0) {
            return redirect()->route('user.dashboard')->with('error', 'Anda tidak sedang login sebagai user.');
        }

        $admin = User::where('id', $impersonatorId)
            ->where('is_admin', true)
            ->first();

        if (!$admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Sesi admin tidak valid. Silakan login ulang.',
            ]);
        }

        Auth::login($admin);
        $request->session()->regenerate();
        $request->session()->forget(['impersonator_admin_id', 'impersonator_admin_name']);

        return redirect()->route('crm.manage-users')
            ->with('success', 'Kembali ke akun admin berhasil.');
    }
}
