<?php

namespace App\Http\Controllers\Responden;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDemographicProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the demographic profile edit form with current data.
     */
    public function edit(): View
    {
        return view('responden.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the respondent's demographic profile.
     * Supports partial updates — only submitted fields are updated.
     */
    public function update(UpdateDemographicProfileRequest $request): RedirectResponse
    {
        $fields = $request->only([
            'tanggal_lahir',
            'jenis_kelamin',
            'provinsi',
            'kota',
            'pendidikan',
            'pekerjaan',
        ]);

        // Filter out null values to support partial updates
        $fields = array_filter($fields, fn ($value) => !is_null($value));

        Auth::user()->update($fields);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
