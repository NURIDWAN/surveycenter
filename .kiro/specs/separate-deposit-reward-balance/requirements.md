# Requirements Document

## Introduction

Fitur ini memisahkan saldo responden menjadi dua tipe: "Saldo Deposit" (uang yang diisi melalui topup) dan "Saldo Reward" (penghasilan dari menyelesaikan survey). Kedua saldo ditampilkan secara terpisah beserta total gabungan di sidebar, top bar, dashboard, dan form penarikan. Kedua tipe saldo dapat ditarik melalui fitur "Tarik Saldo" yang sudah ada.

## Glossary

- **Sistem**: Aplikasi web SurveyCenter berbasis Laravel
- **Wallet**: Record di tabel `wallets` yang menyimpan saldo pengguna
- **Saldo_Deposit**: Saldo yang berasal dari topup (reference_type: topup)
- **Saldo_Reward**: Saldo yang berasal dari reward pengisian survey (reference_type: survey_filling)
- **Saldo_Total**: Jumlah gabungan dari Saldo_Deposit dan Saldo_Reward
- **Responden**: Pengguna dengan role responden yang mengisi survey
- **WalletTransaction**: Record transaksi kredit/debit pada wallet
- **Penarikan**: Proses withdraw saldo oleh responden melalui fitur Tarik Saldo

## Requirements

### Requirement 1: Skema Database Saldo Terpisah

**User Story:** Sebagai developer, saya ingin menyimpan saldo deposit dan saldo reward secara terpisah di database, agar sistem dapat melacak dan menampilkan kedua tipe saldo secara independen.

#### Acceptance Criteria

1. THE Sistem SHALL menyimpan kolom `deposit_balance` bertipe decimal(15,2) default 0 pada tabel `wallets`
2. THE Sistem SHALL menyimpan kolom `reward_balance` bertipe decimal(15,2) default 0 pada tabel `wallets`
3. THE Sistem SHALL mempertahankan kolom `balance` yang ada sebagai representasi Saldo_Total (deposit_balance + reward_balance)
4. WHEN migrasi dijalankan, THE Sistem SHALL mengalokasikan saldo existing berdasarkan riwayat WalletTransaction: total credit dengan reference_type topup dikurangi total debit menjadi deposit_balance, dan total credit dengan reference_type survey_filling dikurangi sisa debit menjadi reward_balance

### Requirement 2: Kredit Saldo Deposit via Topup

**User Story:** Sebagai responden, saya ingin uang yang saya topup masuk ke Saldo Deposit, agar saya dapat melihat berapa uang yang berasal dari topup saya.

#### Acceptance Criteria

1. WHEN topup berhasil dikonfirmasi (status paid), THE Sistem SHALL menambah nilai `deposit_balance` pada Wallet sebesar jumlah topup
2. WHEN topup berhasil dikonfirmasi, THE Sistem SHALL memperbarui kolom `balance` agar sama dengan jumlah `deposit_balance` + `reward_balance`
3. WHEN topup berhasil dikonfirmasi, THE Sistem SHALL mencatat WalletTransaction dengan reference_type topup dan amount yang sesuai

### Requirement 3: Kredit Saldo Reward via Survey Filling

**User Story:** Sebagai responden, saya ingin penghasilan dari survey masuk ke Saldo Reward, agar saya dapat melihat berapa uang yang saya dapatkan dari mengisi survey.

#### Acceptance Criteria

1. WHEN pengisian survey disetujui (approved), THE Sistem SHALL menambah nilai `reward_balance` pada Wallet sebesar reward_amount survey
2. WHEN pengisian survey disetujui, THE Sistem SHALL memperbarui kolom `balance` agar sama dengan jumlah `deposit_balance` + `reward_balance`
3. WHEN pengisian survey disetujui, THE Sistem SHALL mencatat WalletTransaction dengan reference_type survey_filling dan amount yang sesuai

### Requirement 4: Debit Saldo saat Penarikan

**User Story:** Sebagai responden, saya ingin dapat menarik dari kedua tipe saldo, agar semua uang di akun saya dapat dicairkan.

#### Acceptance Criteria

1. WHEN responden melakukan Penarikan, THE Sistem SHALL mengurangi saldo dari `reward_balance` terlebih dahulu sampai habis, kemudian sisanya dari `deposit_balance`
2. WHEN responden melakukan Penarikan, THE Sistem SHALL memperbarui kolom `balance` agar sama dengan jumlah `deposit_balance` + `reward_balance` setelah debit
3. IF jumlah Penarikan melebihi Saldo_Total, THEN THE Sistem SHALL menolak permintaan Penarikan dengan pesan "Saldo tidak mencukupi"
4. THE Sistem SHALL memvalidasi bahwa Saldo_Total memenuhi minimum threshold sebelum mengizinkan Penarikan

### Requirement 5: Tampilan Saldo di Sidebar

**User Story:** Sebagai responden, saya ingin melihat rincian saldo saya di sidebar, agar saya mengetahui komposisi saldo deposit dan reward saya.

#### Acceptance Criteria

1. WHILE sidebar dalam kondisi terbuka (expanded), THE Sistem SHALL menampilkan label "Saldo Deposit" dengan nilai Saldo_Deposit dalam format Rupiah
2. WHILE sidebar dalam kondisi terbuka, THE Sistem SHALL menampilkan label "Saldo Reward" dengan nilai Saldo_Reward dalam format Rupiah
3. WHILE sidebar dalam kondisi terbuka, THE Sistem SHALL menampilkan label "Total Saldo" dengan nilai Saldo_Total dalam format Rupiah
4. WHILE sidebar dalam kondisi collapsed, THE Sistem SHALL menampilkan ikon wallet yang dapat diklik untuk membuka sidebar

### Requirement 6: Tampilan Saldo di Top Bar

**User Story:** Sebagai responden, saya ingin melihat total saldo saya di top bar, agar saya cepat mengetahui saldo keseluruhan tanpa membuka sidebar.

#### Acceptance Criteria

1. THE Sistem SHALL menampilkan Saldo_Total dalam format Rupiah di area top bar pada tampilan desktop (sm breakpoint ke atas)
2. WHEN responden mengarahkan kursor ke area saldo di top bar, THE Sistem SHALL menampilkan tooltip atau dropdown yang menunjukkan rincian Saldo_Deposit dan Saldo_Reward

### Requirement 7: Tampilan Saldo di Dashboard

**User Story:** Sebagai responden, saya ingin melihat rincian saldo di halaman dashboard, agar saya memiliki gambaran lengkap keuangan saya.

#### Acceptance Criteria

1. THE Sistem SHALL menampilkan card saldo di dashboard yang berisi Saldo_Deposit, Saldo_Reward, dan Saldo_Total dalam format Rupiah
2. THE Sistem SHALL membedakan secara visual antara Saldo_Deposit dan Saldo_Reward menggunakan label dan warna yang berbeda

### Requirement 8: Tampilan Saldo di Form Penarikan

**User Story:** Sebagai responden, saya ingin melihat rincian saldo di form penarikan, agar saya mengetahui saldo yang tersedia sebelum melakukan penarikan.

#### Acceptance Criteria

1. THE Sistem SHALL menampilkan Saldo_Deposit, Saldo_Reward, dan Saldo_Total pada halaman form Penarikan
2. THE Sistem SHALL menampilkan informasi minimum threshold Penarikan
3. THE Sistem SHALL memvalidasi jumlah Penarikan berdasarkan Saldo_Total (bukan per tipe saldo)
