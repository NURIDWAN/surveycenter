# Design Document: Survey Center Indonesia

## Overview

Survey Center Indonesia is a respondent-facing extension to the existing Grapadi Survey platform. It enables users to register as "responden", browse available surveys, fill Google Forms externally, upload proof screenshots, and earn Rupiah saldo upon admin verification. The design leverages the existing Wallet, User, and Survey models while introducing a new SurveyFilling workflow and respondent-specific controllers, middleware, and views.

## Architecture

Survey Center Indonesia extends the existing Grapadi Survey platform with a respondent-facing workflow. The architecture follows the existing Laravel MVC pattern with Blade + Tailwind CSS 4 for the frontend, introducing a new `Responden` namespace under controllers and views that parallels the existing `User` (survey creator) namespace.

### High-Level Flow

```
Responden Registration → Dashboard → Browse Surveys → Start Survey (Google Form) 
→ Upload Proof Screenshot → Admin Verification → Saldo Credited to Wallet → Withdrawal
```

### Key Architectural Decisions

1. **Reuse existing Wallet system** — Respondent saldo uses the same `wallets` and `wallet_transactions` tables already in place for survey creators.
2. **New `SurveyFilling` model** — Separate from the existing `Response` model which tracks admin-input respondent counts. `SurveyFilling` tracks individual respondent participation.
3. **Role-based access via `is_responden` flag** — Mirrors the existing `is_admin` pattern on the User model. Users can have both roles simultaneously.
4. **New `Responden` controller namespace** — `App\Http\Controllers\Responden\*` with separate middleware and route prefix `/responden`.
5. **Extend Survey model** — Add reward_amount, deadline, estimated_time, and eligibility_criteria fields to the existing surveys table.

## Components and Interfaces

### New Models

#### SurveyFilling

Tracks a respondent's participation in a survey from initiation through verification.

```php
// app/Models/SurveyFilling.php
class SurveyFilling extends Model
{
    public const STATUS_SEDANG_DIKERJAKAN = 'sedang_dikerjakan';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'survey_id',
        'user_id',
        'status',
        'proof_file_path',
        'catatan',
        'rejection_reason_id',
        'rejection_notes',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }
}
```

#### RejectionReason

Predefined reasons for admin rejection.

```php
// app/Models/RejectionReason.php
class RejectionReason extends Model
{
    protected $fillable = ['label'];

    public function surveyFillings(): HasMany
    {
        return $this->hasMany(SurveyFilling::class);
    }
}
```

#### RespondentWithdrawal

Tracks respondent withdrawal requests.

```php
// app/Models/RespondentWithdrawal.php
class RespondentWithdrawal extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'amount',
        'provider_name',
        'account_number',
        'account_holder_name',
        'status',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Modified Models

#### User (Extended)

Add `is_responden` flag and demographic profile fields.

```php
// Additional fillable fields on User model:
'is_responden',
'tanggal_lahir',
'jenis_kelamin',
'provinsi',
'kota',
'pendidikan',
'pekerjaan',
'whatsapp_number',
```

New relationships:

```php
public function surveyFillings(): HasMany
{
    return $this->hasMany(SurveyFilling::class);
}

public function respondentWithdrawals(): HasMany
{
    return $this->hasMany(RespondentWithdrawal::class);
}
```

#### Survey (Extended)

Add respondent reward and eligibility fields.

```php
// Additional fillable fields on Survey model:
'reward_amount',
'deadline',
'estimated_time_minutes',
'eligibility_criteria',
```

New cast:

```php
'eligibility_criteria' => 'array',
'deadline' => 'datetime',
```

New relationships:

```php
public function surveyFillings(): HasMany
{
    return $this->hasMany(SurveyFilling::class);
}
```

## Data Models

### New Tables

#### `survey_fillings`

```sql
CREATE TABLE survey_fillings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'sedang_dikerjakan',
    proof_file_path VARCHAR(500) NULL,
    catatan TEXT NULL,
    rejection_reason_id BIGINT UNSIGNED NULL,
    rejection_notes TEXT NULL,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rejection_reason_id) REFERENCES rejection_reasons(id) ON DELETE SET NULL,
    
    UNIQUE KEY survey_fillings_survey_user_unique (survey_id, user_id),
    INDEX survey_fillings_status_index (status),
    INDEX survey_fillings_user_status_index (user_id, status)
);
```

#### `rejection_reasons`

```sql
CREATE TABLE rejection_reasons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### `respondent_withdrawals`

```sql
CREATE TABLE respondent_withdrawals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    provider_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_holder_name VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    processed_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX respondent_withdrawals_status_index (status),
    INDEX respondent_withdrawals_user_index (user_id)
);
```

### Schema Modifications

#### `users` table additions

```sql
ALTER TABLE users
    ADD COLUMN is_responden TINYINT(1) NOT NULL DEFAULT 0 AFTER is_admin,
    ADD COLUMN whatsapp_number VARCHAR(15) NULL AFTER phone,
    ADD COLUMN tanggal_lahir DATE NULL,
    ADD COLUMN jenis_kelamin ENUM('Laki-laki', 'Perempuan') NULL,
    ADD COLUMN provinsi VARCHAR(100) NULL,
    ADD COLUMN kota VARCHAR(100) NULL,
    ADD COLUMN pendidikan VARCHAR(100) NULL,
    ADD COLUMN pekerjaan VARCHAR(100) NULL;
```

#### `surveys` table additions

```sql
ALTER TABLE surveys
    ADD COLUMN reward_amount INT UNSIGNED NULL DEFAULT 0,
    ADD COLUMN deadline TIMESTAMP NULL,
    ADD COLUMN estimated_time_minutes INT UNSIGNED NULL,
    ADD COLUMN eligibility_criteria JSON NULL;
```

## Interfaces

### Controllers

#### Respondent Auth

```php
// App\Http\Controllers\Responden\AuthController
class AuthController extends Controller
{
    public function showRegisterForm(): View;
    public function register(Request $request): RedirectResponse;
    public function showLoginForm(): View;
    public function login(Request $request): RedirectResponse;
    public function logout(Request $request): RedirectResponse;
}
```

#### Respondent Dashboard

```php
// App\Http\Controllers\Responden\DashboardController
class DashboardController extends Controller
{
    public function index(): View;
    // Returns: saldo balance, survey counts, matched available surveys
}
```

#### Respondent Profile

```php
// App\Http\Controllers\Responden\ProfileController
class ProfileController extends Controller
{
    public function edit(): View;
    public function update(Request $request): RedirectResponse;
}
```

#### Respondent Survey Browsing

```php
// App\Http\Controllers\Responden\SurveyController
class SurveyController extends Controller
{
    public function index(Request $request): View;  // Available surveys
    public function show(Survey $survey): View;     // Survey detail
    public function start(Survey $survey): RedirectResponse; // Create filling, redirect to form
}
```

#### Respondent Survey Filling

```php
// App\Http\Controllers\Responden\SurveyFillingController
class SurveyFillingController extends Controller
{
    public function index(): View;                              // Survey history
    public function showUploadForm(SurveyFilling $filling): View;
    public function uploadProof(Request $request, SurveyFilling $filling): RedirectResponse;
}
```

#### Respondent Withdrawal

```php
// App\Http\Controllers\Responden\WithdrawalController
class WithdrawalController extends Controller
{
    public function create(): View;
    public function store(Request $request): RedirectResponse;
    public function index(): View;  // Withdrawal history
}
```

#### Admin Verification

```php
// App\Http\Controllers\Admin\SurveyFillingVerificationController
class SurveyFillingVerificationController extends Controller
{
    public function index(Request $request): View;  // Verification dashboard with filters
    public function show(SurveyFilling $filling): View;  // Detail with proof
    public function approve(SurveyFilling $filling): RedirectResponse;
    public function reject(Request $request, SurveyFilling $filling): RedirectResponse;
}
```

### Services

#### SurveyFillingService

Handles the core business logic for survey filling workflow.

```php
// App\Services\SurveyFillingService
class SurveyFillingService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function startFilling(User $user, Survey $survey): SurveyFilling;
    public function uploadProof(SurveyFilling $filling, UploadedFile $file, ?string $catatan): SurveyFilling;
    public function approve(SurveyFilling $filling): SurveyFilling;
    public function reject(SurveyFilling $filling, int $rejectionReasonId, ?string $notes): SurveyFilling;
}
```

#### SurveyEligibilityService

Handles matching respondents to surveys based on demographic criteria.

```php
// App\Services\SurveyEligibilityService
class SurveyEligibilityService
{
    public function getAvailableSurveys(User $responden): Builder;
    public function isEligible(User $responden, Survey $survey): bool;
    public function matchesCriteria(User $responden, array $criteria): bool;
}
```

#### RespondentWithdrawalService

Handles withdrawal request logic.

```php
// App\Services\RespondentWithdrawalService
class RespondentWithdrawalService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function requestWithdrawal(User $user, int $amount, array $accountDetails): RespondentWithdrawal;
    public function getMinimumThreshold(): int;
    public function canWithdraw(User $user, int $amount): bool;
}
```

### Middleware

#### RespondenMiddleware

```php
// App\Http\Middleware\RespondenMiddleware
class RespondenMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->is_responden) {
            return redirect()->route('responden.login');
        }
        return $next($request);
    }
}
```

### Routes

```php
// routes/web.php additions

// Respondent Auth (guest)
Route::prefix('responden')->name('responden.')->group(function () {
    Route::get('/login', [Responden\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [Responden\AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [Responden\AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [Responden\AuthController::class, 'register'])->name('register.submit');
    Route::post('/logout', [Responden\AuthController::class, 'logout'])->name('logout');
});

// Respondent Protected Routes
Route::middleware(['auth', 'responden'])->prefix('responden')->name('responden.')->group(function () {
    Route::get('/dashboard', [Responden\DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/profile', [Responden\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Responden\ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/surveys', [Responden\SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/{survey}', [Responden\SurveyController::class, 'show'])->name('surveys.show');
    Route::post('/surveys/{survey}/start', [Responden\SurveyController::class, 'start'])->name('surveys.start');
    
    Route::get('/fillings', [Responden\SurveyFillingController::class, 'index'])->name('fillings.index');
    Route::get('/fillings/{filling}/upload', [Responden\SurveyFillingController::class, 'showUploadForm'])->name('fillings.upload');
    Route::post('/fillings/{filling}/upload', [Responden\SurveyFillingController::class, 'uploadProof'])->name('fillings.upload.store');
    
    Route::get('/withdrawals', [Responden\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/create', [Responden\WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/withdrawals', [Responden\WithdrawalController::class, 'store'])->name('withdrawals.store');
});

// Admin Verification Routes (under existing admin middleware group)
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/survey-fillings', [Admin\SurveyFillingVerificationController::class, 'index'])
        ->name('admin.survey-fillings.index');
    Route::get('/survey-fillings/{filling}', [Admin\SurveyFillingVerificationController::class, 'show'])
        ->name('admin.survey-fillings.show');
    Route::post('/survey-fillings/{filling}/approve', [Admin\SurveyFillingVerificationController::class, 'approve'])
        ->name('admin.survey-fillings.approve');
    Route::post('/survey-fillings/{filling}/reject', [Admin\SurveyFillingVerificationController::class, 'reject'])
        ->name('admin.survey-fillings.reject');
});
```

### Views Structure

```
resources/views/
├── responden/
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── dashboard/
│   │   └── index.blade.php
│   ├── profile/
│   │   └── edit.blade.php
│   ├── surveys/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── fillings/
│   │   ├── index.blade.php
│   │   └── upload.blade.php
│   └── withdrawals/
│       ├── index.blade.php
│       └── create.blade.php
├── admin/
│   └── survey-fillings/
│       ├── index.blade.php
│       └── show.blade.php
├── layouts/
│   └── responden.blade.php  (new layout for responden interface)
```

## Data Flow

### Survey Filling Workflow State Machine

```
┌─────────────────┐    Start    ┌──────────────────────┐    Upload    ┌────────────────────────┐
│   (Available)   │───────────►│  Sedang Dikerjakan    │────────────►│  Menunggu Verifikasi   │
└─────────────────┘            └──────────────────────┘             └────────────────────────┘
                                                                           │            │
                                                                    Approve│            │Reject
                                                                           ▼            ▼
                                                                    ┌───────────┐ ┌──────────┐
                                                                    │ Disetujui │ │ Ditolak  │
                                                                    └───────────┘ └──────────┘
```

### Approval Flow (Financial)

```
Admin Approves SurveyFilling
    │
    ▼
DB::transaction {
    1. Lock wallet (SELECT FOR UPDATE)
    2. SurveyFilling.status = 'disetujui'
    3. SurveyFilling.approved_at = now()
    4. wallet.balance += survey.reward_amount
    5. Create WalletTransaction (type=credit, reference_type='survey_filling', reference_id=filling.id)
}
```

### Withdrawal Flow

```
Responden Requests Withdrawal
    │
    ▼
Check: wallet.balance >= minimum_threshold
Check: wallet.balance >= requested_amount
    │
    ▼
DB::transaction {
    1. Lock wallet (SELECT FOR UPDATE)
    2. Create RespondentWithdrawal (status=pending)
    3. wallet.balance -= amount
    4. Create WalletTransaction (type=debit, reference_type='respondent_withdrawal', reference_id=withdrawal.id)
}
```

### Survey Eligibility Matching

The `eligibility_criteria` JSON column stores criteria as:

```json
{
    "jenis_kelamin": ["Laki-laki"],
    "age_min": 18,
    "age_max": 35,
    "provinsi": ["Jawa Barat", "DKI Jakarta"],
    "kota": [],
    "pendidikan": ["S1", "S2"],
    "pekerjaan": ["Karyawan Swasta", "Mahasiswa"]
}
```

Empty arrays or null values mean "no restriction" for that field. The `SurveyEligibilityService.matchesCriteria()` method checks each non-empty criterion against the user's profile.

## Error Handling

### Validation Errors

All form submissions use Laravel's built-in validation with custom Indonesian-language messages. Validation rules are defined in Form Request classes:

- `StoreRespondenRegistrationRequest` — validates nama, email uniqueness, password min:8, whatsapp_number digits/length
- `UpdateDemographicProfileRequest` — validates tanggal_lahir (date, before:today), jenis_kelamin (in:Laki-laki,Perempuan), provinsi/kota against region list
- `UploadProofRequest` — validates file (image, mimes:jpg,png, max:2048)
- `StoreWithdrawalRequest` — validates amount (integer, min:threshold), provider_name, account_number, account_holder_name

### Business Rule Violations

- **Duplicate filling attempt** — Return 409 Conflict with redirect back and error message
- **Survey at max capacity** — Return 422 with message "Survey sudah penuh"
- **Self-survey fill attempt** — Return 403 Forbidden
- **Insufficient saldo for withdrawal** — Return validation error with minimum threshold info
- **Invalid state transition** (e.g., approving already-approved filling) — Return 422 with descriptive message

### File Upload Errors

- Invalid MIME type → Validation error "File harus berformat JPG atau PNG"
- File too large → Validation error "Ukuran file maksimal 2MB"
- Storage failure → Log error + 500 with user-friendly message

### Concurrency

- Wallet operations use `lockForUpdate()` within DB transactions (following existing WalletService pattern)
- Survey filling creation uses the `UNIQUE(survey_id, user_id)` constraint as a safety net against race conditions
- Survey slot counting uses an atomic check within the transaction

## Configuration

### Environment Variables

```env
# Minimum withdrawal threshold in Rupiah
RESPONDENT_MIN_WITHDRAWAL=50000

# Proof screenshot storage disk
RESPONDENT_PROOF_DISK=local

# Proof screenshot storage path prefix
RESPONDENT_PROOF_PATH=proofs/screenshots
```

### Config File

```php
// config/responden.php
return [
    'min_withdrawal' => (int) env('RESPONDENT_MIN_WITHDRAWAL', 50000),
    'proof_disk' => env('RESPONDENT_PROOF_DISK', 'local'),
    'proof_path' => env('RESPONDENT_PROOF_PATH', 'proofs/screenshots'),
    'proof_max_size_kb' => 2048,
    'proof_allowed_mimes' => ['jpg', 'jpeg', 'png'],
];
```

### Seeder Data

```php
// RejectionReason seeder
$reasons = [
    'Screenshot tidak jelas / tidak terbaca',
    'Screenshot bukan dari Google Form yang dimaksud',
    'Jawaban tidak sesuai kriteria',
    'Bukti pengisian tidak lengkap',
    'Duplikat pengisian',
    'Lainnya',
];
```

## Testing Strategy

### Unit Tests
- Validation logic in Form Request classes (registration, profile, proof upload, withdrawal)
- SurveyEligibilityService.matchesCriteria() with specific demographic combinations
- Rupiah formatting helper with edge cases (0, large numbers)
- State machine transitions (valid and invalid)

### Property-Based Tests
- Registration validation across generated invalid inputs (Properties 1, 2)
- Demographic profile persistence and partial update invariants (Properties 3, 4)
- Survey availability filter correctness with generated survey/user combinations (Properties 5, 6)
- Wallet balance integrity across sequences of credits and debits (Properties 10, 13, 14)
- Eligibility matching logic with generated criteria and profiles (Property 6)

### Integration Tests
- Full registration → wallet creation flow
- Full survey filling workflow: start → upload → approve → wallet credit
- Withdrawal flow with balance checks
- Admin verification approve/reject with wallet side effects

### Feature Tests (HTTP)
- Route access control (responden middleware, admin middleware)
- CSRF protection on all POST routes
- File upload validation (MIME type, size limits)
- Concurrency safety on wallet operations

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Registration validation rejects invalid inputs

*For any* registration input where email is not unique, OR password is less than 8 characters, OR whatsapp_number is not digits-only or outside 10-15 character length, the registration request SHALL be rejected with appropriate validation errors and no user record SHALL be created.

**Validates: Requirements 1.2, 1.3, 1.4**

### Property 2: Registration creates wallet with zero balance

*For any* successful responden registration, the newly created user SHALL have an associated Wallet record with balance equal to zero.

**Validates: Requirements 1.6**

### Property 3: Demographic profile round-trip persistence

*For any* valid demographic profile data (valid tanggal_lahir, jenis_kelamin in {Laki-laki, Perempuan}, valid provinsi, valid kota), saving and then retrieving the profile SHALL return equivalent values for all submitted fields.

**Validates: Requirements 3.2, 3.3, 3.4, 3.5**

### Property 4: Partial profile updates preserve existing data

*For any* subset of demographic profile fields submitted in an update, only the submitted fields SHALL be modified; all other existing profile fields SHALL remain unchanged.

**Validates: Requirements 3.6**

### Property 5: Survey availability filter correctness

*For any* survey returned in the respondent's available survey list, that survey SHALL have status "active", SHALL have remaining respondent slots (approved fillings < respondent_count), SHALL NOT have been started or completed by that responden, and the responden SHALL NOT be the survey creator.

**Validates: Requirements 5.1, 5.4, 5.5, 14.2**

### Property 6: Survey eligibility matching

*For any* responden with a completed demographic profile and any survey with non-empty eligibility criteria, the survey SHALL appear in the available list if and only if the responden's profile satisfies all non-empty criteria fields. Surveys with no eligibility criteria SHALL be visible to all respondents.

**Validates: Requirements 15.2, 15.4**

### Property 7: Survey filling uniqueness

*For any* responden and any survey, at most one SurveyFilling record SHALL exist. Attempting to create a second filling for the same (survey_id, user_id) pair SHALL be rejected.

**Validates: Requirements 6.3**

### Property 8: Proof upload transitions status correctly

*For any* SurveyFilling in "sedang_dikerjakan" status, uploading a valid proof file (JPG/PNG, ≤2MB) SHALL transition the status to "menunggu_verifikasi" and store the file with a unique path.

**Validates: Requirements 7.1, 7.2, 7.3, 7.4**

### Property 9: State transition guard — only pending verification can be approved or rejected

*For any* SurveyFilling whose status is NOT "menunggu_verifikasi", both approval and rejection operations SHALL be rejected. Only fillings in "menunggu_verifikasi" status SHALL allow state transitions.

**Validates: Requirements 9.4, 10.4**

### Property 10: Approval credits exact reward amount to wallet

*For any* SurveyFilling that is approved, the responden's wallet balance SHALL increase by exactly the associated survey's reward_amount, and a WalletTransaction record SHALL be created with type "credit", the exact reward amount, and reference_type "survey_filling" pointing to the filling's ID.

**Validates: Requirements 9.1, 9.2, 9.3, 12.4**

### Property 11: Rejection stores reason and is visible to respondent

*For any* SurveyFilling that is rejected with a valid rejection_reason_id, the filling's status SHALL be "ditolak", the rejection_reason_id and optional notes SHALL be persisted, and the rejection reason SHALL be retrievable when the responden queries their survey history.

**Validates: Requirements 10.2, 10.3, 10.5**

### Property 12: Admin verification list filter consistency

*For any* status filter applied to the admin verification dashboard, all returned SurveyFilling records SHALL have a status matching the filter value, and the results SHALL be sorted by created_at descending.

**Validates: Requirements 8.2, 8.4**

### Property 13: Withdrawal requires minimum threshold and deducts balance

*For any* withdrawal request where the responden's wallet balance is below the minimum threshold, the request SHALL be rejected. For any valid withdrawal, the wallet balance SHALL decrease by exactly the withdrawal amount, a WalletTransaction with type "debit" SHALL be created, and a RespondentWithdrawal record with status "pending" SHALL exist.

**Validates: Requirements 13.1, 13.3, 13.4, 13.5**

### Property 14: Wallet balance integrity across all operations

*For any* sequence of approval credits and withdrawal debits on a respondent's wallet, the wallet balance SHALL equal the sum of all credit WalletTransaction amounts minus the sum of all debit WalletTransaction amounts. The balance SHALL never become negative.

**Validates: Requirements 9.2, 13.4**

### Property 15: Respondent dashboard counts accuracy

*For any* responden, the dashboard count for "menunggu verifikasi" SHALL equal the number of SurveyFilling records with that status for that user, and the "disetujui" count SHALL equal the number of approved fillings for that user.

**Validates: Requirements 4.2**

### Property 16: Rupiah formatting consistency

*For any* non-negative integer amount, the Rupiah format function SHALL produce a string matching the pattern "Rp " followed by the number with period-separated thousands (e.g., 50000 → "Rp 50.000").

**Validates: Requirements 4.4**

### Property 17: Responden route access control

*For any* authenticated user without the is_responden flag, accessing any responden-prefixed route SHALL result in a redirect to the responden login page. For any authenticated responden, accessing admin routes SHALL be denied.

**Validates: Requirements 2.4**
