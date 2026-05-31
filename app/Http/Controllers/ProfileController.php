<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Menampilkan halaman profil
    public function show()
    {
        $user = Auth::user();
        return view('user.profile.show', compact('user'));
    }

    // Menampilkan form edit profil
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile.edit', compact('user'));
    }

    // Update profil
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|regex:/^08[0-9]{8,13}$/|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed', // password optional
        ]);

        // Update field
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('user.profile.show')->with('success', 'Profil berhasil diperbarui!');
    }

    // Update nomor HP via Modal
    public function updatePhone(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone' => 'required|string|regex:/^08[0-9]{8,13}$/|unique:users,phone,' . $user->id,
        ]);

        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Nomor HP berhasil disimpan.');
    }
}
