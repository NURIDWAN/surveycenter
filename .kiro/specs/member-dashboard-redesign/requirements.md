# Requirements Document

## Introduction

Full redesign of the SurveyCenter member dashboard to match a new visual mockup. The redesign replaces the current dashboard content and updates the shared layout sidebar with grouped navigation. The new dashboard introduces a promotional banner slider, quick action cards, statistics row with sparkline charts, a performance survey section with Chart.js line chart using real database metrics, a recent surveys list with status badges, a reward & points widget, and a footer security bar. Sections not present in the mockup (pricing info, pending payment alerts, failed transaction alerts, recent transactions panel) are removed.

## Glossary

- **Dashboard_Page**: The member-facing dashboard view at `resources/views/user/dashboard/index.blade.php` that displays summary data and quick actions after login.
- **Layout_Sidebar**: The shared navigation sidebar defined in `resources/views/layouts/user.blade.php` used across all member pages.
- **Dashboard_Controller**: The `App\Http\Controllers\User\DashboardController` responsible for querying data and passing variables to the Dashboard_Page.
- **Performance_Metrics**: Calculated statistics including response rate, completion rate, active survey count, and average completion time derived from real database records.
- **Banner_Slider**: A rotating promotional image carousel sourced from the `DashboardBanner` model.
- **Quick_Action_Card**: A clickable card providing shortcut navigation to key features (create survey, view surveys, reports & analytics).
- **Statistics_Card**: A compact card displaying a single numeric metric with a mini sparkline visualization.
- **Performance_Chart**: A Chart.js line chart comparing respondent acquisition versus target over a 7-day period.
- **Survey_Status_Badge**: A colored label indicating a survey's current state (Aktif or Draft).
- **Footer_Bar**: A persistent bar at the bottom of the dashboard content area displaying security assurance and support contact.

## Requirements

### Requirement 1: Sidebar Navigation Grouping

**User Story:** As a member, I want the sidebar navigation organized into labeled groups, so that I can find menu items faster.

#### Acceptance Criteria

1. THE Layout_Sidebar SHALL display navigation items organized into the following groups in order: SURVEY, TRANSAKSI, ANALYTICS, REWARD, AKUN.
2. WHEN the sidebar is in expanded state, THE Layout_Sidebar SHALL display a visible group label above each navigation group using uppercase text.
3. THE Layout_Sidebar SHALL include the following items under the SURVEY group: Dashboard, Survey Saya, Buat Survey, Template.
4. THE Layout_Sidebar SHALL include the following items under the TRANSAKSI group: Transaksi, Wallet.
5. THE Layout_Sidebar SHALL include the following items under the ANALYTICS group: Analytics, Laporan.
6. THE Layout_Sidebar SHALL include the following items under the REWARD group: Reward & Poin, Affiliate.
7. THE Layout_Sidebar SHALL include the following items under the AKUN group: Profil Saya, Pengaturan.
8. WHEN a navigation item links to a route that does not yet exist, THE Layout_Sidebar SHALL render the item as a placeholder link with a "#" href.

### Requirement 2: Sidebar Saldo Widget and Top Up Button

**User Story:** As a member, I want to see my deposit balance and a top-up button in the sidebar, so that I can quickly check and replenish my wallet.

#### Acceptance Criteria

1. THE Layout_Sidebar SHALL display a Saldo Deposit widget below the navigation groups showing the authenticated user's current deposit balance formatted in Indonesian Rupiah.
2. THE Layout_Sidebar SHALL display a "Top Up Wallet" button within or adjacent to the Saldo Deposit widget.
3. WHEN the member clicks the "Top Up Wallet" button, THE Layout_Sidebar SHALL navigate to the wallet top-up page at the `user.topups.create` route.

### Requirement 3: Dashboard Header

**User Story:** As a member, I want to see a welcoming header with my name and role when I open the dashboard, so that I feel oriented.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display the title "Dashboard" in the top header bar.
2. THE Dashboard_Page SHALL display the subtitle "Selamat datang di dashboard SurveyCenter" in the top header bar.
3. THE Dashboard_Page SHALL display a notification bell icon in the top header bar that opens the notification dropdown when clicked.
4. THE Dashboard_Page SHALL display the authenticated user's name and a "Member" role badge in the top header bar.

### Requirement 4: Promotional Banner Slider

**User Story:** As a member, I want to see promotional banners on my dashboard, so that I am aware of current offers and announcements.

#### Acceptance Criteria

1. WHEN active banners exist in the database, THE Dashboard_Page SHALL display a Banner_Slider section showing promotional images.
2. THE Banner_Slider SHALL auto-rotate between banners at an interval between 4 and 6 seconds.
3. WHEN more than one banner exists, THE Banner_Slider SHALL display navigation dots allowing manual slide selection.
4. IF no active banners exist in the database, THEN THE Dashboard_Page SHALL display a fallback promotional banner with static content.

### Requirement 5: Quick Action Cards

**User Story:** As a member, I want quick action shortcuts on the dashboard, so that I can navigate to common tasks with one click.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display three Quick_Action_Cards: "Buat Survey Baru", "Lihat Survey Saya", and "Laporan & Analytics".
2. WHEN the member clicks "Buat Survey Baru", THE Dashboard_Page SHALL navigate to the `user.surveys.create` route.
3. WHEN the member clicks "Lihat Survey Saya", THE Dashboard_Page SHALL navigate to the `user.surveys.index` route.
4. WHEN the member clicks "Laporan & Analytics", THE Dashboard_Page SHALL navigate to the `user.analytics` route.
5. THE Dashboard_Page SHALL position the Quick_Action_Cards to the right of the Banner_Slider on desktop viewports and below the Banner_Slider on mobile viewports.

### Requirement 6: Statistics Row with Sparklines

**User Story:** As a member, I want to see key metrics at a glance with trend indicators, so that I can quickly assess my survey activity.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display five Statistics_Cards in a horizontal row: "Survey Dibuat", "Pertanyaan", "Target Responden", "Responden Diperoleh", and "Total Transaksi".
2. THE Statistics_Card for "Survey Dibuat" SHALL display the total count of surveys created by the authenticated user.
3. THE Statistics_Card for "Pertanyaan" SHALL display the total sum of questions across all surveys owned by the authenticated user.
4. THE Statistics_Card for "Target Responden" SHALL display the total sum of target respondents across all surveys owned by the authenticated user.
5. THE Statistics_Card for "Responden Diperoleh" SHALL display the total count of respondents obtained via admin input for the authenticated user's surveys.
6. THE Statistics_Card for "Total Transaksi" SHALL display the total amount spent on paid transactions formatted in Indonesian Rupiah.
7. THE Dashboard_Page SHALL display a mini sparkline chart within each Statistics_Card to indicate recent trend.

### Requirement 7: Performance Survey Section

**User Story:** As a member, I want to see detailed performance metrics and a chart of respondent progress, so that I can track how my surveys are performing over time.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display a "Performance Survey" section containing four Performance_Metrics: Response Rate, Completion Rate, Survey Aktif, and Avg. Waktu.
2. THE Dashboard_Controller SHALL calculate Response Rate as the percentage of obtained respondents divided by total target respondents across all active surveys owned by the authenticated user.
3. THE Dashboard_Controller SHALL calculate Completion Rate as the percentage of surveys that have reached their full respondent target out of all surveys owned by the authenticated user.
4. THE Dashboard_Controller SHALL calculate Survey Aktif as the count of surveys with status "active" owned by the authenticated user.
5. THE Dashboard_Controller SHALL calculate Avg. Waktu as the average number of days between survey creation and respondent target completion for completed surveys owned by the authenticated user.
6. THE Dashboard_Page SHALL display a Performance_Chart using Chart.js showing two line datasets: "Responden" (obtained) and "Target" plotted over the last 7 days.
7. THE Dashboard_Controller SHALL query daily respondent acquisition data for the last 7 days to supply the Performance_Chart.

### Requirement 8: Recent Surveys List

**User Story:** As a member, I want to see my most recent surveys with their status, so that I can quickly resume work on them.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display a "Survey Terbaru" section listing up to 5 of the most recently created surveys owned by the authenticated user.
2. THE Dashboard_Page SHALL display a Survey_Status_Badge for each survey indicating either "Aktif" or "Draft" status.
3. THE Dashboard_Page SHALL display the respondent count obtained for each listed survey.
4. WHEN the member clicks a survey item in the list, THE Dashboard_Page SHALL navigate to that survey's detail page.
5. THE Dashboard_Page SHALL display a "Lihat Semua" link that navigates to the `user.surveys.index` route.

### Requirement 9: Reward and Points Widget

**User Story:** As a member, I want to see my current reward points and how to earn more, so that I am motivated to engage with the platform.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display a "Reward & Poin" section showing the authenticated user's current point balance.
2. THE Dashboard_Page SHALL display a "Lihat Reward" button that navigates to the `user.rewards.index` route.
3. THE Dashboard_Page SHALL display informational text explaining how to earn points (e.g., "Cara mendapatkan poin").

### Requirement 10: Footer Security and Support Bar

**User Story:** As a member, I want to see security assurance and a support contact option at the bottom of the dashboard, so that I feel confident using the platform.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL display a footer bar at the bottom of the dashboard content area.
2. THE Footer_Bar SHALL display a "Keamanan Terjamin" security notice with an appropriate icon.
3. THE Footer_Bar SHALL display a "Butuh bantuan?" text with a "Hubungi Support" button.
4. WHEN the member clicks "Hubungi Support", THE Footer_Bar SHALL navigate to a support contact mechanism.

### Requirement 11: Removal of Deprecated Sections

**User Story:** As a member, I want a clean dashboard without outdated or irrelevant sections, so that I can focus on what matters.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL NOT display the pricing information section that was previously shown.
2. THE Dashboard_Page SHALL NOT display the pending payment alert banner that was previously shown.
3. THE Dashboard_Page SHALL NOT display the failed transaction alert banner that was previously shown.
4. THE Dashboard_Page SHALL NOT display the recent transactions panel that was previously shown.

### Requirement 12: Three-Column Bottom Layout

**User Story:** As a member, I want the bottom section of the dashboard organized in three columns, so that I can scan performance, surveys, and rewards side by side.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL arrange the Performance Survey section, Survey Terbaru section, and Reward & Poin section in a three-column grid layout on desktop viewports.
2. WHILE the viewport width is below the desktop breakpoint, THE Dashboard_Page SHALL stack the three bottom sections vertically in a single column.

### Requirement 13: Responsive Design

**User Story:** As a member, I want the dashboard to work well on both desktop and mobile devices, so that I can access it from any device.

#### Acceptance Criteria

1. THE Dashboard_Page SHALL adapt its layout from multi-column to single-column when viewed on viewports narrower than 1024px.
2. THE Layout_Sidebar SHALL be hidden off-screen on mobile viewports and accessible via a hamburger menu toggle.
3. THE Statistics_Cards SHALL wrap into multiple rows on viewports narrower than 768px while maintaining readability.
4. THE Banner_Slider and Quick_Action_Cards SHALL stack vertically on viewports narrower than 1024px.
