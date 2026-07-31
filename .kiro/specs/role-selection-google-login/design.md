# Design Document: Role Selection Google Login

## Overview

This feature modifies the existing Google OAuth flow in the Laravel 12 application to support role-based user creation and login. When a user clicks "Masuk dengan Google" on the main login or register page, the currently selected role tab value (surveyor or responden) is passed to the OAuth flow and used to determine user creation behavior and post-login redirect targets. The separate responden auth pages are removed and consolidated into the main auth pages.

## Architecture

This feature modifies the existing Google OAuth flow to support role-based user creation/login. The architecture follows the existing Laravel patterns in the project:

- **Controller Layer**: `GoogleAuthController` is extended to read a `role` query parameter, store it in session, and use it after the OAuth callback to determine user creation behavior and redirect targets.
- **View Layer**: The Alpine.js role selector on the main login/register Blade templates dynamically binds the selected role as a query parameter to the Google OAuth link.
- **Middleware Layer**: `RespondenMiddleware` is updated to redirect to the main login page.
- **Route Layer**: Separate responden auth page routes are removed; POST endpoints for login/register submission are retained.

No new models, migrations, or services are introduced. The change operates entirely within existing infrastructure.

## Components and Interfaces

### 1. GoogleAuthController (Modified)

**File**: `app/Http/Controllers/Auth/GoogleAuthController.php`

Responsibilities:
- Accept `role` query parameter on the redirect endpoint
- Store role in session before Google OAuth redirect
- Retrieve and remove role from session during callback
- Create new users with correct `is_responden` flag based on role
- Create Wallet for responden users
- Upgrade existing surveyor users to responden when role dictates
- Redirect to appropriate dashboard based on user role

### 2. Main Login Page (Modified)

**File**: `resources/views/auth/login.blade.php`

Responsibilities:
- Dynamically include the selected role value in the Google OAuth link URL using Alpine.js `:href` binding

### 3. Main Register Page (Modified)

**File**: `resources/views/auth/register.blade.php`

Responsibilities:
- Same Alpine.js dynamic binding as login page

### 4. RespondenMiddleware (Modified)

**File**: `app/Http/Middleware/RespondenMiddleware.php`

Responsibilities:
- Redirect unauthenticated or non-responden users to the main login page (with responden role indicator) instead of the removed `/responden/login`

### 5. Responden AuthController (Modified)

**File**: `app/Http/Controllers/Responden/AuthController.php`

Responsibilities:
- `logout()` method redirects to main login page instead of removed route
- `showLoginForm()` and `showRegisterForm()` methods are removed
- `login()` and `register()` POST handlers are retained for the consolidated form submissions

### 6. Navbar Partial (Modified)

**File**: `resources/views/partials/navbar.blade.php`

Responsibilities:
- "Isi Survey & Dapatkan Saldo" link points to main login page with `?role=responden`

### 7. Route Definitions (Modified)

**File**: `routes/web.php`

Responsibilities:
- Remove `Route::get('/login', ...)` and `Route::get('/register', ...)` from responden group
- Retain `Route::post('/login', ...)` and `Route::post('/register', ...)` for form submissions
- Retain `Route::post('/logout', ...)` for responden logout

### Interfaces

#### GoogleAuthController::redirectToGoogle

```php
public function redirectToGoogle(Request $request): RedirectResponse
{
    // Read role from query parameter, default to 'survey'
    $role = $request->query('role', 'survey');

    // Validate role is one of allowed values
    $role = in_array($role, ['survey', 'responden']) ? $role : 'survey';

    // Store in session for retrieval after OAuth callback
    $request->session()->put('google_auth_role', $role);

    return Socialite::driver('google')->redirect();
}
```

#### GoogleAuthController::handleGoogleCallback

```php
public function handleGoogleCallback(Request $request): RedirectResponse
{
    // Pull role from session (retrieve + remove), default to 'survey'
    $role = $request->session()->pull('google_auth_role', 'survey');
    $isResponden = ($role === 'responden');

    try {
        $googleUser = Socialite::driver('google')->user();

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleUser->id)->first()
            ?? User::where('email', $googleUser->email)->first();

        if ($user) {
            return $this->handleExistingUser($user, $googleUser, $isResponden, $request);
        }

        return $this->handleNewUser($googleUser, $isResponden, $request);

    } catch (Exception $e) {
        // Error handling...
    }
}
```

#### Helper Methods

```php
private function handleExistingUser(User $user, $googleUser, bool $isResponden, Request $request): RedirectResponse
{
    // Block admin accounts
    if ($user->is_admin) {
        return redirect()->route('login')
            ->withErrors(['email' => 'Akun admin tidak dapat login melalui Google.']);
    }

    // Link Google account if not already linked
    if (!$user->google_id) {
        $user->update([
            'google_id' => $googleUser->id,
            'google_avatar' => $googleUser->avatar,
        ]);
    }

    // Upgrade to responden if requested and not already
    if ($isResponden && !$user->is_responden) {
        $user->update(['is_responden' => true]);
        if (!$user->wallet) {
            Wallet::create(['user_id' => $user->id, 'balance' => 0]);
        }
    }

    Auth::login($user);
    $request->session()->regenerate();

    // Redirect based on final role state
    if ($user->is_responden) {
        return redirect()->route('responden.dashboard');
    }

    return redirect()->intended(route('user.dashboard'));
}

private function handleNewUser($googleUser, bool $isResponden, Request $request): RedirectResponse
{
    $referrerId = null;
    $refCode = $request->session()->pull('referral_code');
    if ($refCode) {
        $referrer = User::where('referral_code', $refCode)->first();
        $referrerId = $referrer?->id;
    }

    $newUser = User::create([
        'name' => $googleUser->name,
        'email' => $googleUser->email,
        'google_id' => $googleUser->id,
        'google_avatar' => $googleUser->avatar,
        'password' => Str::random(32),
        'phone' => null,
        'referred_by_id' => $referrerId,
        'is_responden' => $isResponden,
    ]);

    $newUser->forceFill(['email_verified_at' => now()])->save();

    if ($isResponden) {
        Wallet::create(['user_id' => $newUser->id, 'balance' => 0]);
    }

    Auth::login($newUser);
    $request->session()->regenerate();

    $dashboard = $isResponden ? 'responden.dashboard' : 'user.dashboard';
    $message = 'Registrasi dengan Google berhasil!';

    return redirect()->route($dashboard)->with('success', $message);
}
```

## Data Models

No schema changes are required. The existing fields are sufficient:

| Model | Field | Type | Usage |
|-------|-------|------|-------|
| User | is_responden | boolean | Determines if user is responden or surveyor |
| User | google_id | string | Google OAuth identifier |
| User | google_avatar | string | Google profile picture URL |
| Wallet | user_id | bigint (FK) | Links wallet to user |
| Wallet | balance | decimal(10,2) | Responden balance, initialized to 0 |

### Session Keys

| Key | Type | Lifetime | Purpose |
|-----|------|----------|---------|
| `google_auth_role` | string (`'survey'` or `'responden'`) | Until OAuth callback | Carries role selection across OAuth round-trip |

## Error Handling

| Scenario | Behavior |
|----------|----------|
| Invalid role query parameter value | Default to `'survey'` |
| Missing role in session at callback | Default to `'survey'` |
| Google OAuth failure (network, token) | Redirect to login with error flash message |
| Admin user attempts Google login | Redirect to login with error message |
| Existing user email conflict | Link Google account to existing user |

## View Changes

### Alpine.js Dynamic Google Link

The Google OAuth link changes from a static `route('auth.google')` to a dynamic `:href` binding:

```html
{{-- Inside x-data="{ role: 'survey' }" scope --}}
<a :href="'{{ route('auth.google') }}' + '?role=' + role"
   class="w-full py-3.5 bg-white border ...">
    {{-- Google icon SVG --}}
    Masuk dengan Google
</a>
```

This ensures the role is transmitted as a query parameter when the user clicks the Google button.

### Navbar Link Update

```html
{{-- Before --}}
<a href="{{ route('responden.login') }}">Isi Survey & Dapatkan Saldo</a>

{{-- After --}}
<a href="{{ route('login') }}?role=responden">Isi Survey & Dapatkan Saldo</a>
```

### RespondenMiddleware Redirect

```php
// Before
return redirect()->route('responden.login');

// After
return redirect()->route('login', ['role' => 'responden']);
```

## Testing Strategy

### Property-Based Tests (PHPUnit with Faker-driven randomization)

Property-based tests focus on the GoogleAuthController callback logic, which is pure business logic operating on inputs (Google user data, session role value, existing user state) and producing deterministic outputs (user records, wallet records, redirects).

- **Minimum 100 iterations** per property test using randomized inputs via Laravel factories and Faker
- Tests mock `Socialite::driver('google')->user()` to provide generated Google user data
- Tests use Laravel's session helpers to inject role values
- Tests verify database state and redirect targets

### Example-Based Tests

- Blade template rendering tests for dynamic Google OAuth link binding
- Route existence/non-existence assertions for removed responden auth pages
- Specific redirect scenarios for logout and middleware

### Integration Tests

- Full HTTP flow tests: hitting `/auth/google?role=responden`, verifying session state, then simulating callback
- Navbar rendering with correct link targets

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Role parameter is stored in session before OAuth redirect

*For any* valid role value (`'survey'` or `'responden'`) passed as a query parameter to the Google OAuth redirect endpoint, the GoogleAuthController SHALL store that exact role value in the session under the key `google_auth_role` before redirecting to Google.

**Validates: Requirements 1.1, 2.1, 3.3**

### Property 2: New user role mapping matches session role

*For any* new user created via Google OAuth callback, the `is_responden` field on the User record SHALL be `true` if and only if the session role value was `'responden'`, and `false` otherwise.

**Validates: Requirements 1.2, 1.4**

### Property 3: Wallet creation for responden users

*For any* new user created via Google OAuth callback with the responden role in session, a Wallet record SHALL exist for that user with a balance of zero. *For any* new user created with the surveyor role, no Wallet record SHALL be created.

**Validates: Requirements 1.3**

### Property 4: Existing surveyor role upgrade with wallet creation

*For any* existing user with `is_responden = false` who logs in via Google OAuth with the responden role in session, the system SHALL set `is_responden` to `true` and create a Wallet with zero balance if no Wallet already exists for that user.

**Validates: Requirements 1.5**

### Property 5: Redirect routing based on user responden status

*For any* user who completes Google OAuth login/registration, if the user has `is_responden = true` after processing, the redirect target SHALL be the responden dashboard route. If `is_responden = false`, the redirect target SHALL be the surveyor dashboard route.

**Validates: Requirements 1.6, 1.7, 2.2, 2.3**

### Property 6: Session role cleanup after OAuth callback

*For any* Google OAuth callback execution that completes (successfully or via default fallback), the session SHALL NOT contain the `google_auth_role` key after the callback method returns.

**Validates: Requirements 3.4**

### Property 7: Middleware redirects non-responden users to main login

*For any* HTTP request to a responden-protected route by an unauthenticated user or a user with `is_responden = false`, the RespondenMiddleware SHALL redirect to the main login page route (not the removed `/responden/login` path).

**Validates: Requirements 5.2**
