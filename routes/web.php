<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\TabController;
use App\Http\Controllers\Admin\PartnerLogoController;
use App\Http\Controllers\Admin\CustomerStoryController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\LayananController as AdminLayananController;
use App\Http\Controllers\Admin\DiscountBannerController;
use App\Http\Controllers\Admin\DashboardBannerController;
use App\Http\Controllers\Admin\TestimoniController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SingaPayController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\SurveyController as UserSurveyController;
use App\Http\Controllers\User\TransactionController as UserTransactionController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\TransactionProgressController as AdminTransactionProgressController;
use App\Http\Controllers\Admin\ResponseController;
use App\Http\Controllers\Admin\SurveyManagementController;
use App\Http\Controllers\Admin\UserImpersonationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FaspayTestTransactionController;
use App\Http\Controllers\FaspayController;
use App\Http\Controllers\FormAnalyzerController;
use App\Http\Controllers\User\RewardController;
use App\Http\Controllers\User\AffiliateController;
use App\Http\Controllers\Admin\AffiliateWithdrawalController;
use App\Http\Controllers\Admin\RewardItemController;
use App\Http\Controllers\Admin\RewardRedemptionController;
use App\Services\SitemapService;

Route::get('/', [HomeController::class, 'index'])->name('landing');

Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Google OAuth
use App\Http\Controllers\Auth\GoogleAuthController;
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Password Reset via OTP (WhatsApp & Email)
use App\Http\Controllers\Auth\ForgotPasswordController;
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.send-otp');
Route::get('/verify-otp', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOtp'])->name('password.otp.resend');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('user.dashboard');
    
    Route::get('/analytics', [\App\Http\Controllers\User\AnalyticsController::class, 'index'])
        ->name('user.analytics');

    Route::get('/history', [TransactionController::class, 'history'])
        ->name('user.history');

    Route::get('/wallet', [\App\Http\Controllers\User\WalletController::class, 'index'])
        ->name('user.wallet.index');

    Route::get('/profile', [ProfileController::class, 'show'])->name('user.profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('user.profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::post('/profile/update-phone', [ProfileController::class, 'updatePhone'])->name('user.profile.update-phone');

    // User Survey Routes
    Route::prefix('my-surveys')->name('user.surveys.')->group(function () {
        Route::get('/', [UserSurveyController::class, 'index'])->name('index');
        Route::get('/create', [UserSurveyController::class, 'create'])->name('create');
        Route::post('/', [UserSurveyController::class, 'store'])->name('store');
        Route::get('/{survey}', [UserSurveyController::class, 'show'])->name('show');
        Route::get('/{survey}/edit', [UserSurveyController::class, 'edit'])->name('edit');
        Route::put('/{survey}', [UserSurveyController::class, 'update'])->name('update');
        Route::delete('/{survey}', [UserSurveyController::class, 'destroy'])->name('destroy');
        Route::get('/{survey}/export/pdf', [UserSurveyController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{survey}/export/responses-pdf', [UserSurveyController::class, 'exportResponsesPdf'])->name('export-responses-pdf');
    });

    // User Transaction Routes
    Route::prefix('my-transactions')->name('user.transactions.')->group(function () {
        Route::get('/', [UserTransactionController::class, 'index'])->name('index');
        Route::get('/{transaction}', [UserTransactionController::class, 'show'])->name('show');
    });

    // User Payments
    Route::prefix('payments')->name('user.payments.')->group(function () {
        Route::get('/{transaction}', [\App\Http\Controllers\User\PaymentController::class, 'show'])->name('show');
        Route::post('/{transaction}/process', [\App\Http\Controllers\User\PaymentController::class, 'process'])->name('process');
        Route::get('/{transaction}/success', [\App\Http\Controllers\User\PaymentController::class, 'success'])->name('success');
        Route::get('/{transaction}/failed', [\App\Http\Controllers\User\PaymentController::class, 'failed'])->name('failed');
    });

    // User Rewards
    Route::prefix('rewards')->name('user.rewards.')->group(function () {
        Route::get('/', [RewardController::class, 'index'])->name('index');
        Route::post('/{rewardItem}/redeem', [RewardController::class, 'redeem'])->name('redeem');
    });

    // User Topups
    Route::prefix('topups')->name('user.topups.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\TopupController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\User\TopupController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\User\TopupController::class, 'store'])->name('store');
    });

    // User Affiliate
    Route::prefix('affiliate')->name('user.affiliate.')->group(function () {
        Route::get('/', [AffiliateController::class, 'index'])->name('index');
        Route::post('/withdraw', [AffiliateController::class, 'withdraw'])->name('withdraw');
    });

    // User Notifications
    Route::post('/notifications/read-all', [\App\Http\Controllers\User\NotificationController::class, 'markAllAsRead'])->name('user.notifications.readAll');
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\User\NotificationController::class, 'markAsRead'])->name('user.notifications.read');

    // Stop admin impersonation and return to admin account
    Route::post('/impersonation/stop', [UserImpersonationController::class, 'stop'])->name('admin.impersonation.stop');
});

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/pricing', function () {
    $terms = \App\Models\Setting::where('key', 'terms_content')->value('value') ?? '';
    return view('pages.pricing', compact('terms'));
})->name('pricing');

Route::get('/price', function () {
    return redirect()->route('pricing');
})->name('price');

Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

Route::get('/set-sitemap', function () {
    app(SitemapService::class)->generate();

    return response()->json([
        'message' => 'Sitemaps generated successfully.',
    ]);
});



Route::post('/crm/customers/store', [HomeController::class, 'storeCustomer'])->name('crm.customers.store.user');
Route::post('/whatsapp/lead', [HomeController::class, 'storeCustomer'])->name('whatsapp.lead.store');
Route::post('/crm/customers', [CustomerController::class, 'store'])->name('crm.customers.store');
Route::get('/customer-form', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customer-form', [CustomerController::class, 'store'])->name('customers.store');
Route::post('/form-analyzer/preview', [FormAnalyzerController::class, 'preview'])
    ->name('form-analyzer.preview');

Route::middleware(['auth'])->group(function () {
    Route::resource('customers', CustomerController::class)->except(['create', 'store']);
});



Route::get('/cart', [TransactionController::class, 'cart'])
    ->name('cart.index')
    ->middleware('auth');

Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index')->middleware('auth');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category?}', [BlogController::class, 'category'])->name('blog.category');



// route dinamis halaman layanan
Route::get('/layanan/{slug}', [App\Http\Controllers\LayananController::class, 'show'])
    ->name('layanan.show');

// Faspay Return URL
Route::get('/transaction/faspay/return', [FaspayController::class, 'returnUrl'])->name('faspay.return');

Route::get('/{slug}', [BlogController::class, 'show'])->name('blog.show');


Route::middleware(['auth'])->group(function () {

    Route::resource('surveys', SurveyController::class);
    Route::post('surveys/transaction', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('surveys/{survey}/transaction', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('surveys/{survey}/transaction', [TransactionController::class, 'store'])->name('transactionss.store');

    Route::get('transactions/{transaction}/payment', [TransactionController::class, 'payment'])->name('transactions.payment');
    Route::post('transactions/{transaction}/payment', [TransactionController::class, 'processPayment'])->name('transactions.processPayment');
    Route::get('transactions/{transaction}/transfer', [TransactionController::class, 'showTransfer'])->name('transactions.showTransfer');
    Route::get('transactions/{transaction}/invoice', [TransactionController::class, 'invoice'])
        ->name('transactions.invoice');
    Route::get('transactions/{transaction}/download', [TransactionController::class, 'download'])
        ->name('transactions.download');
    Route::get('/transactions/{transaction}/progress', [App\Http\Controllers\TransactionProgressController::class, 'show'])->middleware('auth')->name('transactions.progress');
    // Halaman form upload
    Route::get('/transactions/{transaction}/payment-proof', [PaymentProofController::class, 'create'])
        ->name('payment-proofs.create');

    // Proses upload
    Route::post('/transactions/{transaction}/payment-proof', [PaymentProofController::class, 'store'])
        ->name('payment-proofs.store');

    Route::get('/transactions/{transaction}/qris-debug', [SingaPayController::class, 'generateQrisDebug']);

    Route::get('singapay/pay/{transaction}', [SingaPayController::class, 'pay'])->name('singapay.pay');
    Route::post('singapay/callback', [SingaPayController::class, 'callback'])->name('singapay.callback');
});


// Login Admin
Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Dashboard (hanya admin)
Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/pilih-dashboard', function () {
        return view('admin.auth.pilih-dashboard');
    })->name('pilih-dashboard');

    Route::get('/pilih-client', [CRMController::class, 'clientMenu'])->name('pilih-client');

    // Hidden log viewer — NOT in sidebar/dashboard, accessible only via direct URL
    Route::get('/x9k7-system-logs', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('admin.logs.index');
    Route::get('/x9k7-system-logs/download', [\App\Http\Controllers\Admin\LogViewerController::class, 'download'])->name('admin.logs.download');
    Route::post('/x9k7-system-logs/clear', [\App\Http\Controllers\Admin\LogViewerController::class, 'clear'])->name('admin.logs.clear');

    // Hidden activity log viewer — NOT in sidebar/dashboard
    Route::get('/x9k7-activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::post('/x9k7-activity-logs/clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'clear'])->name('admin.activity-logs.clear');


    Route::get('/crm/dashboard', [CRMController::class, 'index'])->name('crm.dashboard');
    Route::get('/crm/manage-users', [CRMController::class, 'customerAlready'])->name('crm.manage-users');
    Route::get('/crm/customer-already', [CRMController::class, 'customerAlready'])->name('crm.customer-already');
    Route::get('/crm/manage-users/{user}', [CRMController::class, 'showManageUser'])->name('crm.manage-users.show');
    Route::post('/users/{user}/impersonate', [UserImpersonationController::class, 'impersonate'])->name('admin.users.impersonate');

    // Follow Up khusus status closed
    Route::get('/followups/closed', [FollowUpController::class, 'closed'])->name('followups.closed');

    // CRUD utama
    Route::resource('followups', FollowUpController::class);


    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // SEO Management
    Route::get('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('admin.seo.index');
    Route::put('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'update'])->name('admin.seo.update');

    // Syarat & Ketentuan
    Route::get('/terms', [App\Http\Controllers\Admin\SettingController::class, 'terms'])->name('admin.terms.edit');
    Route::post('/terms', [App\Http\Controllers\Admin\SettingController::class, 'updateTerms'])->name('admin.terms.update');

    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Reward Items CRUD
    Route::resource('reward-items', RewardItemController::class)->except(['show'])->names('admin.reward-items');

    // Reward Redemptions
    Route::get('reward-redemptions', [RewardRedemptionController::class, 'index'])->name('admin.reward-redemptions.index');
    Route::patch('reward-redemptions/{redemption}/status', [RewardRedemptionController::class, 'updateStatus'])->name('admin.reward-redemptions.update-status');

    // Affiliate Withdrawals
    Route::prefix('affiliate-withdrawals')->name('admin.affiliate-withdrawals.')->group(function () {
        Route::get('/', [AffiliateWithdrawalController::class, 'index'])->name('index');
        Route::post('/{withdrawal}/approve', [AffiliateWithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject', [AffiliateWithdrawalController::class, 'reject'])->name('reject');
    });

    // Tab Management (CRUD)
    Route::get('tabs', [TabController::class, 'index'])->name('tabs.index');
    Route::get('tabs/create', [TabController::class, 'create'])->name('tabs.create');
    Route::post('tabs', [TabController::class, 'store'])->name('tabs.store');
    Route::get('tabs/{tab}/edit', [TabController::class, 'edit'])->name('tabs.edit');
    Route::put('tabs/{tab}', [TabController::class, 'update'])->name('tabs.update');
    Route::delete('tabs/{tab}', [TabController::class, 'destroy'])->name('tabs.destroy');

    // Partner Logos (CRUD)
    Route::get('partner-logos', [PartnerLogoController::class, 'index'])->name('partner-logos.index');
    Route::get('partner-logos/create', [PartnerLogoController::class, 'create'])->name('partner-logos.create');
    Route::post('partner-logos', [PartnerLogoController::class, 'store'])->name('partner-logos.store');
    Route::get('partner-logos/{partnerLogo}/edit', [PartnerLogoController::class, 'edit'])->name('partner-logos.edit');
    Route::put('partner-logos/{partnerLogo}', [PartnerLogoController::class, 'update'])->name('partner-logos.update');
    Route::delete('partner-logos/{partnerLogo}', [PartnerLogoController::class, 'destroy'])->name('partner-logos.destroy');

    // Testimoni Images
    Route::get('testimoni', [TestimoniController::class, 'index'])->name('admin.testimoni.index');
    Route::post('testimoni', [TestimoniController::class, 'store'])->name('admin.testimoni.store');
    Route::delete('testimoni/{testimoni}', [TestimoniController::class, 'destroy'])->name('admin.testimoni.destroy');
    Route::post('testimoni/{testimoni}/toggle', [TestimoniController::class, 'toggleActive'])->name('admin.testimoni.toggle');

    // Customer Stories CRUD
    Route::get('customer-stories', [CustomerStoryController::class, 'index'])->name('customer-stories.index');
    Route::get('customer-stories/create', [CustomerStoryController::class, 'create'])->name('customer-stories.create');
    Route::post('customer-stories', [CustomerStoryController::class, 'store'])->name('customer-stories.store');
    Route::get('customer-stories/{customerStory}/edit', [CustomerStoryController::class, 'edit'])->name('customer-stories.edit');
    Route::put('customer-stories/{customerStory}', [CustomerStoryController::class, 'update'])->name('customer-stories.update');
    Route::delete('customer-stories/{customerStory}', [CustomerStoryController::class, 'destroy'])->name('customer-stories.destroy');

     // Article CRUD
     // ✅ Semua route berada di bawah /admin/...
     Route::get('articles', [ArticleController::class, 'index'])->name('admin.articles.index');
     Route::get('articles/create', [ArticleController::class, 'create'])->name('admin.articles.create');
     Route::post('articles', [ArticleController::class, 'store'])->name('admin.articles.store');
     Route::get('articles/{article}/edit', [ArticleController::class, 'edit'])->name('admin.articles.edit');
     Route::put('articles/{article}', [ArticleController::class, 'update'])->name('admin.articles.update');
     Route::delete('articles/{id}', [ArticleController::class, 'destroy'])->name('admin.articles.destroy');

    Route::resource('layanan', AdminLayananController::class)->names('admin.layanan');
    Route::resource('discount-banners', DiscountBannerController::class)
        ->except(['show'])
        ->names('admin.discount-banners');

    Route::resource('dashboard-banners', DashboardBannerController::class)
        ->except(['show'])
        ->names('admin.dashboard-banners');
    Route::post('dashboard-banners/{dashboardBanner}/toggle', [DashboardBannerController::class, 'toggle'])
        ->name('admin.dashboard-banners.toggle');

    // Halaman daftar transaksi paid
    Route::get('/transactions/progress', [AdminTransactionProgressController::class, 'index'])
        ->name('admin.transactions.progress.index');

    // Form update progress
    Route::get('/transactions/{transaction}/progress', [AdminTransactionProgressController::class, 'edit'])
        ->name('admin.transactions.progress.edit');

    // Aksi update progress
    Route::put('/transactions/{transaction}/progress', [AdminTransactionProgressController::class, 'update'])
        ->name('admin.transactions.progress.update');

    Route::resource('transactions', AdminTransactionController::class)->names('admin.transactions');

    Route::get('/surveys/manage', [SurveyManagementController::class, 'index'])
        ->name('admin.surveys.manage');

    Route::post('/surveys/{survey}/respondents', [SurveyManagementController::class, 'storeRespondent'])
        ->name('admin.surveys.respondents.store');

    Route::put('/surveys/{survey}/respondents/{response}', [SurveyManagementController::class, 'updateRespondent'])
        ->name('admin.surveys.respondents.update');

    Route::resource('responses', ResponseController::class)->names('admin.responses');

    Route::get('payment-proofs', [\App\Http\Controllers\Admin\PaymentProofController::class, 'index'])->name('admin.payment-proofs.index');
});

// ===== FASPAY XPRESS INTEGRATION ROUTES =====

// Test Transaction Routes (requires auth)
Route::middleware(['auth'])->prefix('faspay/test')->name('faspay.')->group(function () {
    Route::get('transactions', [FaspayTestTransactionController::class, 'index'])->name('test-transaction.index');
    Route::get('transactions/create', [FaspayTestTransactionController::class, 'create'])->name('test-transaction.create');
    Route::post('transactions', [FaspayTestTransactionController::class, 'store'])->name('test-transaction.store');
    Route::get('transactions/{testTransaction}', [FaspayTestTransactionController::class, 'show'])->name('test-transaction.show');
    Route::get('transactions/{testTransaction}/payment', [FaspayTestTransactionController::class, 'payment'])->name('test-transaction.payment');
    Route::post('transactions/{testTransaction}/payment', [FaspayTestTransactionController::class, 'processPayment'])->name('test-transaction.process-payment');
    Route::get('transactions/{testTransaction}/success', [FaspayTestTransactionController::class, 'success'])->name('test-transaction.success');
    Route::delete('transactions/{testTransaction}', [FaspayTestTransactionController::class, 'destroy'])->name('test-transaction.destroy');
    Route::post('transactions/{testTransaction}/simulate', [FaspayTestTransactionController::class, 'simulatePayment'])->name('test-transaction.simulate');
});

// Faspay Webhook Routes (NO auth required - Faspay will call these)
Route::post('/api/webhook/faspay/notification', [FaspayController::class, 'notification'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
    ->name('faspay.notification');

// Return URL Faspay tersedia di /transaction/faspay/return

// Faspay Debug Routes (dev only)
Route::middleware(['auth'])->prefix('faspay')->group(function () {
    Route::get('debug', [FaspayController::class, 'debugConfig'])->name('faspay.debug');
    Route::get('list-transactions', [FaspayController::class, 'listTransactions'])->name('faspay.list-transactions');
});

// ===== SINGAPAY TEST TRANSACTION ROUTES =====
Route::middleware(['auth'])->prefix('singapay/test')->name('singapay.test.')->group(function () {
    Route::get('transactions', [\App\Http\Controllers\SingaPayTestController::class, 'index'])->name('index');
    Route::get('transactions/create', [\App\Http\Controllers\SingaPayTestController::class, 'create'])->name('create');
    Route::post('transactions', [\App\Http\Controllers\SingaPayTestController::class, 'store'])->name('store');
    Route::get('transactions/{singaPayTestTransaction}', [\App\Http\Controllers\SingaPayTestController::class, 'show'])->name('show');
    Route::get('transactions/{singaPayTestTransaction}/payment', [\App\Http\Controllers\SingaPayTestController::class, 'payment'])->name('payment');
    Route::post('transactions/{singaPayTestTransaction}/payment', [\App\Http\Controllers\SingaPayTestController::class, 'processPayment'])->name('process');
    Route::get('transactions/{singaPayTestTransaction}/success', [\App\Http\Controllers\SingaPayTestController::class, 'success'])->name('success');
    Route::get('transactions/{singaPayTestTransaction}/check-status', [\App\Http\Controllers\SingaPayTestController::class, 'checkStatus'])->name('check-status');
    Route::delete('transactions/{singaPayTestTransaction}', [\App\Http\Controllers\SingaPayTestController::class, 'destroy'])->name('destroy');
});

// SingaPay Test Webhook (NO auth, NO CSRF)
Route::post('/api/webhook/singapay/test', [\App\Http\Controllers\SingaPayTestController::class, 'webhook'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
    ->name('singapay.test.webhook');
