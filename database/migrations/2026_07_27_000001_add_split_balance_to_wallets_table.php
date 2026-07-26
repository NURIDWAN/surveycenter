<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('deposit_balance', 15, 2)->default(0)->after('balance');
            $table->decimal('reward_balance', 15, 2)->default(0)->after('deposit_balance');
        });

        $this->backfillSplitBalances();
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['deposit_balance', 'reward_balance']);
        });
    }

    private function backfillSplitBalances(): void
    {
        DB::table('wallets')->orderBy('id')->chunkById(100, function ($wallets) {
            foreach ($wallets as $wallet) {
                // Sum all topup credits
                $totalTopupCredits = (float) DB::table('wallet_transactions')
                    ->where('wallet_id', $wallet->id)
                    ->where('type', 'credit')
                    ->where('reference_type', 'topup')
                    ->sum('amount');

                // Sum all survey_filling credits
                $totalRewardCredits = (float) DB::table('wallet_transactions')
                    ->where('wallet_id', $wallet->id)
                    ->where('type', 'credit')
                    ->where('reference_type', 'survey_filling')
                    ->sum('amount');

                // Sum all debits
                $totalDebits = (float) DB::table('wallet_transactions')
                    ->where('wallet_id', $wallet->id)
                    ->where('type', 'debit')
                    ->sum('amount');

                // Allocate debits: deduct from reward first, then deposit
                $rewardBalance = $totalRewardCredits;
                $depositBalance = $totalTopupCredits;

                if ($totalDebits <= $rewardBalance) {
                    $rewardBalance -= $totalDebits;
                } else {
                    $remainingDebit = $totalDebits - $rewardBalance;
                    $rewardBalance = 0;
                    $depositBalance = max(0, $depositBalance - $remainingDebit);
                }

                DB::table('wallets')->where('id', $wallet->id)->update([
                    'deposit_balance' => $depositBalance,
                    'reward_balance' => $rewardBalance,
                ]);
            }
        });
    }
};
