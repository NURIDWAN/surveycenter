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
     * Balance must be >= threshold AND >= amount.
     */
    public function canWithdraw(User $user, int $amount): bool
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            return false;
        }

        $balance = (int) $wallet->balance;
        $threshold = $this->getMinimumThreshold();

        return $balance >= $threshold && $balance >= $amount;
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

            $balance = (float) $wallet->balance;

            if ($balance < $amount) {
                throw new RuntimeException('Saldo tidak mencukupi.');
            }

            // Create the withdrawal record with pending status
            $withdrawal = RespondentWithdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'provider_name' => $accountDetails['provider_name'],
                'account_number' => $accountDetails['account_number'],
                'account_holder_name' => $accountDetails['account_holder_name'],
                'status' => RespondentWithdrawal::STATUS_PENDING,
            ]);

            // Debit the wallet
            $balanceBefore = $balance;
            $balanceAfter = $balance - $amount;
            $wallet->update(['balance' => $balanceAfter]);

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
