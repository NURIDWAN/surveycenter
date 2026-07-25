# Requirements Document

## Introduction

Survey Center Indonesia is a respondent-facing extension to the existing Grapadi Survey platform. The feature introduces a "responden" role that allows users to register, browse available surveys, fill Google Forms externally, upload proof screenshots, and receive Rupiah saldo after admin verification. The platform manages only the proof-of-completion workflow; survey answers remain on Google Forms.

## Glossary

- **Platform**: The Grapadi Survey Laravel application
- **Responden**: A user with the responden role who fills surveys and earns saldo
- **Survey_Creator**: A user who creates and publishes surveys on the platform
- **Admin**: A user with is_admin=true who manages verifications and platform operations
- **Survey**: A published survey record containing a Google Form link, reward amount, and criteria
- **Survey_Filling**: A record representing a respondent's participation in a specific survey, tracking status from start through verification
- **Proof_Screenshot**: An uploaded image file (JPG/PNG) serving as evidence of survey completion
- **Saldo**: Rupiah-denominated balance stored in the respondent's Wallet
- **Wallet**: The existing wallet system (Wallet + WalletTransaction models) that stores user balance
- **Demographic_Profile**: Respondent data including tanggal lahir, jenis kelamin, provinsi, kota, pendidikan, and pekerjaan
- **Rejection_Reason**: A predefined reason selected by Admin when rejecting a Survey_Filling
- **Minimum_Threshold**: The minimum saldo amount a Responden must reach before requesting withdrawal

## Requirements

### Requirement 1: Respondent Registration

**User Story:** As a new user, I want to register as a responden with minimal information, so that I can start browsing and filling surveys quickly.

#### Acceptance Criteria

1. WHEN a user submits the responden registration form with nama, email, password, and nomor WhatsApp, THE Platform SHALL create a new user account with the responden role.
2. THE Platform SHALL validate that email is unique across all users in the system.
3. THE Platform SHALL validate that the password meets a minimum length of 8 characters.
4. THE Platform SHALL validate that nomor WhatsApp contains only digits and is between 10 and 15 characters.
5. IF the registration form is submitted with missing or invalid fields, THEN THE Platform SHALL display specific validation error messages for each invalid field.
6. WHEN a responden account is created, THE Platform SHALL create an associated Wallet record with a zero balance.

### Requirement 2: Respondent Authentication

**User Story:** As a responden, I want to log in using my email and password, so that I can access my dashboard and available surveys.

#### Acceptance Criteria

1. WHEN a responden submits valid email and password credentials, THE Platform SHALL authenticate the user and redirect to the responden dashboard.
2. IF a responden submits invalid credentials, THEN THE Platform SHALL display an authentication error message without revealing which field is incorrect.
3. THE Platform SHALL maintain separate authentication flows for responden and admin users.
4. WHEN a responden is authenticated, THE Platform SHALL restrict access to responden-only routes and prevent access to admin or survey-creator routes.

### Requirement 3: Demographic Profile Completion

**User Story:** As a responden, I want to complete my demographic profile, so that the system can match me with relevant surveys.

#### Acceptance Criteria

1. WHEN a responden navigates to the profile page, THE Platform SHALL display a form with fields for tanggal lahir, jenis kelamin, provinsi, kota, pendidikan, and pekerjaan.
2. WHEN a responden submits the demographic profile form, THE Platform SHALL save all provided fields to the user record.
3. THE Platform SHALL validate that tanggal lahir is a valid date in the past.
4. THE Platform SHALL validate that jenis kelamin is one of the predefined options (Laki-laki, Perempuan).
5. THE Platform SHALL validate that provinsi and kota are valid Indonesian administrative regions.
6. THE Platform SHALL allow partial profile updates without requiring all fields to be filled simultaneously.

### Requirement 4: Respondent Dashboard

**User Story:** As a responden, I want to see my saldo and survey statistics on a dashboard, so that I can track my earnings and activity.

#### Acceptance Criteria

1. WHEN a responden accesses the dashboard, THE Platform SHALL display the current saldo balance in Rupiah format.
2. WHEN a responden accesses the dashboard, THE Platform SHALL display counts for survey tersedia, menunggu verifikasi, and disetujui.
3. WHEN a responden accesses the dashboard, THE Platform SHALL display a list of available surveys that match the responden's demographic criteria.
4. THE Platform SHALL format saldo amounts using Indonesian Rupiah notation (e.g., Rp 50.000).

### Requirement 5: Survey Browsing and Detail

**User Story:** As a responden, I want to view survey details including reward and criteria, so that I can decide which surveys to fill.

#### Acceptance Criteria

1. WHEN a responden views the survey list, THE Platform SHALL display only surveys with status active and remaining respondent slots available.
2. WHEN a responden views a survey detail page, THE Platform SHALL display the survey title, description, reward amount in Rupiah, estimated completion time, eligibility criteria, and deadline.
3. WHEN a responden views a survey detail page, THE Platform SHALL display a "Mulai Survey" button that initiates the survey filling process.
4. THE Platform SHALL exclude surveys that the responden has already started or completed from the available survey list.
5. WHILE a survey has reached its maximum respondent count, THE Platform SHALL hide that survey from the available survey list.

### Requirement 6: Survey Filling Initiation

**User Story:** As a responden, I want to start a survey and be redirected to the Google Form, so that I can fill in the survey externally.

#### Acceptance Criteria

1. WHEN a responden clicks "Mulai Survey", THE Platform SHALL create a Survey_Filling record with status "Sedang Dikerjakan".
2. WHEN a Survey_Filling record is created, THE Platform SHALL redirect the responden to the external Google Form URL in a new browser tab.
3. THE Platform SHALL prevent a responden from creating multiple Survey_Filling records for the same survey.
4. WHEN a responden returns from the Google Form, THE Platform SHALL display the upload proof page for that Survey_Filling.

### Requirement 7: Proof Upload

**User Story:** As a responden, I want to upload a screenshot proving I completed the survey, so that admin can verify my submission.

#### Acceptance Criteria

1. WHEN a responden uploads a Proof_Screenshot, THE Platform SHALL accept files in JPG or PNG format only.
2. THE Platform SHALL enforce a maximum file size of 2MB for Proof_Screenshot uploads.
3. WHEN a responden submits the proof upload form with a valid screenshot and optional catatan, THE Platform SHALL update the Survey_Filling status to "Menunggu Verifikasi".
4. THE Platform SHALL store the uploaded Proof_Screenshot file in a secure storage location with a unique filename.
5. IF a responden submits the proof upload form without a screenshot file, THEN THE Platform SHALL display a validation error requiring the screenshot.
6. WHEN the proof upload is successful, THE Platform SHALL display a confirmation page showing the "Menunggu Verifikasi" status.

### Requirement 8: Admin Verification Dashboard

**User Story:** As an admin, I want to view all pending survey fillings, so that I can verify respondent submissions efficiently.

#### Acceptance Criteria

1. WHEN an admin accesses the verification dashboard, THE Platform SHALL display a table with columns for Survey name, Responden name, Waktu Kirim, Status, and Aksi.
2. THE Platform SHALL allow the admin to filter the verification table by status (Menunggu Verifikasi, Disetujui, Ditolak).
3. WHEN an admin clicks on a Survey_Filling entry, THE Platform SHALL display the detail page showing survey name, respondent info, respondent email, waktu kirim, Google Form link, and uploaded Proof_Screenshot.
4. THE Platform SHALL sort the verification table by waktu kirim in descending order by default.

### Requirement 9: Admin Approval

**User Story:** As an admin, I want to approve a respondent's survey filling, so that the respondent receives their earned saldo.

#### Acceptance Criteria

1. WHEN an admin clicks the "Approve" button on a Survey_Filling detail page, THE Platform SHALL update the Survey_Filling status to "Disetujui".
2. WHEN a Survey_Filling status changes to "Disetujui", THE Platform SHALL add the survey reward amount to the responden's Wallet balance.
3. WHEN saldo is added to the responden's Wallet, THE Platform SHALL create a WalletTransaction record with type credit, the reward amount, and a reference to the Survey_Filling.
4. THE Platform SHALL allow approval only for Survey_Filling records with status "Menunggu Verifikasi".

### Requirement 10: Admin Rejection

**User Story:** As an admin, I want to reject a respondent's survey filling with a reason, so that the respondent understands why their submission was not accepted.

#### Acceptance Criteria

1. WHEN an admin clicks the "Reject" button on a Survey_Filling detail page, THE Platform SHALL display a rejection form with a Rejection_Reason dropdown and optional additional notes field.
2. THE Platform SHALL require the admin to select a Rejection_Reason from the predefined dropdown before confirming rejection.
3. WHEN an admin confirms rejection, THE Platform SHALL update the Survey_Filling status to "Ditolak" and store the selected Rejection_Reason and optional notes.
4. THE Platform SHALL allow rejection only for Survey_Filling records with status "Menunggu Verifikasi".
5. WHEN a Survey_Filling is rejected, THE Platform SHALL make the rejection reason visible to the responden on their survey history.

### Requirement 11: Survey Filling Status Tracking

**User Story:** As a responden, I want to see the status of all my survey fillings, so that I can track progress and view rejection reasons.

#### Acceptance Criteria

1. WHEN a responden views their survey history, THE Platform SHALL display all Survey_Filling records with their current status (Tersedia, Sedang Dikerjakan, Menunggu Verifikasi, Disetujui, Ditolak).
2. WHILE a Survey_Filling has status "Ditolak", THE Platform SHALL display the Rejection_Reason and any additional notes to the responden.
3. WHILE a Survey_Filling has status "Disetujui", THE Platform SHALL display the earned reward amount next to the entry.
4. THE Platform SHALL display Survey_Filling records sorted by most recent submission first.

### Requirement 12: Survey Reward Configuration

**User Story:** As a survey creator, I want to set a reward amount for my survey, so that respondents know how much saldo they will earn.

#### Acceptance Criteria

1. WHEN a survey creator creates or edits a survey, THE Platform SHALL provide a field to set the reward amount in Rupiah.
2. THE Platform SHALL validate that the reward amount is a positive integer value.
3. THE Platform SHALL display the reward amount on the survey detail page visible to respondents.
4. THE Platform SHALL use the configured reward amount when crediting the responden's Wallet upon approval.

### Requirement 13: Saldo Withdrawal

**User Story:** As a responden, I want to withdraw my saldo to a bank account or e-wallet, so that I can receive my earnings.

#### Acceptance Criteria

1. WHEN a responden requests a withdrawal, THE Platform SHALL require the responden's saldo to meet or exceed the Minimum_Threshold.
2. IF a responden requests a withdrawal with saldo below the Minimum_Threshold, THEN THE Platform SHALL display a message indicating the minimum required amount.
3. WHEN a responden submits a withdrawal request, THE Platform SHALL require bank account or e-wallet details (provider name, account number, account holder name).
4. WHEN a withdrawal request is submitted, THE Platform SHALL create a pending withdrawal record and deduct the requested amount from the responden's available saldo.
5. THE Platform SHALL create a WalletTransaction record with type debit for the withdrawal amount.

### Requirement 14: Respondent Role Management

**User Story:** As a platform user, I want to have both survey creator and responden capabilities, so that I can both create surveys and fill other users' surveys.

#### Acceptance Criteria

1. THE Platform SHALL support users having both survey creator and responden roles simultaneously.
2. THE Platform SHALL prevent a responden from filling surveys they have created themselves.
3. WHEN a user has both roles, THE Platform SHALL provide navigation to switch between survey creator and responden interfaces.
4. THE Platform SHALL store the responden role as a distinct attribute on the User model alongside the existing is_admin flag.

### Requirement 15: Survey Eligibility Criteria

**User Story:** As a survey creator, I want to set demographic criteria for my survey, so that only matching respondents can participate.

#### Acceptance Criteria

1. WHEN a survey creator configures a survey, THE Platform SHALL allow setting eligibility criteria based on jenis kelamin, age range, provinsi, kota, pendidikan, and pekerjaan.
2. WHEN a responden browses available surveys, THE Platform SHALL display only surveys whose eligibility criteria match the responden's Demographic_Profile.
3. IF a responden's Demographic_Profile is incomplete, THEN THE Platform SHALL display a prompt to complete the profile before accessing surveys with demographic criteria.
4. THE Platform SHALL allow surveys with no eligibility criteria to be visible to all respondents regardless of profile completeness.
