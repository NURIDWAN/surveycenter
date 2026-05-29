# Implementation Plan: Member Dashboard Redesign

## Overview

Full redesign of the SurveyCenter member dashboard. Implementation modifies the sidebar layout (`layouts/user.blade.php`), the dashboard controller (`DashboardController.php`), and completely rewrites the dashboard view (`user/dashboard/index.blade.php`). The work is broken into incremental steps: sidebar navigation grouping first, then controller data layer changes, followed by the view rewrite, and finally cleanup of deprecated sections.

## Tasks

- [x] 1. Update sidebar navigation with grouped structure and route checks
  - [x] 1.1 Refactor sidebar navigation in `resources/views/layouts/user.blade.php` to use `Route::has()` for placeholder links
    - Replace the existing `$menuGroups` array to use `Route::has($link['route'])` check for each navigation item's `href` attribute
    - Items whose routes don't exist (e.g., `user.templates.index`, `user.reports.index`, `user.settings.index`) should render `href="#"` instead of calling `route()` directly
    - Ensure the 5 groups (SURVEY, TRANSAKSI, ANALYTICS, REWARD, AKUN) remain with correct items per the design
    - Verify the "Top Up Wallet" button in the saldo widget links to `user.topups.create`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 2.1, 2.2, 2.3_

  - [ ]* 1.2 Write unit test for sidebar route existence check logic
    - Test that `Route::has()` returns false for undefined routes and the rendered href is `#`
    - Test that defined routes render their proper URL
    - **Property 1: Placeholder links for undefined routes**
    - **Validates: Requirements 1.8**

- [x] 2. Update DashboardController with new performance metrics and chart data
  - [x] 2.1 Add performance metrics calculations to `app/Http/Controllers/User/DashboardController.php`
    - Add `$activeSurveys` count query (surveys with status 'active')
    - Add `$responseRate` calculation: `(totalRespondentsObtained / totalTargetRespondents) * 100`, guarding against division by zero
    - Add `$completionRate` calculation: surveys reaching target / total surveys * 100
    - Add `$avgCompletionDays` calculation using `DATEDIFF(completed_at, created_at)` average for surveys with non-null `completed_at`
    - _Requirements: 7.2, 7.3, 7.4, 7.5_

  - [x] 2.2 Add 7-day chart data queries to `DashboardController`
    - Query daily respondent acquisition for last 7 days from `Response` model (where `input_by_admin_id` is not null)
    - Build `$chartLabels` (7 date strings), `$chartRespondents` (7 integers), `$chartTargets` (7 floats) arrays
    - Fill missing days with 0
    - Calculate `$dailyTarget` as `totalTargetResponden / max(totalSurveys, 1) / 7`
    - _Requirements: 7.6, 7.7_

  - [x] 2.3 Add sparkline data arrays to `DashboardController`
    - Add `$sparkSurveys` — daily survey creation count (last 7 days)
    - Add `$sparkQuestions` — daily question additions (last 7 days)
    - Add `$sparkTargetResponden` — daily target respondent additions (last 7 days)
    - Add `$sparkRespondenDiperoleh` — daily respondent acquisitions (last 7 days)
    - Add `$sparkTransactions` — daily transaction amounts (last 7 days)
    - _Requirements: 6.7_

  - [x] 2.4 Remove deprecated variables from `DashboardController`
    - Remove `$pendingPayments`, `$failedTransactions`, `$recentTransactions` queries and their `compact()` entries
    - Update the `compact()` call to include all new variables: `activeSurveys`, `responseRate`, `completionRate`, `avgCompletionDays`, `chartLabels`, `chartRespondents`, `chartTargets`, sparkline arrays
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

  - [ ]* 2.5 Write property tests for performance metrics formulas
    - **Property 4: Performance metrics formulas**
    - **Validates: Requirements 7.2, 7.3, 7.4, 7.5**

  - [ ]* 2.6 Write property test for chart data aggregation
    - **Property 5: Daily respondent chart aggregation**
    - **Validates: Requirements 7.7**

- [x] 3. Checkpoint - Ensure controller changes work correctly
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Check and add database migrations if needed
  - [x] 4.1 Check if `status` column exists on `surveys` table; if not, create migration
    - Add `status` column (string, default 'draft') to `surveys` table if missing
    - Add `STATUS_ACTIVE` and `STATUS_DRAFT` constants to the Survey model if not present
    - _Requirements: 7.4, 8.2_

  - [x] 4.2 Check if `completed_at` column exists on `surveys` table; if not, create migration
    - Add `completed_at` nullable timestamp column to `surveys` table if missing
    - _Requirements: 7.5_

- [x] 5. Rewrite dashboard view — Banner slider and quick actions row
  - [x] 5.1 Rewrite the banner slider section in `resources/views/user/dashboard/index.blade.php`
    - Implement AlpineJS-powered auto-rotating banner slider (4-6 second interval)
    - Add navigation dots for manual slide selection when multiple banners exist
    - Implement fallback gradient banner with welcome text when no banners exist
    - Use `grid grid-cols-1 lg:grid-cols-12` layout with banner at `lg:col-span-8`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 5.5, 13.4_

  - [x] 5.2 Implement quick action cards section
    - Create three Quick_Action_Cards: "Buat Survey Baru" → `user.surveys.create`, "Lihat Survey Saya" → `user.surveys.index`, "Laporan & Analytics" → `user.analytics`
    - Position at `lg:col-span-4` (right of banner on desktop, below on mobile)
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 6. Rewrite dashboard view — Statistics row with sparklines
  - [x] 6.1 Implement statistics cards row with server-side SVG sparklines
    - Create 5 statistics cards in `grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5` layout
    - Cards: "Survey Dibuat" (`$totalSurveys`), "Pertanyaan" (`$totalQuestions`), "Target Responden" (`$totalTargetResponden`), "Responden Diperoleh" (`$totalRespondenDiperoleh`), "Total Transaksi" (`Rp $totalSpent`)
    - Generate inline SVG sparklines from sparkline data arrays passed by controller
    - Format "Total Transaksi" value in Indonesian Rupiah with dot separators
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 13.3_

  - [ ]* 6.2 Write property test for Rupiah formatting
    - **Property 2: Rupiah formatting correctness**
    - **Validates: Requirements 2.1, 6.6**

  - [ ]* 6.3 Write property test for statistics aggregate calculations
    - **Property 3: Statistics aggregate calculations**
    - **Validates: Requirements 6.2, 6.3, 6.4, 6.5**

- [x] 7. Rewrite dashboard view — Three-column bottom section
  - [x] 7.1 Implement Performance Survey section with Chart.js line chart
    - Create performance metrics grid (Response Rate, Completion Rate, Survey Aktif, Avg. Waktu) using dynamic data from controller
    - Add Chart.js line chart with `@json($chartLabels)`, `@json($chartRespondents)`, `@json($chartTargets)` datasets
    - Push Chart.js CDN script via `@push('scripts')`
    - Style with orange "Responden" line and gray dashed "Target" line
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7_

  - [x] 7.2 Implement Survey Terbaru section with status badges
    - List up to 5 recent surveys with title, created date, respondent count
    - Display status badge: "Aktif" (green) for `status == 'active'`, "Draft" (gray) for others
    - Each survey item links to its detail page via `route('user.surveys.show', $survey)`
    - Add "Lihat Semua" link to `user.surveys.index`
    - Show empty state with CTA when no surveys exist
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [x] 7.3 Implement Reward & Poin widget
    - Display user's current `point_balance` formatted with dot separators
    - Add "Lihat Reward" button linking to `user.rewards.index`
    - Add informational text explaining how to earn points
    - _Requirements: 9.1, 9.2, 9.3_

  - [x] 7.4 Arrange bottom sections in three-column grid layout
    - Use `grid grid-cols-1 lg:grid-cols-3 gap-6` for desktop three-column layout
    - Stack vertically on viewports below `lg` breakpoint
    - _Requirements: 12.1, 12.2_

  - [ ]* 7.5 Write property test for recent surveys ordering and limit
    - **Property 6: Recent surveys ordering and limit**
    - **Validates: Requirements 8.1**

  - [ ]* 7.6 Write property test for survey status badge mapping
    - **Property 7: Survey status badge mapping**
    - **Validates: Requirements 8.2**

- [x] 8. Implement footer bar and remove deprecated sections
  - [x] 8.1 Add footer security bar to dashboard view
    - Add flex container with "Keamanan Terjamin" notice (shield-check icon) and "Hubungi Support" link
    - Use `Route::has('contact')` check for support link href
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [x] 8.2 Remove all deprecated sections from dashboard view
    - Remove pricing information section ("Info Pricing untuk Create Survey")
    - Remove pending payment alert banner (`$pendingPayments > 0` check)
    - Remove failed transaction alert banner (`$failedTransactions > 0` check)
    - Remove recent transactions panel ("Transaksi Terakhir")
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

- [x] 9. Responsive design verification and final wiring
  - [x] 9.1 Ensure responsive breakpoints are correctly applied across all sections
    - Verify `xl:grid-cols-5` for statistics on extra-large screens
    - Verify `lg:grid-cols-12` for banner + quick actions
    - Verify `lg:grid-cols-3` for bottom three-column section
    - Verify sidebar hidden on mobile with hamburger toggle (already in layout)
    - Verify statistics wrap to `grid-cols-2` on small screens
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

  - [ ]* 9.2 Write property test for user-specific data rendering
    - **Property 8: User-specific data rendering**
    - **Validates: Requirements 3.4, 9.1, 2.1**

- [x] 10. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Chart.js is already available via CDN — no new package installation needed
- The sidebar already has the grouped structure in place; task 1.1 focuses on adding `Route::has()` safety checks
- Survey `status` and `completed_at` columns may already exist — task 4 checks first before creating migrations
- The existing dashboard view already has much of the structure; the rewrite tasks refine it to match the design spec exactly with dynamic data

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "4.1", "4.2"] },
    { "id": 1, "tasks": ["1.2", "2.1", "2.2", "2.3"] },
    { "id": 2, "tasks": ["2.4", "2.5", "2.6"] },
    { "id": 3, "tasks": ["5.1", "5.2"] },
    { "id": 4, "tasks": ["6.1", "6.2", "6.3"] },
    { "id": 5, "tasks": ["7.1", "7.2", "7.3", "7.4"] },
    { "id": 6, "tasks": ["7.5", "7.6", "8.1", "8.2"] },
    { "id": 7, "tasks": ["9.1", "9.2"] }
  ]
}
```
