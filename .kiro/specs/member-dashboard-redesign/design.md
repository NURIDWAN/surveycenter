# Design Document: Member Dashboard Redesign

## Overview

Full redesign of the SurveyCenter member dashboard. The redesign modifies two primary Blade templates (`layouts/user.blade.php` and `user/dashboard/index.blade.php`) and one controller (`DashboardController`) within the existing Laravel 12 application. No new packages or build tool changes are required — Chart.js is already available via `package.json`, and the frontend continues using CDN Tailwind CSS, AlpineJS, and Lucide Icons.

## Architecture

### Component Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│  Browser                                                         │
│  ┌───────────────────────┐  ┌─────────────────────────────────┐ │
│  │  Layout Sidebar       │  │  Dashboard Content Area          │ │
│  │  (layouts/user.blade) │  │  (user/dashboard/index.blade)    │ │
│  │                       │  │                                   │ │
│  │  ┌─────────────────┐  │  │  ┌─────────────┬──────────────┐ │ │
│  │  │ Logo + Toggle   │  │  │  │ Banner      │ Quick Actions│ │ │
│  │  ├─────────────────┤  │  │  │ Slider      │ Cards        │ │ │
│  │  │ Nav Groups:     │  │  │  ├─────────────┴──────────────┤ │ │
│  │  │  SURVEY         │  │  │  │ Statistics Row (5 cards)    │ │ │
│  │  │  TRANSAKSI      │  │  │  ├──────────┬────────┬────────┤ │ │
│  │  │  ANALYTICS      │  │  │  │Performanc│ Recent │ Reward │ │ │
│  │  │  REWARD         │  │  │  │e Survey  │Surveys │ & Poin │ │ │
│  │  │  AKUN           │  │  │  ├──────────┴────────┴────────┤ │ │
│  │  ├─────────────────┤  │  │  │ Footer Security Bar        │ │ │
│  │  │ Saldo + Top Up  │  │  │  └────────────────────────────┘ │ │
│  │  ├─────────────────┤  │  └─────────────────────────────────┘ │
│  │  │ Logout / Home   │  │                                      │
│  │  └─────────────────┘  │                                      │
│  └───────────────────────┘                                      │
└─────────────────────────────────────────────────────────────────┘
```

### Data Flow

```
DashboardController::index()
  ├── Existing queries (surveys, questions, respondents, transactions, banners)
  ├── NEW: Performance metrics calculation
  │     ├── responseRate = (totalObtained / totalTarget) * 100
  │     ├── completionRate = (completedSurveys / totalSurveys) * 100
  │     ├── activeSurveyCount = surveys.where(status, active).count()
  │     └── avgCompletionDays = avg(completed_at - created_at)
  ├── NEW: 7-day respondent chart data (daily aggregation)
  └── Pass all variables to view
```

## Components and Interfaces

### 1. Layout Sidebar (`resources/views/layouts/user.blade.php`)

**Changes:** Replace the flat `$userLinks` array and single "Akun" section with a grouped navigation structure.

#### Navigation Group Configuration

```php
@php
    $navGroups = [
        'SURVEY' => [
            ['route' => 'user.dashboard', 'is' => 'user.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ['route' => 'user.surveys.index', 'is' => 'user.surveys.*', 'icon' => 'clipboard-list', 'label' => 'Survey Saya'],
            ['route' => 'user.surveys.create', 'is' => 'user.surveys.create', 'icon' => 'plus-circle', 'label' => 'Buat Survey'],
            ['route' => 'user.templates.index', 'is' => 'user.templates.*', 'icon' => 'layout-template', 'label' => 'Template'],
        ],
        'TRANSAKSI' => [
            ['route' => 'user.transactions.index', 'is' => 'user.transactions.*', 'icon' => 'receipt', 'label' => 'Transaksi'],
            ['route' => 'user.wallet.index', 'is' => 'user.wallet.*', 'icon' => 'wallet', 'label' => 'Wallet'],
        ],
        'ANALYTICS' => [
            ['route' => 'user.analytics', 'is' => 'user.analytics', 'icon' => 'bar-chart-2', 'label' => 'Analytics'],
            ['route' => 'user.reports.index', 'is' => 'user.reports.*', 'icon' => 'file-bar-chart', 'label' => 'Laporan'],
        ],
        'REWARD' => [
            ['route' => 'user.rewards.index', 'is' => 'user.rewards.*', 'icon' => 'gift', 'label' => 'Reward & Poin'],
            ['route' => 'user.affiliate.index', 'is' => 'user.affiliate.*', 'icon' => 'share-2', 'label' => 'Affiliate'],
        ],
        'AKUN' => [
            ['route' => 'user.profile.show', 'is' => 'user.profile.*', 'icon' => 'user-circle', 'label' => 'Profil Saya'],
            ['route' => 'user.settings.index', 'is' => 'user.settings.*', 'icon' => 'settings', 'label' => 'Pengaturan'],
        ],
    ];
@endphp
```

#### Route Existence Check (Placeholder Links)

For navigation items whose routes do not yet exist (e.g., `user.templates.index`, `user.reports.index`, `user.settings.index`), the sidebar renders `href="#"` instead of calling `route()` which would throw an exception:

```php
@php
    $href = Route::has($link['route']) ? route($link['route']) : '#';
@endphp
<a href="{{ $href }}" ...>
```

#### Saldo Widget with Top Up Button

The existing saldo widget is retained but the "Wallet" button is replaced with a "Top Up Wallet" button linking to `user.topups.create`. The widget is positioned below the navigation groups (above the logout section).

### 2. Dashboard Controller (`app/Http/Controllers/User/DashboardController.php`)

**Changes:** Add performance metrics calculations and chart data queries.

#### New Variables Passed to View

```php
// Performance Metrics
$activeSurveys = Survey::where('user_id', $user->id)
    ->where('status', 'active')
    ->count();

$responseRate = $totalTargetResponden > 0
    ? round(($totalRespondenDiperoleh / $totalTargetResponden) * 100, 1)
    : 0;

$completedSurveys = Survey::where('user_id', $user->id)
    ->whereHas('adminResponses', function ($q) {
        $q->selectRaw('survey_id, SUM(respond_count) as total')
          ->groupBy('survey_id');
    })
    ->get()
    ->filter(fn ($s) => $s->adminResponses->sum('respond_count') >= $s->respondent_count)
    ->count();

$completionRate = $totalSurveys > 0
    ? round(($completedSurveys / $totalSurveys) * 100, 1)
    : 0;

$avgCompletionDays = Survey::where('user_id', $user->id)
    ->whereNotNull('completed_at')
    ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
    ->value('avg_days') ?? 0;

// 7-day chart data
$chartData = Response::whereHas('survey', fn ($q) => $q->where('user_id', $user->id))
    ->whereNotNull('input_by_admin_id')
    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
    ->selectRaw('DATE(created_at) as date, SUM(respond_count) as total')
    ->groupBy('date')
    ->orderBy('date')
    ->pluck('total', 'date');

// Build 7-day labels and fill missing days with 0
$chartLabels = [];
$chartRespondents = [];
$chartTargets = [];
$dailyTarget = $totalTargetResponden > 0
    ? round($totalTargetResponden / max($totalSurveys, 1) / 7, 1)
    : 0;

for ($i = 6; $i >= 0; $i--) {
    $date = now()->subDays($i)->format('Y-m-d');
    $chartLabels[] = now()->subDays($i)->format('d M');
    $chartRespondents[] = (int) ($chartData[$date] ?? 0);
    $chartTargets[] = $dailyTarget;
}
```

#### Removed Variables

The following variables are no longer passed to the view:
- `$pendingPayments`
- `$failedTransactions`
- `$recentTransactions`

### 3. Dashboard View (`resources/views/user/dashboard/index.blade.php`)

Complete rewrite of the view content. The structure follows this order:

#### Section Layout

```
┌─────────────────────────────────────────────────────────┐
│ Row 1: Banner Slider (8/12) + Quick Actions (4/12)      │
├─────────────────────────────────────────────────────────┤
│ Row 2: Statistics Cards (5 cards, responsive grid)       │
├──────────────────┬──────────────────┬───────────────────┤
│ Row 3 Col 1:     │ Row 3 Col 2:     │ Row 3 Col 3:      │
│ Performance      │ Survey Terbaru   │ Reward & Poin     │
│ Survey           │                  │                    │
├──────────────────┴──────────────────┴───────────────────┤
│ Row 4: Footer Security Bar                              │
└─────────────────────────────────────────────────────────┘
```

### 4. Controller → View Contract (Interfaces)

```php
// DashboardController passes these variables to the view:
compact(
    'user',                  // User model (authenticated)
    'totalSurveys',          // int
    'totalQuestions',         // int
    'totalTargetResponden',  // int
    'totalRespondenDiperoleh', // int
    'totalSpent',            // int (in Rupiah)
    'recentSurveys',         // Collection<Survey> (max 5)
    'banners',               // Collection<DashboardBanner>
    'activeSurveys',         // int (NEW)
    'responseRate',          // float (NEW, 0-100)
    'completionRate',        // float (NEW, 0-100)
    'avgCompletionDays',     // float (NEW)
    'chartLabels',           // array<string> (NEW, 7 items)
    'chartRespondents',      // array<int> (NEW, 7 items)
    'chartTargets',          // array<float> (NEW, 7 items)
)
```

### Chart.js Integration Interface

The Performance Chart is rendered using Chart.js loaded via CDN (already available). Data is injected into JavaScript via Blade:

```php
<canvas id="performanceChart" class="w-full h-48"></canvas>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Responden',
                data: @json($chartRespondents),
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true,
                tension: 0.4,
            },
            {
                label: 'Target',
                data: @json($chartTargets),
                borderColor: '#94a3b8',
                borderDash: [5, 5],
                fill: false,
                tension: 0.4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
```

### Sparkline Implementation

Statistics cards use inline SVG sparklines generated server-side. Each sparkline represents the last 7 days of data for that metric. The approach avoids additional JS libraries:

```php
@php
    // Example: generate sparkline path for survey creation trend
    $sparkData = [2, 3, 1, 4, 2, 5, 3]; // 7-day sample data from controller
    $max = max($sparkData) ?: 1;
    $width = 60;
    $height = 20;
    $points = collect($sparkData)->map(function ($val, $i) use ($max, $width, $height) {
        $x = ($i / 6) * $width;
        $y = $height - (($val / $max) * $height);
        return "$x,$y";
    })->implode(' ');
@endphp
<svg width="{{ $width }}" height="{{ $height }}" class="inline-block">
    <polyline points="{{ $points }}" fill="none" stroke="currentColor"
              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</svg>
```

The controller will pass sparkline data arrays for each statistic:
- `$sparkSurveys` — daily survey creation count (last 7 days)
- `$sparkQuestions` — daily question additions (last 7 days)
- `$sparkTargetResponden` — daily target respondent additions (last 7 days)
- `$sparkRespondenDiperoleh` — daily respondent acquisitions (last 7 days)
- `$sparkTransactions` — daily transaction amounts (last 7 days)

## Data Models

### Existing Models (No Changes)

| Model | Table | Key Fields Used |
|-------|-------|-----------------|
| `Survey` | `surveys` | id, user_id, title, question_count, respondent_count, status, created_at, completed_at |
| `Transaction` | `transactions` | id, user_id, survey_id, amount, status, created_at |
| `Response` | `responses` | id, survey_id, input_by_admin_id, respond_count, created_at |
| `DashboardBanner` | `dashboard_banners` | id, image, is_active, order |
| `User` | `users` | id, name, deposit_balance, point_balance |

### Survey Status Field

The `surveys` table needs a `status` column if not already present. The redesign uses:
- `'active'` — survey is live and accepting respondents
- `'draft'` — survey is created but not yet published

If the `status` column does not exist, a migration should be added. The Survey model should define status constants:

```php
// In Survey model
public const STATUS_ACTIVE = 'active';
public const STATUS_DRAFT = 'draft';
```

### Survey `completed_at` Field

The `Avg. Waktu` metric requires knowing when a survey reached its respondent target. If `completed_at` does not exist on the `surveys` table, a migration should add it as a nullable timestamp. It gets set when `adminResponses.sum(respond_count) >= respondent_count`.

## Error Handling

### Missing Routes

Navigation items referencing routes that don't exist yet (Template, Laporan, Pengaturan) use `Route::has()` to check existence and fall back to `href="#"`. This prevents `RouteNotFoundException` in production.

### Empty States

| Scenario | Behavior |
|----------|----------|
| No banners in DB | Show fallback gradient banner with welcome text |
| No surveys created | Show empty state in "Survey Terbaru" with CTA |
| Zero respondents | Performance metrics show 0% / 0 values gracefully |
| Division by zero | All percentage calculations guard against zero denominators |

### Chart Data Edge Cases

- If no responses exist in the last 7 days, all chart data points are 0
- The chart still renders with empty data (flat line at 0)
- `$dailyTarget` calculation guards against division by zero with `max($totalSurveys, 1)`

## Responsive Breakpoints Strategy

| Breakpoint | Layout Behavior |
|------------|-----------------|
| `≥ 1280px` (xl) | Full 3-column bottom grid, banner + quick actions side-by-side |
| `≥ 1024px` (lg) | 2-column bottom grid (performance + surveys, rewards below), sidebar visible |
| `≥ 768px` (md) | Statistics in 3-column grid, stacked bottom sections |
| `< 768px` (sm) | Statistics in 2-column grid, everything stacked, sidebar hidden (hamburger) |

### Tailwind Grid Classes

```html
<!-- Row 1: Banner + Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8"><!-- Banner Slider --></div>
    <div class="lg:col-span-4"><!-- Quick Action Cards --></div>
</div>

<!-- Row 2: Statistics -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
    <!-- 5 Statistics Cards -->
</div>

<!-- Row 3: Three-column bottom -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div><!-- Performance Survey --></div>
    <div><!-- Survey Terbaru --></div>
    <div><!-- Reward & Poin --></div>
</div>
```

## Removal of Deprecated Sections

The following sections from the current `index.blade.php` are removed entirely:

1. **Pricing Information Section** — The `Info Pricing untuk Create Survey` card with volume pricing tiers and minimum order info
2. **Pending Payment Alert** — The `$pendingPayments > 0` amber alert banner
3. **Failed Transaction Alert** — The `$failedTransactions > 0` red alert banner
4. **Recent Transactions Panel** — The `Transaksi Terakhir` card in the content grid

The controller stops querying `$pendingPayments`, `$failedTransactions`, and `$recentTransactions`.

## Survey Status Badge Logic

Each survey in the "Survey Terbaru" list displays a status badge:

```php
@php
    $badgeClass = match($survey->status) {
        'active' => 'bg-emerald-100 text-emerald-700',
        default  => 'bg-gray-100 text-gray-600',
    };
    $badgeLabel = match($survey->status) {
        'active' => 'Aktif',
        default  => 'Draft',
    };
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
    {{ $badgeLabel }}
</span>
```

## Footer Security Bar

A simple flex container at the bottom of the dashboard content:

```html
<div class="flex items-center justify-between px-6 py-4 bg-white rounded-xl border border-gray-200/80">
    <div class="flex items-center gap-2 text-sm text-gray-600">
        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
        <span>Keamanan Terjamin</span>
    </div>
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <span>Butuh bantuan?</span>
        <a href="{{ route('contact') ?? '#' }}" class="text-orange-600 font-medium hover:text-orange-700">
            Hubungi Support
        </a>
    </div>
</div>
```

## Testing Strategy

### Unit Tests (Example-Based)

- Verify sidebar renders all 5 navigation groups with correct items (Req 1.1–1.7)
- Verify banner slider shows fallback when no banners exist (Req 4.4)
- Verify quick action cards link to correct routes (Req 5.2–5.4)
- Verify deprecated sections are absent from rendered output (Req 11.1–11.4)
- Verify footer bar contains security notice and support link (Req 10.1–10.3)

### Property Tests

- Placeholder link resolution (Property 1)
- Rupiah formatting across all valid integer inputs (Property 2)
- Statistics aggregate correctness with generated survey data (Property 3)
- Performance metrics formula correctness with generated datasets (Property 4)
- Chart data aggregation with generated response records (Property 5)
- Recent surveys ordering/limit with varying survey counts (Property 6)
- Status badge mapping for all status values (Property 7)
- User-specific data rendering with generated user records (Property 8)

### Integration Tests

- Full page render with authenticated user returns 200
- Controller passes all required variables to view
- Chart.js script tag is present in rendered output

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Placeholder links for undefined routes

*For any* navigation item in the sidebar configuration whose route name does not exist in the Laravel route registry, the rendered `href` attribute SHALL be `"#"` rather than throwing a `RouteNotFoundException`.

**Validates: Requirements 1.8**

### Property 2: Rupiah formatting correctness

*For any* non-negative integer value representing Indonesian Rupiah (deposit balance or transaction total), the formatted output SHALL equal `"Rp "` followed by the number formatted with dot thousands separators and no decimal places (e.g., 1500000 → "Rp 1.500.000").

**Validates: Requirements 2.1, 6.6**

### Property 3: Statistics aggregate calculations

*For any* authenticated user with zero or more surveys, the statistics values SHALL satisfy:
- "Survey Dibuat" equals the count of surveys where `user_id` matches the user
- "Pertanyaan" equals the sum of `question_count` across those surveys
- "Target Responden" equals the sum of `respondent_count` across those surveys
- "Responden Diperoleh" equals the sum of `respond_count` from responses with non-null `input_by_admin_id` linked to those surveys

**Validates: Requirements 6.2, 6.3, 6.4, 6.5**

### Property 4: Performance metrics formulas

*For any* authenticated user's survey dataset:
- Response Rate SHALL equal `(totalRespondentsObtained / totalTargetRespondents) * 100` when totalTargetRespondents > 0, or 0 otherwise
- Completion Rate SHALL equal `(surveysReachingTarget / totalSurveys) * 100` when totalSurveys > 0, or 0 otherwise
- Survey Aktif SHALL equal the count of surveys with status "active"
- Avg. Waktu SHALL equal the average of `(completed_at - created_at)` in days for surveys with non-null `completed_at`, or 0 if none exist

**Validates: Requirements 7.2, 7.3, 7.4, 7.5**

### Property 5: Daily respondent chart aggregation

*For any* set of response records within the last 7 days belonging to the authenticated user's surveys, the chart data array SHALL contain exactly 7 entries (one per day), where each entry equals the sum of `respond_count` for responses created on that date, and missing days are filled with 0.

**Validates: Requirements 7.7**

### Property 6: Recent surveys ordering and limit

*For any* authenticated user with N surveys (N ≥ 0), the "Survey Terbaru" list SHALL contain `min(N, 5)` items, and they SHALL be ordered by `created_at` descending (most recent first).

**Validates: Requirements 8.1**

### Property 7: Survey status badge mapping

*For any* survey displayed in the recent surveys list, the status badge SHALL display "Aktif" with green styling when `survey.status == 'active'`, and "Draft" with gray styling for all other status values.

**Validates: Requirements 8.2**

### Property 8: User-specific data rendering

*For any* authenticated user, the dashboard SHALL display that user's `name` in the header, their `point_balance` in the Reward section, and their `deposit_balance` in the sidebar widget — each matching the exact values from the user's database record.

**Validates: Requirements 3.4, 9.1, 2.1**
