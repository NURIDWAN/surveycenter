<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index(WalletService $walletService)
    {
        $user = Auth::user();
        $wallet = $walletService->getOrCreateWallet($user);

        $transactions = $wallet->transactions()
            ->latest()
            ->paginate(15);

        return view('user.wallet.index', compact('wallet', 'transactions'));
    }
}
