<?php

namespace App\Observers;

use App\Models\PointTransaction;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "updated" event.
     * Award points when a transaction transitions to 'paid'.
     */
    public function updated(Transaction $transaction): void
    {
        if (
            $transaction->isDirty('status') &&
            $transaction->status === Transaction::STATUS_PAID &&
            $transaction->getOriginal('status') !== Transaction::STATUS_PAID
        ) {
            $this->debitWalletIfPaidBySaldo($transaction);
            $this->awardPoints($transaction);
            $this->awardReferralCommission($transaction);
        }
    }

    private function debitWalletIfPaidBySaldo(Transaction $transaction): void
    {
        if ($transaction->payment_method !== 'saldo') {
            return;
        }

        try {
            app(WalletService::class)->debitPaidSaldoTransaction($transaction);
        } catch (\Throwable $e) {
            Log::error('Failed to debit wallet from transaction', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Award points for a paid transaction.
     * Rp 1.000 = 1 point. Idempotent — won't double-award.
     */
    private function awardPoints(Transaction $transaction): void
    {
        $alreadyAwarded = PointTransaction::where('transaction_id', $transaction->id)
            ->where('type', PointTransaction::TYPE_EARN)
            ->where('user_id', $transaction->user_id)
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $points = PointTransaction::calculatePoints($transaction->amount);

        if ($points <= 0) {
            return;
        }

        PointTransaction::create([
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
            'type' => PointTransaction::TYPE_EARN,
            'points' => $points,
            'description' => 'Poin dari transaksi #' . $transaction->id . ' (Rp ' . number_format($transaction->amount, 0, ',', '.') . ')',
        ]);
    }

    /**
     * Award referral commission (Rupiah) to the referrer.
     * Only triggers if the paying user was referred by someone.
     * Idempotent via unique constraint on referral_commissions.transaction_id.
     */
    private function awardReferralCommission(Transaction $transaction): void
    {
        // Already awarded for this transaction?
        if (ReferralCommission::where('transaction_id', $transaction->id)->exists()) {
            return;
        }

        $buyer = User::find($transaction->user_id);
        if (!$buyer || !$buyer->referred_by_id) {
            return;
        }

        $referrer = User::find($buyer->referred_by_id);
        if (!$referrer) {
            return;
        }

        $percent = ReferralCommission::getCommissionPercent();
        if ($percent <= 0) {
            return;
        }

        $commissionAmount = ReferralCommission::calculateCommission($transaction->amount);
        if ($commissionAmount <= 0) {
            return;
        }

        // Record the commission (Rupiah-based, no point transaction)
        ReferralCommission::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $buyer->id,
            'transaction_id' => $transaction->id,
            'commission_amount' => $commissionAmount,
            'commission_percent' => $percent,
        ]);
    }
}
