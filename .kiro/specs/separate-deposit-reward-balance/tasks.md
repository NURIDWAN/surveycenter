# Implementation Plan: Separate Deposit & Reward Balance

## Overview

Memisahkan saldo responden menjadi `deposit_balance` dan `reward_balance` pada tabel wallets, meng-update service layer agar kredit/debit menargetkan kolom yang tepat, lalu memperbarui UI (sidebar, top bar, dashboard, withdrawal form) untuk menampilkan breakdown saldo.

## Tasks

- [x] 1. Database migration dan model update
  - [x] 1.1 Buat migration untuk menambah kolom `deposit_balance` dan `reward_balance` ke tabel `wallets`
    - Buat file migration baru: `database/migrations/YYYY_MM_DD_HHMMSS_add_split_balance_to_wallets_table.php`
    - Tambah kolom `deposit_balance` decimal(15,2) default 0 setelah kolom `balance`
    - Tambah kolom `reward_balance` decimal(15,2) default 0 setelah kolom `deposit_balance`
    - Implementasi method `backfillSplitBalances()` yang menghitung deposit/reward dari riwayat WalletTransaction
    - Backfill: sum credit topup → deposit_balance, sum credit survey_filling → reward_balance, debits dikurangi reward-first
    - Implementasi `down()` yang drop kedua kolom baru
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 1.2 Update Wallet model dengan fillable, casts, dan method `syncTotalBalance`
    - Tambah `deposit_balance` dan `reward_balance` ke `$fillable` array
    - Tambah cast `deposit_balance` dan `reward_balance` sebagai `decimal:2`
    - Implementasi method `syncTotalBalance()` yang set `$this->balance = bcadd($this->deposit_balance, $this->reward_balance, 2)`
    - _Requirements: 1.3_

- [x] 2. Service layer updates
  - [x] 2.1 Update `WalletService::creditTopup` untuk menargetkan `deposit_balance`
    - Dalam method `creditTopup()`, setelah mendapatkan locked wallet, credit ke `deposit_balance` menggunakan `bcadd`
    - Panggil `$wallet->syncTotalBalance()` sebelum save
    - Update `$wallet->save()` (ganti `$wallet->update(['balance' => $after])`)
    - Pastikan `balance_before` dan `balance_after` pada WalletTransaction tetap menggunakan total balance
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 2.2 Update `WalletService::debitPaidSaldoTransaction` dan `payTransactionWithWallet` untuk debit dari `deposit_balance`
    - Dalam `debitPaidSaldoTransaction()`, debit dari `deposit_balance` (pembayaran survey menggunakan dana deposit)
    - Panggil `$wallet->syncTotalBalance()` sebelum save
    - Dalam `payTransactionWithWallet()`, debit dari `deposit_balance`
    - Panggil `$wallet->syncTotalBalance()` sebelum save
    - Validasi `deposit_balance >= amount` sebelum debit
    - _Requirements: 1.3_

  - [x] 2.3 Update `SurveyFillingService::approve` untuk credit ke `reward_balance`
    - Dalam method `approve()`, credit ke `reward_balance` menggunakan `bcadd`
    - Panggil `$wallet->syncTotalBalance()` sebelum save
    - Ganti `$wallet->update(['balance' => $balanceAfter])` dengan `$wallet->save()`
    - Pastikan WalletTransaction `balance_before`/`balance_after` menggunakan total balance
    - _Requirements: 3.1, 3.2, 3.3_

  - [x] 2.4 Update `RespondentWithdrawalService::requestWithdrawal` dengan reward-first debit ordering
    - Hitung `$rewardDebit = min($wallet->reward_balance, $amount)`
    - Hitung `$depositDebit = $amount - $rewardDebit`
    - Kurangi `reward_balance` dengan `bcsub` sebesar `$rewardDebit`
    - Kurangi `deposit_balance` dengan `bcsub` sebesar `$depositDebit`
    - Panggil `$wallet->syncTotalBalance()` sebelum save
    - Validasi total balance >= amount sebelum debit
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

  - [x] 2.5 Update `WalletService::getOrCreateWallet` untuk inisialisasi kolom baru
    - Tambah `deposit_balance => 0` dan `reward_balance => 0` pada `Wallet::firstOrCreate`
    - Tambah default values pada `lockedWalletForUser` saat create wallet baru
    - _Requirements: 1.1, 1.2_

- [x] 3. Checkpoint - Pastikan service layer bekerja dengan benar
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Controller dan View Composer updates
  - [x] 4.1 Tambah View Composer untuk layout responden di `AppServiceProvider`
    - Dalam method `boot()` di `AppServiceProvider`, register View Composer untuk `layouts.responden`
    - Pass `$saldo`, `$depositBalance`, `$rewardBalance` ke view layout
    - Handle null wallet (default 0)
    - _Requirements: 5.1, 5.2, 5.3, 6.1, 6.2_

  - [x] 4.2 Update `DashboardController::index` untuk pass split balances
    - Tambah variabel `$depositBalance` dan `$rewardBalance` dari wallet
    - Pass ke view via `compact()`
    - Handle null wallet (default 0)
    - _Requirements: 7.1, 7.2_

  - [x] 4.3 Update `WithdrawalController::create` untuk pass split balances
    - Tambah variabel `$depositBalance` dan `$rewardBalance` dari wallet
    - Pass ke view via `compact()`
    - _Requirements: 8.1, 8.2_

- [x] 5. View/UI updates
  - [x] 5.1 Update sidebar saldo widget di `layouts/responden.blade.php`
    - Tampilkan "Saldo Deposit" dengan warna blue-600 dan "Saldo Reward" dengan warna emerald-600
    - Tampilkan "Total Saldo" dengan separator border-top
    - Gunakan `RupiahHelper::formatRupiah()` untuk format
    - Saat sidebar collapsed, tampilkan ikon wallet yang bisa diklik
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [x] 5.2 Update top bar saldo di `layouts/responden.blade.php`
    - Tampilkan total saldo di area top bar untuk desktop (hidden sm:flex)
    - Tambah tooltip/dropdown on hover yang menunjukkan breakdown deposit/reward/total
    - Gunakan CSS `group-hover` untuk visibility tooltip
    - _Requirements: 6.1, 6.2_

  - [x] 5.3 Update dashboard saldo card di `responden/dashboard/index.blade.php`
    - Ganti single saldo card dengan multi-column card (grid-cols-3)
    - Tampilkan Deposit (blue), Reward (emerald), Total (gray) dengan label berbeda
    - Card span 2 kolom pada grid layout (sm:col-span-2)
    - _Requirements: 7.1, 7.2_

  - [x] 5.4 Update withdrawal form saldo info di `responden/withdrawals/create.blade.php`
    - Tampilkan card dengan breakdown: Saldo Deposit (bg-blue-50), Saldo Reward (bg-emerald-50), Total (bg-gray-50)
    - Tampilkan info minimum threshold penarikan dengan ikon info
    - Validasi jumlah penarikan berdasarkan total saldo
    - _Requirements: 8.1, 8.2, 8.3_

- [x] 6. Checkpoint - Pastikan UI menampilkan saldo dengan benar
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Testing
  - [ ]* 7.1 Write property test: Balance Invariant
    - **Property 1: Balance Invariant** — Untuk setiap sequence kredit/debit, `balance == deposit_balance + reward_balance` setelah setiap operasi
    - Buat test class `tests/Feature/WalletBalanceInvariantTest.php`
    - Generate random sequences of topup credits, survey reward credits, dan withdrawals
    - Assert invariant setelah setiap operasi
    - **Validates: Requirements 1.3, 2.2, 3.2, 4.2**

  - [ ]* 7.2 Write property test: Topup Credits Deposit Balance Only
    - **Property 2: Topup Credits Deposit Balance Only** — Topup hanya mengubah `deposit_balance`, `reward_balance` tetap
    - Buat test dalam `tests/Feature/WalletCreditTargetingTest.php`
    - Generate random topup amounts, verify deposit_balance naik dan reward_balance tidak berubah
    - **Validates: Requirements 2.1**

  - [ ]* 7.3 Write property test: Survey Reward Credits Reward Balance Only
    - **Property 3: Survey Reward Credits Reward Balance Only** — Survey reward hanya mengubah `reward_balance`, `deposit_balance` tetap
    - Generate random reward amounts, verify reward_balance naik dan deposit_balance tidak berubah
    - **Validates: Requirements 3.1**

  - [ ]* 7.4 Write property test: Withdrawal Debits Reward First Then Deposit
    - **Property 4: Withdrawal Debits Reward First Then Deposit** — Withdrawal mengurangi reward_balance terlebih dahulu, sisanya dari deposit_balance
    - Generate random wallet states (deposit, reward) dan random valid withdrawal amounts
    - Assert: jika amount <= reward_balance maka hanya reward berkurang; otherwise reward = 0 dan deposit berkurang sisanya
    - **Validates: Requirements 4.1**

  - [ ]* 7.5 Write property test: Withdrawal Rejection on Insufficient Balance
    - **Property 5: Withdrawal Rejection on Insufficient Balance** — Withdrawal ditolak jika amount > total balance
    - Generate random wallet states dan amounts yang melebihi total balance
    - Assert RuntimeException dilempar
    - **Validates: Requirements 4.3, 4.4**

  - [ ]* 7.6 Write unit tests for migration backfill logic
    - Test backfill dengan known transaction history: wallet dengan topup credits, survey credits, dan debits
    - Verify deposit_balance dan reward_balance dihitung dengan benar setelah backfill
    - Test edge case: wallet tanpa transaksi, wallet dengan hanya topup, wallet dengan hanya reward
    - **Validates: Requirements 1.4**

  - [ ]* 7.7 Write unit tests for controller dan view data
    - Test DashboardController passes `depositBalance` dan `rewardBalance` ke view
    - Test WithdrawalController passes split balances ke view
    - Test View Composer provides correct data to layout
    - **Validates: Requirements 5.1, 6.1, 7.1, 8.1**

- [x] 8. Final checkpoint - Pastikan semua fitur dan tests berjalan
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- Semua operasi balance menggunakan `bcadd`/`bcsub` dengan precision 2 untuk menghindari floating-point errors
- View Composer memastikan data saldo tersedia di layout tanpa perlu passing dari setiap controller

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2"] },
    { "id": 2, "tasks": ["2.1", "2.3", "2.5"] },
    { "id": 3, "tasks": ["2.2", "2.4"] },
    { "id": 4, "tasks": ["4.1", "4.2", "4.3"] },
    { "id": 5, "tasks": ["5.1", "5.2", "5.3", "5.4"] },
    { "id": 6, "tasks": ["7.1", "7.2", "7.3", "7.4", "7.5", "7.6", "7.7"] }
  ]
}
```
