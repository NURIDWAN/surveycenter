# Implementation Plan: Survey Center Indonesia

## Overview

This plan implements the Survey Center Indonesia feature — a respondent-facing extension to the Grapadi Survey platform. The implementation follows an incremental approach: database schema first, then models, services, middleware, controllers, routes, and finally views. Each step builds on the previous, ensuring no orphaned code.

## Tasks

- [x] 1. Database schema and configuration setup
  - [x] 1.1 Create migration to add respondent fields to users table
    - Add `is_responden` (tinyint, default 0), `whatsapp_number` (varchar 15, nullable), `tanggal_lahir` (date, nullable), `jenis_kelamin` (enum Laki-laki/Perempuan, nullable), `provinsi` (varchar 100, nullable), `kota` (varchar 100, nullable), `pendidikan` (varchar 100, nullable), `pekerjaan` (varchar 100, nullable)
    - _Requirements: 1.1, 3.1, 14.4_

  - [x] 1.2 Create migration to add reward and eligibility fields to surveys table
    - Add `reward_amount` (int unsigned, default 0), `deadline` (timestamp, nullable), `estimated_time_minutes` (int unsigned, nullable), `eligibility_criteria` (json, nullable)
    - _Requirements: 12.1, 15.1, 5.2_

  - [x] 1.3 Create migration for rejection_reasons table
    - Fields: id, label (varchar 255), created_at, updated_at
    - _Requirements: 10.1, 10.2_

  - [x] 1.4 Create migration for survey_fillings table
    - Fields: id, survey_id (FK), user_id (FK), status (varchar 30, default 'sedang_dikerjakan'), proof_file_path (varchar 500, nullable), catatan (text, nullable), rejection_reason_id (FK nullable), rejection_notes (text, nullable), approved_at (timestamp, nullable), rejected_at (timestamp, nullable), timestamps
    - Add unique constraint on (survey_id, user_id), index on status, index on (user_id, status)
    - _Requirements: 6.1, 6.3, 7.3_

  - [x] 1.5 Create migration for respondent_withdrawals table
    - Fields: id, user_id (FK), amount (decimal 15,2), provider_name (varchar 100), account_number (varchar 50), account_holder_name (varchar 255), status (varchar 20, default 'pending'), processed_at (timestamp, nullable), notes (text, nullable), timestamps
    - Add index on status, index on user_id
    - _Requirements: 13.3, 13.4_

  - [x] 1.6 Create config/responden.php configuration file
    - Define min_withdrawal, proof_disk, proof_path, proof_max_size_kb, proof_allowed_mimes
    - _Requirements: 13.1, 7.2_

  - [x] 1.7 Create RejectionReasonSeeder
    - Seed predefined rejection reasons: 'Screenshot tidak jelas / tidak terbaca', 'Screenshot bukan dari Google Form yang dimaksud', 'Jawaban tidak sesuai kriteria', 'Bukti pengisian tidak lengkap', 'Duplikat pengisian', 'Lainnya'
    - _Requirements: 10.1, 10.2_

- [x] 2. Models and relationships
  - [x] 2.1 Create SurveyFilling model
    - Define status constants, fillable fields, casts (approved_at, rejected_at as datetime), relationships (survey, user, rejectionReason)
    - _Requirements: 6.1, 7.3, 9.1, 10.3_

  - [x] 2.2 Create RejectionReason model
    - Define fillable (label), relationship to surveyFillings (hasMany)
    - _Requirements: 10.1, 10.2_

  - [x] 2.3 Create RespondentWithdrawal model
    - Define status constants (pending, approved, rejected), fillable fields, casts (amount as decimal:2, processed_at as datetime), relationship to user
    - _Requirements: 13.3, 13.4_

  - [x] 2.4 Extend User model with respondent fields and relationships
    - Add is_responden, whatsapp_number, tanggal_lahir, jenis_kelamin, provinsi, kota, pendidikan, pekerjaan to fillable
    - Add tanggal_lahir cast to date
    - Add surveyFillings() hasMany and respondentWithdrawals() hasMany relationships
    - _Requirements: 1.1, 3.2, 14.4_

  - [x] 2.5 Extend Survey model with reward and eligibility fields
    - Add reward_amount, deadline, estimated_time_minutes, eligibility_criteria to fillable
    - Add casts: eligibility_criteria as array, deadline as datetime
    - Add surveyFillings() hasMany relationship
    - _Requirements: 12.1, 15.1_

- [x] 3. Middleware and Form Requests
  - [x] 3.1 Create RespondenMiddleware
    - Check auth and is_responden flag, redirect to responden.login if unauthorized
    - Register in bootstrap/app.php (or Kernel) with alias 'responden'
    - _Requirements: 2.4, 17 (Property)_

  - [x] 3.2 Create StoreRespondenRegistrationRequest form request
    - Validate nama (required, string, max:255), email (required, email, unique:users), password (required, min:8, confirmed), whatsapp_number (required, digits_between:10,15)
    - _Requirements: 1.2, 1.3, 1.4, 1.5_

  - [x] 3.3 Create UpdateDemographicProfileRequest form request
    - Validate tanggal_lahir (nullable, date, before:today), jenis_kelamin (nullable, in:Laki-laki,Perempuan), provinsi (nullable, string, max:100), kota (nullable, string, max:100), pendidikan (nullable, string, max:100), pekerjaan (nullable, string, max:100)
    - All fields optional for partial updates
    - _Requirements: 3.3, 3.4, 3.5, 3.6_

  - [x] 3.4 Create UploadProofRequest form request
    - Validate proof_file (required, image, mimes:jpg,jpeg,png, max:2048), catatan (nullable, string, max:1000)
    - _Requirements: 7.1, 7.2, 7.5_

  - [x] 3.5 Create StoreWithdrawalRequest form request
    - Validate amount (required, integer, min:config threshold), provider_name (required, string, max:100), account_number (required, string, max:50), account_holder_name (required, string, max:255)
    - _Requirements: 13.1, 13.3_

- [x] 4. Checkpoint - Run migrations and verify schema
  - Ensure all migrations run without error, ask the user if questions arise.

- [x] 5. Service layer implementation
  - [x] 5.1 Create SurveyEligibilityService
    - Implement getAvailableSurveys(User): returns Builder query for active surveys with remaining slots, not started by user, not created by user, matching demographics
    - Implement isEligible(User, Survey): checks single survey eligibility
    - Implement matchesCriteria(User, array): checks each criterion (jenis_kelamin, age range, provinsi, kota, pendidikan, pekerjaan) — null/empty criteria means no restriction
    - _Requirements: 5.1, 5.4, 5.5, 14.2, 15.2, 15.4_

  - [ ]* 5.2 Write property test for survey eligibility matching
    - **Property 6: Survey eligibility matching**
    - **Validates: Requirements 15.2, 15.4**

  - [x] 5.3 Create SurveyFillingService
    - Implement startFilling(User, Survey): validates uniqueness and capacity, creates SurveyFilling with status 'sedang_dikerjakan'
    - Implement uploadProof(SurveyFilling, UploadedFile, ?catatan): stores file with unique name, updates status to 'menunggu_verifikasi'
    - Implement approve(SurveyFilling): within DB transaction, update status to 'disetujui', set approved_at, credit wallet via WalletService, create WalletTransaction
    - Implement reject(SurveyFilling, rejectionReasonId, ?notes): update status to 'ditolak', set rejected_at, store rejection reason
    - _Requirements: 6.1, 6.3, 7.3, 7.4, 9.1, 9.2, 9.3, 10.3_

  - [ ]* 5.4 Write property tests for SurveyFillingService
    - **Property 7: Survey filling uniqueness**
    - **Property 8: Proof upload transitions status correctly**
    - **Property 9: State transition guard — only pending verification can be approved or rejected**
    - **Property 10: Approval credits exact reward amount to wallet**
    - **Validates: Requirements 6.3, 7.1, 7.3, 9.1, 9.2, 9.3, 9.4, 10.4**

  - [x] 5.5 Create RespondentWithdrawalService
    - Implement getMinimumThreshold(): returns config value
    - Implement canWithdraw(User, amount): checks balance >= threshold and balance >= amount
    - Implement requestWithdrawal(User, amount, accountDetails): within DB transaction, lock wallet, check balance, create RespondentWithdrawal (pending), debit wallet, create WalletTransaction
    - _Requirements: 13.1, 13.2, 13.4, 13.5_

  - [ ]* 5.6 Write property tests for withdrawal service
    - **Property 13: Withdrawal requires minimum threshold and deducts balance**
    - **Property 14: Wallet balance integrity across all operations**
    - **Validates: Requirements 13.1, 13.4, 13.5**

- [x] 6. Checkpoint - Verify service layer
  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Respondent authentication controllers and views
  - [x] 7.1 Create Responden\AuthController
    - Implement showRegisterForm, register (use StoreRespondenRegistrationRequest, create user with is_responden=true, create wallet, login, redirect to dashboard), showLoginForm, login (validate credentials, check is_responden, redirect to dashboard), logout
    - _Requirements: 1.1, 1.6, 2.1, 2.2, 2.3_

  - [ ]* 7.2 Write property tests for registration validation
    - **Property 1: Registration validation rejects invalid inputs**
    - **Property 2: Registration creates wallet with zero balance**
    - **Validates: Requirements 1.2, 1.3, 1.4, 1.6**

  - [x] 7.3 Create responden layout (resources/views/layouts/responden.blade.php)
    - Responsive layout with Tailwind CSS 4, navigation bar with links to dashboard, surveys, fillings, withdrawals, profile, logout
    - Show saldo in nav bar
    - _Requirements: 4.1, 4.4_

  - [x] 7.4 Create responden auth views (login.blade.php, register.blade.php)
    - Registration form: nama, email, password, password confirmation, nomor WhatsApp
    - Login form: email, password
    - Display validation errors per field
    - _Requirements: 1.1, 1.5, 2.1, 2.2_

- [x] 8. Respondent dashboard and profile controllers and views
  - [x] 8.1 Create Responden\DashboardController
    - Fetch saldo from user's wallet, count survey tersedia (via SurveyEligibilityService), count menunggu verifikasi, count disetujui
    - Pass data and list of available surveys to view
    - _Requirements: 4.1, 4.2, 4.3_

  - [ ]* 8.2 Write property test for dashboard counts
    - **Property 15: Respondent dashboard counts accuracy**
    - **Validates: Requirements 4.2**

  - [x] 8.3 Create Responden\ProfileController
    - Implement edit (show current demographic data), update (use UpdateDemographicProfileRequest, only update submitted fields to support partial updates)
    - _Requirements: 3.1, 3.2, 3.6_

  - [ ]* 8.4 Write property tests for demographic profile
    - **Property 3: Demographic profile round-trip persistence**
    - **Property 4: Partial profile updates preserve existing data**
    - **Validates: Requirements 3.2, 3.3, 3.4, 3.5, 3.6**

  - [x] 8.5 Create dashboard view (resources/views/responden/dashboard/index.blade.php)
    - Display saldo in Rp format, counts for tersedia/menunggu/disetujui, list of available surveys with reward and estimated time
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

  - [x] 8.6 Create profile edit view (resources/views/responden/profile/edit.blade.php)
    - Form with tanggal_lahir (date picker), jenis_kelamin (dropdown), provinsi, kota, pendidikan, pekerjaan fields
    - Show current values, allow partial submission
    - _Requirements: 3.1, 3.6_

- [x] 9. Survey browsing and filling controllers and views
  - [x] 9.1 Create Responden\SurveyController
    - Implement index: use SurveyEligibilityService to get available surveys, paginate
    - Implement show: display survey detail, check eligibility, show "Mulai Survey" button
    - Implement start: use SurveyFillingService.startFilling, redirect to Google Form URL (target blank handled in view)
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.1, 6.2, 6.3, 14.2, 15.2_

  - [ ]* 9.2 Write property test for survey availability filter
    - **Property 5: Survey availability filter correctness**
    - **Validates: Requirements 5.1, 5.4, 5.5, 14.2**

  - [x] 9.3 Create Responden\SurveyFillingController
    - Implement index: list all user's survey fillings with status, rejection reasons if ditolak, reward amount if disetujui
    - Implement showUploadForm: show form for SurveyFilling in 'sedang_dikerjakan' status
    - Implement uploadProof: use SurveyFillingService.uploadProof, redirect with confirmation
    - _Requirements: 7.1, 7.3, 7.6, 11.1, 11.2, 11.3, 11.4_

  - [x] 9.4 Create survey browsing views (resources/views/responden/surveys/index.blade.php, show.blade.php)
    - Index: list surveys with title, reward (Rp format), estimated time, deadline
    - Show: full detail with title, description, reward, estimated time, eligibility criteria, deadline, "Mulai Survey" button (opens Google Form link in new tab)
    - _Requirements: 5.1, 5.2, 5.3_

  - [x] 9.5 Create survey filling views (resources/views/responden/fillings/index.blade.php, upload.blade.php)
    - Index: list all fillings with status badge, survey name, date, rejection reason if ditolak, reward if disetujui
    - Upload: file input for screenshot, optional catatan textarea, submit button
    - _Requirements: 7.1, 7.5, 7.6, 11.1, 11.2, 11.3, 11.4_

- [x] 10. Withdrawal controller and views
  - [x] 10.1 Create Responden\WithdrawalController
    - Implement index: list respondent's withdrawal history with status
    - Implement create: show withdrawal form, display current saldo and minimum threshold
    - Implement store: use StoreWithdrawalRequest and RespondentWithdrawalService.requestWithdrawal, handle insufficient balance error
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

  - [x] 10.2 Create withdrawal views (resources/views/responden/withdrawals/index.blade.php, create.blade.php)
    - Index: list withdrawals with amount, provider, status, date
    - Create: form with amount input, provider_name, account_number, account_holder_name, show current saldo and minimum threshold
    - _Requirements: 13.1, 13.2, 13.3_

- [x] 11. Admin verification controller and views
  - [x] 11.1 Create Admin\SurveyFillingVerificationController
    - Implement index: list survey fillings with filters by status, sortable by waktu kirim (descending default), paginated
    - Implement show: display filling detail with survey name, respondent info, email, waktu kirim, Google Form link, proof screenshot image
    - Implement approve: use SurveyFillingService.approve, validate status is 'menunggu_verifikasi'
    - Implement reject: use SurveyFillingService.reject with rejection_reason_id and optional notes, validate status is 'menunggu_verifikasi'
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 9.1, 9.4, 10.1, 10.2, 10.3, 10.4_

  - [x] 11.2 Create admin verification views (resources/views/admin/survey-fillings/index.blade.php, show.blade.php)
    - Index: table with Survey name, Responden name, Waktu Kirim, Status badge, Aksi button; status filter dropdown
    - Show: detail page with all info, proof screenshot display, approve button, reject button with modal (rejection reason dropdown + notes textarea)
    - _Requirements: 8.1, 8.2, 8.3, 10.1_

- [x] 12. Routes and survey reward field integration
  - [x] 12.1 Register all responden and admin verification routes
    - Add guest routes: /responden/login, /responden/register
    - Add protected routes with auth + responden middleware: dashboard, profile, surveys, fillings, withdrawals
    - Add admin routes: /admin/survey-fillings with index, show, approve, reject
    - Register RespondenMiddleware alias
    - _Requirements: 2.3, 2.4_

  - [x] 12.2 Add reward_amount, deadline, estimated_time_minutes, eligibility_criteria fields to survey create/edit forms
    - Update existing survey creation form to include reward amount (integer input), deadline (datetime picker), estimated time (integer input), eligibility criteria (multi-select or JSON editor for jenis_kelamin, age range, provinsi, kota, pendidikan, pekerjaan)
    - Update SurveyController store/update methods to handle new fields
    - _Requirements: 12.1, 12.2, 12.3, 15.1_

- [x] 13. Checkpoint - Full integration verification
  - Ensure all tests pass, ask the user if questions arise.

- [x] 14. Helper utilities and final wiring
  - [x] 14.1 Create Rupiah formatting helper
    - Implement a helper function/method that formats integers to "Rp X.XXX" pattern (e.g., 50000 → "Rp 50.000")
    - Use in all views displaying saldo or reward amounts
    - _Requirements: 4.4_

  - [ ]* 14.2 Write property test for Rupiah formatting
    - **Property 16: Rupiah formatting consistency**
    - **Validates: Requirements 4.4**

  - [ ]* 14.3 Write property test for route access control
    - **Property 17: Responden route access control**
    - **Validates: Requirements 2.4**

  - [x] 14.4 Add navigation links for responden in existing app
    - Add "Survey Center" or "Responden" link in main navigation for users with is_responden flag
    - Add "Verifikasi Survey Filling" link in admin sidebar
    - Handle dual-role navigation (survey creator + responden)
    - _Requirements: 14.1, 14.3_

- [x] 15. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- The existing Wallet/WalletTransaction system is reused — no new wallet tables needed
- All financial operations (approve, withdraw) must use DB transactions with lockForUpdate()
- Views use Blade + Tailwind CSS 4 with Vite build pipeline
- Indonesian language validation messages should be used throughout

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3", "1.6"] },
    { "id": 1, "tasks": ["1.4", "1.5", "1.7"] },
    { "id": 2, "tasks": ["2.1", "2.2", "2.3", "2.4", "2.5"] },
    { "id": 3, "tasks": ["3.1", "3.2", "3.3", "3.4", "3.5"] },
    { "id": 4, "tasks": ["5.1", "5.3", "5.5"] },
    { "id": 5, "tasks": ["5.2", "5.4", "5.6"] },
    { "id": 6, "tasks": ["7.1", "7.3"] },
    { "id": 7, "tasks": ["7.2", "7.4"] },
    { "id": 8, "tasks": ["8.1", "8.3", "8.5", "8.6"] },
    { "id": 9, "tasks": ["8.2", "8.4"] },
    { "id": 10, "tasks": ["9.1", "9.3", "9.4", "9.5"] },
    { "id": 11, "tasks": ["9.2", "10.1", "10.2"] },
    { "id": 12, "tasks": ["11.1", "11.2", "12.1", "12.2"] },
    { "id": 13, "tasks": ["14.1", "14.4"] },
    { "id": 14, "tasks": ["14.2", "14.3"] }
  ]
}
```
