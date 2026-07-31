# Design Document: Separate Deposit & Reward Balance

## Overview

This design separates the respondent wallet into two distinct balance types — **deposit_balance** (from topups) and **reward_balance** (from survey rewards) — while maintaining backward compatibility with the existing `balance` column as the total. The architecture modifies the Wallet model, service layer, and UI components to support split-balance tracking, display, and debit ordering (reward-first withdrawal).

## Architecture

### High-Level Flow

```
┌─────────────────────────────────────────────────────────────┐
│                     Wallet Table                              │
│  deposit_balance | reward_balance | balance (computed total)  │
└────────────┬──────────────┬──────────────────┬──────────────┘
             │              │                  │
     ┌───────┘              │                  └───────┐
     ▼                      ▼                          ▼
 TopupCredit          SurveyRewardCredit         WithdrawalDebit
 (WalletService)     (SurveyFillingService)   (RespondentWithdrawalService)
     │                      │                          │
     ▼                      ▼                          ▼
 deposit_balance++    reward_balance++        reward_balance-- first
                                              then deposit_balance--
```

### Design Principles

1. **Single Source of Truth**: `balance` = `deposit_balance` + `reward_balance` always (enforced at write-time)
2. **Backward Compatibility**: The `balance` column remains, updated alongside split balances
3. **Reward-First Debit**: Withdrawals deduct from `reward_balance` first, then `deposit_balance`
4. **Atomic Operations**: All balance mutations within DB transactions with row-level locking

## Components and Interfaces

### 1. Database Migration

A new migration adds `deposit_balance` and `reward_balance` columns and backfills from transaction history.

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_split_balance_to_wallets_table.php

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
```

### 2. Wallet Model

```php
// app/Models/Wallet.php

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'deposit_balance',
        'reward_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'deposit_balance' => 'decimal:2',
            'reward_balance' => 'decimal:2',
        ];
    }

    /**
     * Recalculate and sync the total balance from split balances.
     */
    public function syncTotalBalance(): void
    {
        $this->balance = bcadd($this->deposit_balance, $this->reward_balance, 2);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
```

### 3. WalletService Changes

The `creditTopup` method now increments `deposit_balance` and syncs `balance`:

```php
// In WalletService::creditTopup()

$wallet = $this->lockedWalletForUser((int) $topup->user_id);
$before = (float) $wallet->balance;
$amount = (float) $topup->amount;

// Credit deposit_balance
$wallet->deposit_balance = bcadd($wallet->deposit_balance, $amount, 2);
$wallet->syncTotalBalance();
$after = (float) $wallet->balance;

$wallet->save();
```

The `debitPaidSaldoTransaction` and `payTransactionWithWallet` methods debit from `deposit_balance` (since these are survey payment debits using deposited funds):

```php
// In WalletService::debitPaidSaldoTransaction() / payTransactionWithWallet()

$wallet = $this->lockedWalletForUser((int) $transaction->user_id);
$before = (float) $wallet->balance;
$amount = (float) $transaction->amount;

if ($before < $amount) {
    throw new RuntimeException('Saldo tidak mencukupi.');
}

// Debit from deposit_balance (survey payment uses deposited funds)
$wallet->deposit_balance = bcsub($wallet->deposit_balance, $amount, 2);
$wallet->syncTotalBalance();
$after = (float) $wallet->balance;

$wallet->save();
```

### 4. SurveyFillingService Changes

The `approve` method now credits `reward_balance`:

```php
// In SurveyFillingService::approve()

$wallet = Wallet::where('user_id', $filling->user_id)->lockForUpdate()->first();
// ... (create wallet if not exists)

$rewardAmount = (float) $survey->reward_amount;
$balanceBefore = (float) $wallet->balance;

// Credit reward_balance
$wallet->reward_balance = bcadd($wallet->reward_balance, $rewardAmount, 2);
$wallet->syncTotalBalance();
$balanceAfter = (float) $wallet->balance;

$wallet->save();
```

### 5. RespondentWithdrawalService Changes

The `requestWithdrawal` method implements reward-first debit ordering:

```php
// In RespondentWithdrawalService::requestWithdrawal()

$wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

$totalBalance = (float) $wallet->balance;

if ($totalBalance < $amount) {
    throw new RuntimeException('Saldo tidak mencukupi.');
}

$balanceBefore = $totalBalance;

// Debit reward_balance first, then deposit_balance for remainder
$rewardDebit = min((float) $wallet->reward_balance, (float) $amount);
$depositDebit = (float) $amount - $rewardDebit;

$wallet->reward_balance = bcsub($wallet->reward_balance, $rewardDebit, 2);
$wallet->deposit_balance = bcsub($wallet->deposit_balance, $depositDebit, 2);
$wallet->syncTotalBalance();

$balanceAfter = (float) $wallet->balance;
$wallet->save();
```

The `canWithdraw` method validates against total balance:

```php
public function canWithdraw(User $user, int $amount): bool
{
    $wallet = $user->wallet;

    if (!$wallet) {
        return false;
    }

    $balance = (float) $wallet->balance; // total balance
    $threshold = $this->getMinimumThreshold();

    return $balance >= $threshold && $balance >= $amount;
}
```

### 6. Controller Changes

#### DashboardController

```php
public function index(): View
{
    $user = Auth::user();
    $wallet = $user->wallet;

    $depositBalance = (int) ($wallet?->deposit_balance ?? 0);
    $rewardBalance = (int) ($wallet?->reward_balance ?? 0);
    $saldo = (int) ($wallet?->balance ?? 0);

    // ... rest unchanged

    return view('responden.dashboard.index', compact(
        'saldo',
        'depositBalance',
        'rewardBalance',
        'surveyTersediaCount',
        'menungguCount',
        'disetujuiCount',
        'availableSurveys',
        'profileComplete'
    ));
}
```

#### WithdrawalController

```php
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
```

### 7. Layout/View Changes

#### Sidebar Saldo Widget (layouts/responden.blade.php)

The sidebar saldo widget will display split balances when expanded:

```blade
{{-- Saldo Widget --}}
@auth
<div class="mx-3 mt-auto mb-2 p-4 rounded-xl bg-gray-50/80 border border-gray-100 relative overflow-hidden group">
    <div class="absolute top-0 right-0 w-16 h-16 bg-orange-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-md"></div>

    <div x-show="sidebarOpen" x-transition>
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Saldo Anda</p>

        <div class="space-y-1.5 mb-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-gray-500">Saldo Deposit</span>
                <span class="text-xs font-bold text-blue-600">
                    {{ \App\Helpers\RupiahHelper::formatRupiah($depositBalance ?? 0) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-gray-500">Saldo Reward</span>
                <span class="text-xs font-bold text-emerald-600">
                    {{ \App\Helpers\RupiahHelper::formatRupiah($rewardBalance ?? 0) }}
                </span>
            </div>
            <div class="border-t border-gray-200 pt-1.5 flex items-center justify-between">
                <span class="text-[11px] font-semibold text-gray-700">Total Saldo</span>
                <span class="text-sm font-extrabold text-gray-900">
                    {{ \App\Helpers\RupiahHelper::formatRupiah($saldo ?? 0) }}
                </span>
            </div>
        </div>

        <a href="{{ route('responden.withdrawals.create') }}" class="flex items-center justify-center gap-1.5 w-full py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-md shadow-orange-500/10 transition-all hover:scale-[1.02]">
            <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
            Tarik Saldo
        </a>
    </div>

    <div x-show="!sidebarOpen" class="flex flex-col items-center justify-center relative cursor-pointer" @click="sidebarOpen = true" x-transition>
        <i data-lucide="wallet" class="w-5 h-5 text-gray-500 group-hover:text-orange-500 transition"></i>
    </div>
</div>
@endauth
```

#### Top Bar Saldo (layouts/responden.blade.php)

Add hover tooltip showing breakdown:

```blade
{{-- Saldo in Top Bar --}}
<div class="flex items-center gap-2 mr-3">
    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 relative group/saldo cursor-default">
        <i data-lucide="wallet" class="w-4 h-4 text-orange-500"></i>
        <span class="text-sm font-semibold text-orange-700">
            {{ \App\Helpers\RupiahHelper::formatRupiah($saldo ?? (auth()->user()->wallet->balance ?? 0)) }}
        </span>

        {{-- Tooltip dropdown --}}
        <div class="absolute top-full right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-200 p-3 z-50 opacity-0 invisible group-hover/saldo:opacity-100 group-hover/saldo:visible transition-all duration-200">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Saldo Deposit</span>
                    <span class="text-xs font-bold text-blue-600">
                        {{ \App\Helpers\RupiahHelper::formatRupiah($depositBalance ?? 0) }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Saldo Reward</span>
                    <span class="text-xs font-bold text-emerald-600">
                        {{ \App\Helpers\RupiahHelper::formatRupiah($rewardBalance ?? 0) }}
                    </span>
                </div>
                <div class="border-t border-gray-100 pt-2 flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-700">Total</span>
                    <span class="text-xs font-bold text-gray-900">
                        {{ \App\Helpers\RupiahHelper::formatRupiah($saldo ?? 0) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### Dashboard Saldo Card

Replace the single saldo card with a multi-balance card:

```blade
{{-- Saldo Card (replaces existing single card) --}}
<div class="relative overflow-hidden bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow sm:col-span-2">
    <div class="absolute top-0 right-0 w-20 h-20 bg-orange-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-lg"></div>
    <div class="relative">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-orange-500"></i>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Saldo</p>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-blue-500 mb-1">Deposit</p>
                <p class="text-base font-extrabold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($depositBalance) }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500 mb-1">Reward</p>
                <p class="text-base font-extrabold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($rewardBalance) }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">Total</p>
                <p class="text-xl font-extrabold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($saldo) }}</p>
            </div>
        </div>
    </div>
</div>
```

#### Withdrawal Form Saldo Card

Update to display all three balance types:

```blade
{{-- Saldo Info Card in withdrawal form --}}
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center flex-shrink-0">
            <i data-lucide="wallet" class="w-5 h-5 text-orange-500"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Saldo Anda</h3>

            <div class="space-y-2 mb-3">
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-blue-50 border border-blue-100">
                    <span class="text-xs text-blue-700 font-medium">Saldo Deposit</span>
                    <span class="text-sm font-bold text-blue-700">{{ \App\Helpers\RupiahHelper::formatRupiah($depositBalance) }}</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-100">
                    <span class="text-xs text-emerald-700 font-medium">Saldo Reward</span>
                    <span class="text-sm font-bold text-emerald-700">{{ \App\Helpers\RupiahHelper::formatRupiah($rewardBalance) }}</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="text-xs font-semibold text-gray-700">Total Saldo Tersedia</span>
                    <span class="text-lg font-extrabold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($saldo) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-100">
                <i data-lucide="info" class="w-4 h-4 text-amber-500 flex-shrink-0"></i>
                <p class="text-xs text-amber-700 font-medium">Minimum penarikan: <span class="font-bold">{{ \App\Helpers\RupiahHelper::formatRupiah($minThreshold) }}</span></p>
            </div>
        </div>
    </div>
</div>
```

### 8. View Composer for Shared Saldo Data

To ensure `$depositBalance`, `$rewardBalance`, and `$saldo` are available to the layout without passing from every controller:

```php
// app/Providers/AppServiceProvider.php (in boot method)

use Illuminate\Support\Facades\View;

View::composer('layouts.responden', function ($view) {
    $user = auth()->user();
    if ($user) {
        $wallet = $user->wallet;
        $view->with([
            'saldo' => (int) ($wallet?->balance ?? 0),
            'depositBalance' => (int) ($wallet?->deposit_balance ?? 0),
            'rewardBalance' => (int) ($wallet?->reward_balance ?? 0),
        ]);
    }
});
```

## Data Models

### Wallet Table (updated)

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| id | bigint | auto | Primary key |
| user_id | bigint | — | FK to users (unique) |
| balance | decimal(15,2) | 0 | Total balance (deposit + reward) |
| deposit_balance | decimal(15,2) | 0 | Balance from topups |
| reward_balance | decimal(15,2) | 0 | Balance from survey rewards |
| created_at | timestamp | — | — |
| updated_at | timestamp | — | — |

**Invariant**: `balance = deposit_balance + reward_balance` (enforced at application layer)

### WalletTransaction Table (unchanged)

No schema changes needed. The existing `reference_type` field already distinguishes between `topup`, `survey_filling`, `respondent_withdrawal`, and `transaction`.

### Interfaces

#### Wallet Model

```php
class Wallet extends Model
{
    public function syncTotalBalance(): void;
    public function user(): BelongsTo;
    public function transactions(): HasMany;
}
```

### WalletService

```php
class WalletService
{
    public function getOrCreateWallet(User $user): Wallet;
    public function creditTopup(TopupTransaction $topup): ?WalletTransaction;
    public function debitPaidSaldoTransaction(Transaction $transaction): ?WalletTransaction;
    public function payTransactionWithWallet(Transaction $transaction, User $user): void;
}
```

### SurveyFillingService

```php
class SurveyFillingService
{
    public function approve(SurveyFilling $filling): SurveyFilling;
    // Credits reward_balance instead of balance
}
```

### RespondentWithdrawalService

```php
class RespondentWithdrawalService
{
    public function getMinimumThreshold(): int;
    public function canWithdraw(User $user, int $amount): bool;
    public function requestWithdrawal(User $user, int $amount, array $accountDetails): RespondentWithdrawal;
    // Implements reward-first debit ordering
}
```

## Error Handling

| Scenario | Behavior |
|----------|----------|
| Withdrawal amount > total balance | RuntimeException: "Saldo tidak mencukupi." |
| Total balance < minimum threshold | `canWithdraw()` returns false; form shows threshold info |
| Wallet not found during debit | RuntimeException: "Wallet tidak ditemukan." |
| Negative balance after operation | `max(0, ...)` guard + DB transaction rollback |
| Concurrent balance modification | Row-level lock via `lockForUpdate()` within DB transaction |

## Testing Strategy

### Property-Based Tests (PHPUnit + custom generators)

Property-based tests validate the core wallet logic with randomized inputs:

- **Balance invariant**: Random sequences of credits/debits verify `balance == deposit_balance + reward_balance` after each operation
- **Topup targeting**: Random topup amounts verify only `deposit_balance` changes
- **Reward targeting**: Random reward amounts verify only `reward_balance` changes
- **Withdrawal ordering**: Random wallet states and withdrawal amounts verify reward-first debit logic
- **Rejection conditions**: Random amounts exceeding balance verify proper error handling

### Unit Tests (Example-Based)

- Migration backfill logic with known transaction histories
- View rendering with known wallet data (sidebar, top bar, dashboard, withdrawal form)
- Edge cases: zero balances, exact-balance withdrawals, wallet creation on first credit

### Integration Tests

- Full topup→credit→withdrawal flow through service layer with DB transactions
- Concurrent withdrawal attempts (lock verification)

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Balance Invariant

*For any* wallet and *for any* sequence of credit/debit operations (topup, reward, withdrawal, transaction payment), after every operation completes, `balance` SHALL equal `deposit_balance + reward_balance`.

**Validates: Requirements 1.3, 2.2, 3.2, 4.2**

### Property 2: Topup Credits Deposit Balance Only

*For any* valid topup amount, when the topup is confirmed (status paid), the wallet's `deposit_balance` SHALL increase by exactly the topup amount, and `reward_balance` SHALL remain unchanged.

**Validates: Requirements 2.1**

### Property 3: Survey Reward Credits Reward Balance Only

*For any* valid survey reward amount, when the survey filling is approved, the wallet's `reward_balance` SHALL increase by exactly the reward amount, and `deposit_balance` SHALL remain unchanged.

**Validates: Requirements 3.1**

### Property 4: Withdrawal Debits Reward First Then Deposit

*For any* wallet state with `deposit_balance = D` and `reward_balance = R`, and *for any* valid withdrawal amount `A` where `A <= D + R`: if `A <= R` then `reward_balance` decreases by `A` and `deposit_balance` is unchanged; otherwise `reward_balance` becomes 0 and `deposit_balance` decreases by `A - R`.

**Validates: Requirements 4.1**

### Property 5: Withdrawal Rejection on Insufficient Balance

*For any* wallet state where total balance (`deposit_balance + reward_balance`) is less than the requested withdrawal amount, the system SHALL reject the withdrawal with a RuntimeException.

**Validates: Requirements 4.3, 4.4**

### Property 6: Withdrawal Validates Against Total Balance

*For any* wallet state and *for any* withdrawal amount `A` where `A <= deposit_balance + reward_balance` and `A >= minimum_threshold`, the withdrawal SHALL succeed regardless of whether `A` exceeds either individual balance type alone.

**Validates: Requirements 8.3**

### Property 7: Transaction Recording Correctness

*For any* credit operation (topup or survey reward), a WalletTransaction record SHALL be created with the correct `reference_type` (topup or survey_filling), correct `amount`, and `balance_after = balance_before + amount`.

**Validates: Requirements 2.3, 3.3**
