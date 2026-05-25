<?php

namespace App\Services;

use App\Models\TopupTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function getOrCreateWallet(User $user): Wallet
    {
        $wallet = $user->wallet;

        if ($wallet) {
            return $wallet;
        }

        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
    }

    public function creditTopup(TopupTransaction $topup): ?WalletTransaction
    {
        if ($topup->status !== TopupTransaction::STATUS_PAID || !$topup->user_id) {
            return null;
        }

        return DB::transaction(function () use ($topup) {
            $existing = WalletTransaction::where('type', WalletTransaction::TYPE_CREDIT)
                ->where('reference_type', WalletTransaction::REF_TOPUP)
                ->where('reference_id', $topup->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $wallet = $this->lockedWalletForUser((int) $topup->user_id);
            $before = (float) $wallet->balance;
            $amount = (float) $topup->amount;
            $after = $before + $amount;

            $wallet->update(['balance' => $after]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $topup->user_id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => WalletTransaction::REF_TOPUP,
                'reference_id' => $topup->id,
                'description' => 'Top up saldo #' . $topup->id,
                'meta' => [
                    'payment_method' => $topup->payment_method,
                    'payment_ref' => $topup->payment_ref,
                    'bill_no' => $topup->bill_no,
                ],
            ]);
        });
    }

    public function debitPaidSaldoTransaction(Transaction $transaction): ?WalletTransaction
    {
        if (
            $transaction->status !== Transaction::STATUS_PAID ||
            $transaction->payment_method !== 'saldo' ||
            !$transaction->user_id
        ) {
            return null;
        }

        return DB::transaction(function () use ($transaction) {
            $existing = WalletTransaction::where('type', WalletTransaction::TYPE_DEBIT)
                ->where('reference_type', WalletTransaction::REF_TRANSACTION)
                ->where('reference_id', $transaction->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $wallet = $this->lockedWalletForUser((int) $transaction->user_id);
            $before = (float) $wallet->balance;
            $amount = (float) $transaction->amount;

            if ($before < $amount) {
                throw new RuntimeException('Saldo tidak mencukupi.');
            }

            $after = $before - $amount;
            $wallet->update(['balance' => $after]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $transaction->user_id,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => WalletTransaction::REF_TRANSACTION,
                'reference_id' => $transaction->id,
                'description' => 'Pembayaran survey #' . $transaction->id,
                'meta' => [
                    'survey_id' => $transaction->survey_id,
                ],
            ]);
        });
    }

    public function payTransactionWithWallet(Transaction $transaction, User $user): void
    {
        DB::transaction(function () use ($transaction, $user) {
            $lockedTransaction = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ((int) $lockedTransaction->user_id !== (int) $user->id) {
                throw new RuntimeException('Transaksi tidak valid.');
            }

            if ($lockedTransaction->status === Transaction::STATUS_PAID) {
                return;
            }

            $wallet = $this->lockedWalletForUser((int) $user->id);
            $before = (float) $wallet->balance;
            $amount = (float) $lockedTransaction->amount;

            if ($before < $amount) {
                throw new RuntimeException('Saldo tidak mencukupi.');
            }

            $after = $before - $amount;
            $wallet->update(['balance' => $after]);

            WalletTransaction::firstOrCreate(
                [
                    'type' => WalletTransaction::TYPE_DEBIT,
                    'reference_type' => WalletTransaction::REF_TRANSACTION,
                    'reference_id' => $lockedTransaction->id,
                ],
                [
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'description' => 'Pembayaran survey #' . $lockedTransaction->id,
                    'meta' => [
                        'survey_id' => $lockedTransaction->survey_id,
                    ],
                ]
            );

            $lockedTransaction->update([
                'payment_method' => 'saldo',
                'status' => Transaction::STATUS_PAID,
            ]);
        });
    }

    private function lockedWalletForUser(int $userId): Wallet
    {
        $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

        if ($wallet) {
            return $wallet;
        }

        Wallet::create([
            'user_id' => $userId,
            'balance' => 0,
        ]);

        return Wallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();
    }
}
