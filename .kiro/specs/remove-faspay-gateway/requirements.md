# Requirements Document

## Pendahuluan

Dokumen ini mendefinisikan kebutuhan untuk menghapus payment gateway Faspay dari proyek Survey Grapadi. Setelah penghapusan, SingaPay menjadi satu-satunya payment gateway yang digunakan. Alur transaksi yang ada (pembayaran survey dan top-up) harus tetap berfungsi normal menggunakan SingaPay.

## Glosarium

- **TransactionController**: Controller yang menangani pembuatan dan pemrosesan pembayaran transaksi survey
- **TopupController**: Controller yang menangani top-up saldo pengguna
- **SingaPayService**: Service class yang mengintegrasikan API SingaPay untuk pembuatan invoice dan pemrosesan webhook
- **FaspayService**: Service class Faspay yang akan dihapus
- **HubWebhookController**: Controller yang menerima webhook dari Payment Callback Hub terpusat
- **payment_gateways.php**: File konfigurasi yang mendefinisikan gateway pembayaran yang tersedia
- **Invoice**: Tagihan pembayaran yang dibuat melalui API payment gateway

## Requirements

### Requirement 1: Penghapusan File Faspay

**User Story:** Sebagai developer, saya ingin menghapus semua file yang khusus untuk Faspay, agar codebase lebih bersih dan tidak mengandung kode yang tidak terpakai.

#### Acceptance Criteria

1. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `app/Services/FaspayService.php`
2. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `config/faspay.php`
3. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `app/Http/Controllers/FaspayController.php`
4. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `app/Http/Controllers/FaspayTestTransactionController.php`
5. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `app/Models/FaspayTestTransaction.php`
6. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus direktori `resources/views/faspay/` beserta seluruh isinya
7. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `tests/Feature/FaspayWebhookNotificationTest.php`
8. WHEN proses penghapusan dilakukan, THE Sistem SHALL menghapus file `tests/Feature/FaspayPaymentProcessTest.php`

### Requirement 2: Refactoring TransactionController ke SingaPay

**User Story:** Sebagai pengguna, saya ingin tetap dapat membayar survey menggunakan payment gateway yang tersedia, agar alur pembayaran tidak terganggu setelah Faspay dihapus.

#### Acceptance Criteria

1. WHEN TransactionController di-refactor, THE TransactionController SHALL menggunakan SingaPayService sebagai dependency injection menggantikan FaspayService
2. WHEN method `store()` memproses pembayaran non-mock, THE TransactionController SHALL memanggil `SingaPayService::createInvoice()` dengan parameter yang sesuai (amount, items array, redirect URL, dan bill number)
3. WHEN method `processPayment()` memproses pembayaran non-mock, THE TransactionController SHALL memanggil `SingaPayService::createInvoice()` dengan parameter yang sesuai (amount, items array, redirect URL, dan bill number)
4. WHEN SingaPayService berhasil membuat invoice, THE TransactionController SHALL menyimpan `singapay_ref` dari response ke field yang sesuai pada model Transaction
5. WHEN SingaPayService gagal membuat invoice, THE TransactionController SHALL mengembalikan user ke halaman sebelumnya dengan pesan error yang informatif
6. WHEN pembayaran berhasil dibuat, THE TransactionController SHALL melakukan redirect ke `payment_url` dari response SingaPay

### Requirement 3: Pembersihan TopupController

**User Story:** Sebagai developer, saya ingin menghapus jalur pembayaran Faspay dari TopupController, agar hanya SingaPay yang tersedia sebagai opsi pembayaran top-up.

#### Acceptance Criteria

1. WHEN TopupController di-refactor, THE TopupController SHALL menghapus method `processFaspayPayment()` secara keseluruhan
2. WHEN TopupController di-refactor, THE TopupController SHALL menghapus import `use App\Services\FaspayService`
3. WHEN pengguna melakukan top-up, THE TopupController SHALL langsung memproses pembayaran menggunakan SingaPay tanpa percabangan gateway
4. IF gateway yang dipilih bukan SingaPay, THEN THE TopupController SHALL menolak request dengan pesan error yang sesuai

### Requirement 4: Pembersihan PaymentController

**User Story:** Sebagai developer, saya ingin menghapus import FaspayService yang tidak terpakai dari PaymentController, agar kode tetap bersih.

#### Acceptance Criteria

1. WHEN PaymentController di-refactor, THE PaymentController SHALL menghapus baris `use App\Services\FaspayService`
2. WHEN PaymentController di-refactor, THE PaymentController SHALL tetap mempertahankan semua fungsionalitas yang ada tanpa perubahan logika

### Requirement 5: Pembersihan HubWebhookController

**User Story:** Sebagai developer, saya ingin menghapus method webhook Faspay dari HubWebhookController, agar tidak ada endpoint yang mereferensi controller yang sudah dihapus.

#### Acceptance Criteria

1. WHEN HubWebhookController di-refactor, THE HubWebhookController SHALL menghapus method `faspay()` secara keseluruhan
2. WHEN HubWebhookController di-refactor, THE HubWebhookController SHALL menghapus import `use App\Http\Controllers\FaspayController` jika ada
3. WHEN HubWebhookController di-refactor, THE HubWebhookController SHALL tetap mempertahankan method `singapay()` dan `verifyHubSignature()` tanpa perubahan

### Requirement 6: Penyederhanaan Konfigurasi Payment Gateway

**User Story:** Sebagai developer, saya ingin menyederhanakan konfigurasi payment gateway agar hanya SingaPay yang terdaftar, sehingga tidak ada referensi ke Faspay di konfigurasi.

#### Acceptance Criteria

1. WHEN konfigurasi di-update, THE config/payment_gateways.php SHALL menghapus entry `faspay` dari array `gateways`
2. WHEN konfigurasi di-update, THE config/payment_gateways.php SHALL mengatur `default` menjadi `'singapay'` secara hardcoded
3. WHEN konfigurasi di-update, THE config/payment_gateways.php SHALL menghapus logika normalisasi yang berkaitan dengan `'fastpay'` dan `'faspay'`
4. WHEN konfigurasi di-update, THE config/payment_gateways.php SHALL menyederhanakan `order` agar hanya berisi `['singapay']`

### Requirement 7: Penghapusan Route Faspay

**User Story:** Sebagai developer, saya ingin menghapus semua route yang terkait Faspay, agar tidak ada URL endpoint yang mengarah ke controller yang sudah dihapus.

#### Acceptance Criteria

1. WHEN routes di-update, THE routes/web.php SHALL menghapus seluruh blok route Faspay test transaction (prefix `faspay/test`)
2. WHEN routes di-update, THE routes/web.php SHALL menghapus route webhook Faspay notification (`/api/webhook/faspay/notification`)
3. WHEN routes di-update, THE routes/web.php SHALL menghapus route Faspay debug (`faspay/debug` dan `faspay/list-transactions`)
4. WHEN routes di-update, THE routes/api.php SHALL menghapus route `/hub-webhook/faspay`

### Requirement 8: Migrasi Database untuk Penghapusan Tabel

**User Story:** Sebagai developer, saya ingin membuat migrasi baru untuk menghapus tabel `faspay_test_transactions`, agar database tidak menyimpan tabel yang tidak terpakai.

#### Acceptance Criteria

1. WHEN migrasi baru dijalankan, THE Migrasi SHALL menghapus tabel `faspay_test_transactions` dari database
2. WHEN migrasi di-rollback, THE Migrasi SHALL membuat ulang tabel `faspay_test_transactions` dengan struktur yang sesuai

### Requirement 9: Validasi Integritas Setelah Penghapusan

**User Story:** Sebagai developer, saya ingin memastikan bahwa setelah semua perubahan dilakukan, aplikasi tetap berjalan tanpa error, agar tidak ada gangguan pada production.

#### Acceptance Criteria

1. WHEN semua perubahan diterapkan, THE Aplikasi SHALL dapat melakukan proses pembayaran survey melalui SingaPay tanpa error
2. WHEN semua perubahan diterapkan, THE Aplikasi SHALL dapat melakukan proses top-up melalui SingaPay tanpa error
3. WHEN semua perubahan diterapkan, THE Aplikasi SHALL tidak memiliki referensi ke class FaspayService, FaspayController, FaspayTestTransactionController, atau FaspayTestTransaction di file manapun yang aktif
4. IF terdapat referensi tersisa ke Faspay di kode yang aktif, THEN THE Sistem SHALL menandai hal tersebut sebagai error pada saat kompilasi/autoload
