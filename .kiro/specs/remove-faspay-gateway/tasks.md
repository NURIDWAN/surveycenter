# Implementation Plan: Remove Faspay Gateway

## Overview

Menghapus seluruh integrasi Faspay dari proyek Survey Grapadi secara bertahap dan aman. Urutan task dirancang agar aplikasi tetap fungsional di setiap langkah — refactoring controller terlebih dahulu (agar pembayaran tetap berjalan via SingaPay), lalu simplifikasi, pembersihan, penghapusan file, dan validasi akhir.

## Tasks

- [x] 1. Refactor TransactionController ke SingaPay
  - [x] 1.1 Ganti dependency FaspayService dengan SingaPayService di TransactionController
    - Hapus `use App\Services\FaspayService;` dan tambahkan `use App\Services\SingaPayService;`
    - Ganti property `private FaspayService $faspayService;` menjadi `private SingaPayService $singaPay;`
    - Update constructor: `public function __construct(SingaPayService $singaPay, FormLinkValidationService $formLinkValidationService)`
    - _Requirements: 2.1_
  - [x] 1.2 Refactor method `store()` untuk menggunakan SingaPayService
    - Hapus pembuatan `$invoiceData` format Faspay
    - Panggil `$this->singaPay->createInvoice($finalPrice, [['name' => $validated['title'] ?? 'Survey Payment', 'quantity' => 1, 'unit_price' => $finalPrice]], route('transactions.progress', $transaction ?? ''), $billNo)`
    - Simpan `singapay_ref` dari response (`$invoice['reff_no']`) ke Transaction
    - Handle response gagal (`!$invoice['success']`) dengan redirect back + error message
    - Redirect ke `$invoice['payment_url']` jika sukses
    - Hapus referensi ke `config('faspay.invoice_expiration')` dan `route('faspay.return')`
    - _Requirements: 2.2, 2.4, 2.5, 2.6_
  - [x] 1.3 Refactor method `processPayment()` untuk menggunakan SingaPayService
    - Ganti `$this->faspayService->createInvoice($invoiceData)` dengan pola yang sama seperti store()
    - Panggil `$this->singaPay->createInvoice($transaction->amount, [['name' => $transaction->survey->title ?? 'Survey Payment', 'quantity' => 1, 'unit_price' => $transaction->amount]], route('transactions.progress', $transaction), $billNo)`
    - Simpan `singapay_ref` dari response ke transaction
    - Handle error dan redirect ke payment_url
    - Hapus referensi ke `config('faspay.invoice_expiration')` dan `route('faspay.return')`
    - _Requirements: 2.3, 2.4, 2.5, 2.6_

- [x] 2. Simplifikasi TopupController
  - [x] 2.1 Hapus branch Faspay dari TopupController
    - Hapus `use App\Services\FaspayService;`
    - Hapus seluruh method `processFaspayPayment()`
    - Di method `store()`: hapus blok `if ($selectedGateway === 'faspay')` dan langsung panggil `return $this->processSingaPayPayment($transaction, $validated['payment_method']);`
    - _Requirements: 3.1, 3.2, 3.3_

- [x] 3. Bersihkan PaymentController
  - [x] 3.1 Hapus import FaspayService yang tidak terpakai
    - Hapus baris `use App\Services\FaspayService;` dari `app/Http/Controllers/User/PaymentController.php`
    - Pastikan tidak ada perubahan logika lainnya
    - _Requirements: 4.1, 4.2_

- [x] 4. Bersihkan HubWebhookController
  - [x] 4.1 Hapus method faspay dan referensi FaspayController
    - Hapus method `public function faspay(Request $request, FaspayController $delegate)` beserta isinya
    - Hapus import `FaspayController` jika ada (saat ini digunakan via type-hint parameter)
    - Pastikan method `singapay()` dan `verifyHubSignature()` tetap utuh tanpa perubahan
    - _Requirements: 5.1, 5.2, 5.3_

- [x] 5. Sederhanakan konfigurasi payment_gateways.php
  - [x] 5.1 Update config/payment_gateways.php
    - Hapus seluruh closure `$normalizeGateway` (tidak diperlukan lagi)
    - Hapus logika parsing `PAYMENT_GATEWAY_ORDER` env variable
    - Hardcode `'order' => ['singapay']`
    - Hardcode `'default' => 'singapay'`
    - Hapus entry `'faspay'` dari array `'gateways'`
    - Hapus referensi ke env `PAYMENT_GATEWAY_FASPAY_ENABLED`, `PAYMENT_GATEWAY_FASTPAY_ENABLED`, `FASPAY_MERCHANT_ID`, `FASPAY_USER_ID`, `FASPAY_PASSWORD`
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 6. Hapus route Faspay dari web.php dan api.php
  - [x] 6.1 Hapus route Faspay dari routes/web.php
    - Hapus import `use App\Http\Controllers\FaspayTestTransactionController;`
    - Hapus import `use App\Http\Controllers\FaspayController;`
    - Hapus route `Route::get('/transaction/faspay/return', ...)` (baris ~192)
    - Hapus seluruh blok `Route::middleware(['auth'])->prefix('faspay/test')->name('faspay.')->group(...)` (test transaction routes)
    - Hapus route `Route::post('/api/webhook/faspay/notification', ...)` (webhook notification)
    - Hapus blok `Route::middleware(['auth'])->prefix('faspay')->group(...)` (debug routes: `faspay/debug`, `faspay/list-transactions`)
    - Hapus komentar yang mereferensi Faspay (seperti `// ===== FASPAY XPRESS INTEGRATION ROUTES =====`)
    - _Requirements: 7.1, 7.2, 7.3_
  - [x] 6.2 Hapus route Faspay dari routes/api.php
    - Hapus route `Route::post('/hub-webhook/faspay', [HubWebhookController::class, 'faspay'])->...->name('hub.webhook.faspay');`
    - _Requirements: 7.4_

- [x] 7. Hapus semua file khusus Faspay
  - [x] 7.1 Hapus file service, controller, model, views, dan tests Faspay
    - Hapus `app/Services/FaspayService.php`
    - Hapus `config/faspay.php`
    - Hapus `app/Http/Controllers/FaspayController.php`
    - Hapus `app/Http/Controllers/FaspayTestTransactionController.php`
    - Hapus `app/Models/FaspayTestTransaction.php`
    - Hapus seluruh direktori `resources/views/faspay/`
    - Hapus `tests/Feature/FaspayWebhookNotificationTest.php`
    - Hapus `tests/Feature/FaspayPaymentProcessTest.php`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8_

- [x] 8. Buat migrasi untuk drop tabel faspay_test_transactions
  - [x] 8.1 Buat file migrasi baru
    - Buat file `database/migrations/{timestamp}_drop_faspay_test_transactions_table.php`
    - Method `up()`: `Schema::dropIfExists('faspay_test_transactions');`
    - Method `down()`: Buat ulang tabel dengan kolom: `id`, `bill_no` (string, unique), `amount` (decimal 12,2), `status` (string, default 'pending'), `trx_id` (string, nullable), `payment_url` (string, nullable), `webhook_payload` (json, nullable), `timestamps`
    - _Requirements: 8.1, 8.2_

- [x] 9. Checkpoint - Validasi integritas
  - Jalankan `php artisan route:list` dan pastikan tidak ada route Faspay
  - Jalankan `grep -r "Faspay\|FaspayService\|FaspayController\|FaspayTestTransaction" app/ config/ routes/` dan pastikan tidak ada referensi tersisa
  - Jalankan `php artisan optimize:clear` untuk memastikan tidak ada cache lama
  - Pastikan semua perubahan konsisten dan tidak ada orphaned code
  - _Requirements: 9.1, 9.2, 9.3, 9.4_

## Task Dependency Graph

```json
{
  "waves": [
    {
      "name": "Wave 1 - Refactor Controllers",
      "tasks": ["1"],
      "description": "Refactor TransactionController ke SingaPay agar pembayaran tetap berjalan"
    },
    {
      "name": "Wave 2 - Cleanup Controllers",
      "tasks": ["2", "3", "4"],
      "description": "Simplifikasi TopupController, bersihkan PaymentController dan HubWebhookController"
    },
    {
      "name": "Wave 3 - Config & Routes",
      "tasks": ["5", "6"],
      "description": "Sederhanakan konfigurasi dan hapus route Faspay"
    },
    {
      "name": "Wave 4 - Delete Files & Migrate",
      "tasks": ["7", "8"],
      "description": "Hapus semua file Faspay dan buat migrasi database"
    },
    {
      "name": "Wave 5 - Validate",
      "tasks": ["9"],
      "description": "Validasi integritas keseluruhan aplikasi"
    }
  ]
}
```

## Notes

- Urutan task dirancang agar aplikasi tetap fungsional di setiap langkah. Task 1-4 melakukan refactoring terlebih dahulu sehingga alur pembayaran tetap berjalan via SingaPay sebelum file Faspay dihapus.
- Task 7 (penghapusan file) baru dilakukan setelah semua referensi ke file tersebut sudah dihapus di task sebelumnya.
- Tidak ada property-based test karena fitur ini adalah penghapusan kode (removal/cleanup), bukan penambahan logika baru.
- Semua referensi requirements merujuk ke nomor requirement di requirements.md.
