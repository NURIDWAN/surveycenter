<?php

namespace Tests\Feature;

use App\Models\Survey;
use App\Models\TopupTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_topup_credits_wallet_once(): void
    {
        $user = User::factory()->create();

        $topup = TopupTransaction::create([
            'user_id' => $user->id,
            'amount' => 100000,
            'status' => TopupTransaction::STATUS_PENDING,
            'payment_method' => 'qris',
        ]);

        $topup->update(['status' => TopupTransaction::STATUS_PAID]);
        $topup->update(['status' => TopupTransaction::STATUS_PAID]);

        $this->assertSame(100000, $user->fresh()->deposit_balance);
        $this->assertDatabaseCount('wallet_transactions', 1);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => WalletTransaction::TYPE_CREDIT,
            'reference_type' => WalletTransaction::REF_TOPUP,
            'reference_id' => $topup->id,
        ]);
    }

    public function test_wallet_payment_debits_balance_and_marks_transaction_paid(): void
    {
        $user = User::factory()->create();

        TopupTransaction::create([
            'user_id' => $user->id,
            'amount' => 150000,
            'status' => TopupTransaction::STATUS_PAID,
            'payment_method' => 'qris',
        ]);

        $survey = Survey::create([
            'title' => 'Survey Wallet',
            'question_count' => 10,
            'respondent_count' => 100,
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'amount' => 50000,
            'status' => Transaction::STATUS_PENDING,
        ]);

        app(WalletService::class)->payTransactionWithWallet($transaction, $user);

        $transaction->refresh();

        $this->assertSame(Transaction::STATUS_PAID, $transaction->status);
        $this->assertSame('saldo', $transaction->payment_method);
        $this->assertSame(100000, $user->fresh()->deposit_balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => WalletTransaction::TYPE_DEBIT,
            'reference_type' => WalletTransaction::REF_TRANSACTION,
            'reference_id' => $transaction->id,
        ]);
    }
}
