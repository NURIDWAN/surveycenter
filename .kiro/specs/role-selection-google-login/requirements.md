# Requirements Document

## Introduction

This feature consolidates the separate responden authentication pages (login and register) into the existing main authentication pages. The main login and register pages already feature a role selector ("Buat Survey" / "Isi Survey & Dapatkan Saldo"). This consolidation extends the role selector behavior to Google OAuth login, so that clicking "Masuk dengan Google" assigns the user a role (surveyor or responden) based on the currently selected role tab. The separate responden auth routes and Blade views are removed entirely.

## Glossary

- **Main_Login_Page**: The primary login page at `/login` rendered by `resources/views/auth/login.blade.php`, which includes a role selector and Google login button.
- **Main_Register_Page**: The primary register page at `/register` rendered by `resources/views/auth/register.blade.php`, which includes a role selector and Google registration button.
- **Role_Selector**: An Alpine.js-based tab component on the main auth pages allowing users to choose between "Buat Survey" (surveyor) and "Isi Survey & Dapatkan Saldo" (responden) roles.
- **GoogleAuthController**: The controller `App\Http\Controllers\Auth\GoogleAuthController` that handles Google OAuth redirect and callback.
- **Responden_Auth_Pages**: The separate login and register pages at `/responden/login` and `/responden/register` rendered by Blade views in `resources/views/responden/auth/`.
- **Responden_Auth_Routes**: The Laravel routes prefixed with `/responden` that serve the responden login and register forms (`responden.login`, `responden.login.submit`, `responden.register`, `responden.register.submit`).
- **is_responden**: A boolean field on the User model that identifies a user as a responden.
- **Wallet**: A model associated with responden users that stores their balance for survey participation rewards.
- **RespondenMiddleware**: Middleware that checks if a user is authenticated and has `is_responden = true`; currently redirects to `responden.login` when the check fails.

## Requirements

### Requirement 1: Google Login Role Selection

**User Story:** As a user on the main login page, I want Google login to respect my role tab selection, so that I am registered or logged in with the correct role without needing an intermediate page.

#### Acceptance Criteria

1. WHEN a user clicks the Google login button on the Main_Login_Page, THE GoogleAuthController SHALL store the currently selected Role_Selector value in the session before redirecting to Google OAuth.
2. WHEN a new user completes Google OAuth callback and the session contains the responden role value, THE GoogleAuthController SHALL create the User record with `is_responden` set to true.
3. WHEN a new user completes Google OAuth callback and the session contains the responden role value, THE GoogleAuthController SHALL create a Wallet record with a zero balance for the new user.
4. WHEN a new user completes Google OAuth callback and the session contains the surveyor role value, THE GoogleAuthController SHALL create the User record with `is_responden` set to false.
5. WHEN an existing user with `is_responden` set to false logs in via Google OAuth and the session contains the responden role value, THE GoogleAuthController SHALL update the user record to set `is_responden` to true and create a Wallet if one does not exist.
6. WHEN an existing responden user logs in via Google OAuth, THE GoogleAuthController SHALL redirect the user to the responden dashboard.
7. WHEN an existing surveyor user logs in via Google OAuth and the session contains the surveyor role value, THE GoogleAuthController SHALL redirect the user to the surveyor dashboard.

### Requirement 2: Google Register Role Selection

**User Story:** As a user on the main register page, I want Google registration to respect my role tab selection, so that my account is created with the correct role.

#### Acceptance Criteria

1. WHEN a user clicks the Google registration button on the Main_Register_Page, THE GoogleAuthController SHALL store the currently selected Role_Selector value in the session before redirecting to Google OAuth.
2. WHEN the Google OAuth callback processes a new user with the responden role in session, THE GoogleAuthController SHALL redirect the new user to the responden dashboard with a success message.
3. WHEN the Google OAuth callback processes a new user with the surveyor role in session, THE GoogleAuthController SHALL redirect the new user to the surveyor dashboard with a success message.

### Requirement 3: Role Parameter Transmission to Google OAuth

**User Story:** As a developer, I want the role selection to be transmitted reliably to the Google OAuth callback, so that the system correctly assigns the role after the OAuth round-trip.

#### Acceptance Criteria

1. THE Main_Login_Page SHALL include the selected role value as a query parameter in the Google OAuth link URL.
2. THE Main_Register_Page SHALL include the selected role value as a query parameter in the Google OAuth link URL.
3. WHEN the GoogleAuthController receives a redirect request with a role query parameter, THE GoogleAuthController SHALL store the role value in the session before redirecting to Google.
4. WHEN the Google OAuth callback completes, THE GoogleAuthController SHALL retrieve and remove the role value from the session.
5. IF the role value is absent from the session during the Google OAuth callback, THEN THE GoogleAuthController SHALL default to the surveyor role.

### Requirement 4: Remove Separate Responden Auth Pages

**User Story:** As a developer, I want the separate responden auth pages removed, so that there is a single consolidated authentication flow.

#### Acceptance Criteria

1. THE application SHALL NOT serve a page at the `/responden/login` URL path.
2. THE application SHALL NOT serve a page at the `/responden/register` URL path.
3. THE application SHALL remove the route definitions for `responden.login`, `responden.login.submit`, `responden.register`, and `responden.register.submit`.
4. THE application SHALL remove the Blade view files `resources/views/responden/auth/login.blade.php` and `resources/views/responden/auth/register.blade.php`.
5. THE application SHALL retain the `responden.logout` route for authenticated responden users to log out.
6. THE application SHALL retain the `responden.login.submit` and `responden.register.submit` route names as they are still referenced by the consolidated main auth pages form action.

### Requirement 5: Update References to Removed Routes

**User Story:** As a user navigating the site, I want all links that previously pointed to `/responden/login` to now point to the main login page, so that navigation remains functional.

#### Acceptance Criteria

1. WHEN the navbar renders the "Isi Survey & Dapatkan Saldo" link, THE navbar partial SHALL link to the main login page URL with a query parameter or anchor indicating the responden role.
2. WHEN the RespondenMiddleware detects an unauthenticated or non-responden user, THE RespondenMiddleware SHALL redirect to the main login page instead of the removed responden login page.
3. WHEN a responden user logs out, THE Responden AuthController logout method SHALL redirect to the main login page instead of the removed responden login page.

### Requirement 6: Preserve Existing Email-Based Login Form Behavior

**User Story:** As a user logging in via email on the main login page, I want the form to continue submitting to the correct endpoint based on my role tab selection, so that email-based login is not disrupted.

#### Acceptance Criteria

1. WHILE the surveyor role tab is selected on the Main_Login_Page, THE login form SHALL submit to the surveyor login endpoint.
2. WHILE the responden role tab is selected on the Main_Login_Page, THE login form SHALL submit to the responden login endpoint.
3. WHILE the surveyor role tab is selected on the Main_Register_Page, THE register form SHALL submit to the surveyor register endpoint.
4. WHILE the responden role tab is selected on the Main_Register_Page, THE register form SHALL submit to the responden register endpoint.
