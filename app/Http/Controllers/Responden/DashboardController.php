<?php

namespace App\Http\Controllers\Responden;

use App\Http\Controllers\Controller;
use App\Services\SurveyEligibilityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private SurveyEligibilityService $surveyEligibilityService
    ) {}

    /**
     * Display the respondent dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // Get split balances from wallet (handle null wallet = 0)
        $depositBalance = (int) ($wallet?->deposit_balance ?? 0);
        $rewardBalance = (int) ($wallet?->reward_balance ?? 0);
        $saldo = (int) ($wallet?->balance ?? 0);

        // Count survey tersedia using SurveyEligibilityService
        $surveyTersediaCount = $this->surveyEligibilityService->getAvailableSurveys($user)->count();

        // Count menunggu verifikasi
        $menungguCount = $user->surveyFillings()
            ->where('status', 'menunggu_verifikasi')
            ->count();

        // Count disetujui
        $disetujuiCount = $user->surveyFillings()
            ->where('status', 'disetujui')
            ->count();

        // Get list of available surveys (limit 5)
        $availableSurveys = $this->surveyEligibilityService->getAvailableSurveys($user)
            ->latest()
            ->limit(5)
            ->get();

        // Check if profile is complete
        $profileComplete = $user->tanggal_lahir && $user->jenis_kelamin && $user->provinsi && $user->kota;

        return view('responden.dashboard.index', compact(
            'saldo',
            'depositBalance',
            'rewardBalance',
            'surveyTersediaCount',
            'menungguCount',
            'disetujuiCount',
            'availableSurveys',
            'profileComplete'
        ));
    }
}
