<?php

namespace App\Services;

use App\Models\RespondentWithdrawal;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RespondentWithdrawalService
{
    /**
     * Get the minimum withdrawal threshold from config.
     */
    public function getMinimumThreshold(): int
    {
        return config('responden.min_withdrawal');
    }

    /**
     * Check if the user can withdraw the given amount.
     * Only reward_balance can be withdrawn.
     */
    public function canWithdraw(User $user, int $amount): bool
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            return false;
        }

        $rewardBalance = (int) $wallet->reward_balance;
        $threshold = $this->getMinimumThreshold();

        return $rewardBalance >= $threshold && $rewardBalance >= $amount;
    }

    /**
     * Request a withdrawal within a DB transaction.
     *
     * @param  array  $accountDetails  Keys: provider_name, account_number, account_holder_name
     *
     * @throws RuntimeException When balance is insufficient.
     */
    public function requestWithdrawal(User $user, int $amount, array $accountDetails): RespondentWithdrawal
    {
        return DB::transaction(function () use ($user, $amount, $accountDetails) {
            // Lock the wallet for update
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet) {
                throw new RuntimeException('Wallet tidak ditemukan.');
            }

            $rewardBalance = (float) $wallet->reward_balance;

            // Validate reward_balance >= amount (only reward can be withdrawn)
            if ($rewardBalance < $amount) {
                throw new RuntimeException('Saldo reward tidak mencukupi.');
            }

            $balanceBefore = (float) $wallet->balance;

            // Debit only from reward_balance
            $wallet->reward_balance = bcsub($wallet->reward_balance, $amount, 2);
            $wallet->syncTotalBalance();

            $balanceAfter = (float) $wallet->balance;
            $wallet->save();

            // Create the withdrawal record with pending status
            $withdrawal = RespondentWithdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'provider_name' => $accountDetails['provider_name'],
                'account_number' => $accountDetails['account_number'],
                'account_holder_name' => $accountDetails['account_holder_name'],
                'status' => RespondentWithdrawal::STATUS_PENDING,
            ]);

            // Create wallet transaction record
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => WalletTransaction::REF_RESPONDENT_WITHDRAWAL,
                'reference_id' => $withdrawal->id,
                'description' => 'Penarikan saldo #' . $withdrawal->id,
            ]);

            return $withdrawal;
        });
    }
}
