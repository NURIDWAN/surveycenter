<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->unique(['reference_type', 'reference_id', 'type'], 'wallet_transactions_reference_unique');
        });

        $this->backfillWallets();
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }

    private function backfillWallets(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $now = now();

        DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($now) {
                foreach ($users as $user) {
                    $walletId = DB::table('wallets')->insertGetId([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $balance = 0.0;

                    if (Schema::hasTable('topup_transactions')) {
                        $topups = DB::table('topup_transactions')
                            ->where('user_id', $user->id)
                            ->where('status', 'paid')
                            ->orderBy('id')
                            ->get(['id', 'amount']);

                        foreach ($topups as $topup) {
                            $before = $balance;
                            $balance += (float) $topup->amount;

                            DB::table('wallet_transactions')->insert([
                                'wallet_id' => $walletId,
                                'user_id' => $user->id,
                                'type' => 'credit',
                                'amount' => $topup->amount,
                                'balance_before' => $before,
                                'balance_after' => $balance,
                                'reference_type' => 'topup',
                                'reference_id' => $topup->id,
                                'description' => 'Backfill top up #' . $topup->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }

                    if (Schema::hasTable('transactions')) {
                        $transactions = DB::table('transactions')
                            ->where('user_id', $user->id)
                            ->where('status', 'paid')
                            ->where('payment_method', 'saldo')
                            ->orderBy('id')
                            ->get(['id', 'amount']);

                        foreach ($transactions as $transaction) {
                            $before = $balance;
                            $balance -= (float) $transaction->amount;

                            DB::table('wallet_transactions')->insert([
                                'wallet_id' => $walletId,
                                'user_id' => $user->id,
                                'type' => 'debit',
                                'amount' => $transaction->amount,
                                'balance_before' => $before,
                                'balance_after' => max(0, $balance),
                                'reference_type' => 'transaction',
                                'reference_id' => $transaction->id,
                                'description' => 'Backfill pembayaran survey #' . $transaction->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }

                    $balance = max(0, $balance);

                    DB::table('wallets')
                        ->where('id', $walletId)
                        ->update([
                            'balance' => $balance,
                            'updated_at' => $now,
                        ]);
                }
            });
    }
};
