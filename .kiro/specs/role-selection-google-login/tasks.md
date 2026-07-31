# Implementation Plan: Role Selection Google Login

## Overview

Modify the existing Google OAuth flow to respect the role tab selection on the main auth pages, consolidate responden auth into the main login/register pages, and remove the separate responden auth page routes/views. Implementation uses the existing Laravel 12 + Blade + Alpine.js + Socialite stack.

## Tasks

- [x] 1. Modify GoogleAuthController to support role-based OAuth flow
  - [x] 1.1 Update `redirectToGoogle` to accept and store role query parameter in session
    - Accept `role` query parameter from request, validate against allowed values (`survey`, `responden`)
    - Default to `survey` if missing or invalid
    - Store validated role in session under key `google_auth_role`
    - _Requirements: 1.1, 2.1, 3.3_

  - [x] 1.2 Refactor `handleGoogleCallback` to use session role for user creation and routing
    - Pull `google_auth_role` from session (retrieve + remove), default to `survey`
    - Set `is_responden` flag based on role value
    - For new users: create User with correct `is_responden` value, create Wallet if responden
    - For existing users: upgrade to responden if requested (set `is_responden = true`, create Wallet if missing)
    - Block admin accounts from Google login
    - Redirect to `responden.dashboard` if user is responden, otherwise `user.dashboard`
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 2.2, 2.3, 3.4, 3.5_

  - [ ]* 1.3 Write property test: Role parameter stored in session before OAuth redirect (Property 1)
    - **Property 1: Role parameter is stored in session before OAuth redirect**
    - **Validates: Requirements 1.1, 2.1, 3.3**

  - [ ]* 1.4 Write property test: New user role mapping matches session role (Property 2)
    - **Property 2: New user role mapping matches session role**
    - **Validates: Requirements 1.2, 1.4**

  - [ ]* 1.5 Write property test: Wallet creation for responden users (Property 3)
    - **Property 3: Wallet creation for responden users**
    - **Validates: Requirements 1.3**

  - [ ]* 1.6 Write property test: Existing surveyor role upgrade with wallet creation (Property 4)
    - **Property 4: Existing surveyor role upgrade with wallet creation**
    - **Validates: Requirements 1.5**

  - [ ]* 1.7 Write property test: Redirect routing based on user responden status (Property 5)
    - **Property 5: Redirect routing based on user responden status**
    - **Validates: Requirements 1.6, 1.7, 2.2, 2.3**

  - [ ]* 1.8 Write property test: Session role cleanup after OAuth callback (Property 6)
    - **Property 6: Session role cleanup after OAuth callback**
    - **Validates: Requirements 3.4**

- [x] 2. Checkpoint - Ensure controller logic tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Update Blade templates for dynamic Google OAuth link
  - [x] 3.1 Update main login page to pass role as query parameter in Google OAuth link
    - Modify `resources/views/auth/login.blade.php`
    - Change static `route('auth.google')` href to Alpine.js `:href` binding: `'{{ route('auth.google') }}' + '?role=' + role`
    - Ensure the `role` variable is available from the existing Alpine.js `x-data` scope
    - _Requirements: 3.1, 6.1, 6.2_

  - [x] 3.2 Update main register page to pass role as query parameter in Google OAuth link
    - Modify `resources/views/auth/register.blade.php`
    - Same `:href` binding pattern as login page
    - _Requirements: 3.2, 6.3, 6.4_

- [x] 4. Update routes, middleware, and references to removed responden auth pages
  - [x] 4.1 Remove responden GET login and register routes, retain POST routes and logout
    - In `routes/web.php`, remove `Route::get('/login', ...)` and `Route::get('/register', ...)` from the responden group
    - Retain `Route::post('/login', ...)`, `Route::post('/register', ...)`, and `Route::post('/logout', ...)`
    - _Requirements: 4.1, 4.2, 4.3, 4.5, 4.6_

  - [x] 4.2 Update RespondenMiddleware to redirect to main login page
    - Modify `app/Http/Middleware/RespondenMiddleware.php`
    - Change redirect from `route('responden.login')` to `route('login', ['role' => 'responden'])`
    - _Requirements: 5.2_

  - [x] 4.3 Update Responden AuthController logout to redirect to main login page
    - Modify `app/Http/Controllers/Responden/AuthController.php`
    - Change logout redirect from removed route to `route('login')`
    - Remove `showLoginForm()` and `showRegisterForm()` methods
    - _Requirements: 5.3_

  - [x] 4.4 Update navbar partial link for "Isi Survey & Dapatkan Saldo"
    - Modify `resources/views/partials/navbar.blade.php`
    - Change link from `route('responden.login')` to `route('login') . '?role=responden'`
    - _Requirements: 5.1_

  - [ ]* 4.5 Write property test: Middleware redirects non-responden users to main login (Property 7)
    - **Property 7: Middleware redirects non-responden users to main login**
    - **Validates: Requirements 5.2**

- [x] 5. Remove separate responden auth Blade views
  - [x] 5.1 Delete `resources/views/responden/auth/login.blade.php` and `resources/views/responden/auth/register.blade.php`
    - Remove the Blade view files for the separate responden login and register pages
    - _Requirements: 4.4_

- [x] 6. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- The Wallet model import (`App\Models\Wallet`) must be added to GoogleAuthController
- The `Illuminate\Support\Str` import is already present in the controller
- Existing email-based form submission behavior (Requirement 6) is preserved by retaining the POST routes

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2"] },
    { "id": 2, "tasks": ["1.3", "1.4", "1.5", "1.6", "1.7", "1.8", "3.1", "3.2", "4.1", "4.2", "4.3", "4.4"] },
    { "id": 3, "tasks": ["4.5", "5.1"] }
  ]
}
```
