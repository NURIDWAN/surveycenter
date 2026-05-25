<?php

namespace App\Observers;

use App\Models\TopupTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\Log;

class TopupTransactionObserver
{
    public function created(TopupTransaction $topupTransaction): void
    {
        if ($topupTransaction->status === TopupTransaction::STATUS_PAID) {
            $this->creditWallet($topupTransaction);
        }
    }

    public function updated(TopupTransaction $topupTransaction): void
    {
        if (
            $topupTransaction->isDirty('status') &&
            $topupTransaction->status === TopupTransaction::STATUS_PAID &&
            $topupTransaction->getOriginal('status') !== TopupTransaction::STATUS_PAID
        ) {
            $this->creditWallet($topupTransaction);
        }
    }

    private function creditWallet(TopupTransaction $topupTransaction): void
    {
        try {
            app(WalletService::class)->creditTopup($topupTransaction);
        } catch (\Throwable $e) {
            Log::error('Failed to credit wallet from top up', [
                'topup_id' => $topupTransaction->id,
                'user_id' => $topupTransaction->user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
