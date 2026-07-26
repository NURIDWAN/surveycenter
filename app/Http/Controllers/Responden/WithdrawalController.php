<?php

namespace App\Http\Controllers\Responden;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Services\RespondentWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class WithdrawalController extends Controller
{
    public function __construct(
        private RespondentWithdrawalService $withdrawalService
    ) {}

    /**
     * List respondent's withdrawal history with status.
     */
    public function index(): View
    {
        $withdrawals = Auth::user()
            ->respondentWithdrawals()
            ->latest()
            ->get();

        return view('responden.withdrawals.index', compact('withdrawals'));
    }

    /**
     * Show withdrawal form with current saldo and minimum threshold.
     */
    public function create(): View
    {
        $wallet = Auth::user()->wallet;
        $saldo = (int) ($wallet->balance ?? 0);
        $depositBalance = (int) ($wallet->deposit_balance ?? 0);
        $rewardBalance = (int) ($wallet->reward_balance ?? 0);
        $minThreshold = $this->withdrawalService->getMinimumThreshold();

        return view('responden.withdrawals.create', compact(
            'saldo',
            'depositBalance',
            'rewardBalance',
            'minThreshold'
        ));
    }

    /**
     * Store a new withdrawal request.
     */
    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        try {
            $this->withdrawalService->requestWithdrawal(
                Auth::user(),
                (int) $request->input('amount'),
                [
                    'provider_name' => $request->input('provider_name'),
                    'account_number' => $request->input('account_number'),
                    'account_holder_name' => $request->input('account_holder_name'),
                ]
            );

            return redirect()
                ->route('responden.withdrawals.index')
                ->with('success', 'Permintaan penarikan berhasil diajukan.');
        } catch (RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
