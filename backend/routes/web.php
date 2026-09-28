<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EstablishmentController;
use App\Http\Controllers\Admin\EstablishmentApprovalController;
use App\Http\Controllers\Admin\EstablishmentTypeController;
use App\Http\Controllers\Admin\EstablishmentSizeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\ServiceFeeAgencyController;
use App\Http\Controllers\Admin\RevenueRuleController;
use App\Http\Controllers\Admin\RevenueHeadController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PublicEstablishmentController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SecurityEnforcementController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

use App\Http\Controllers\Admin\MarketController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\ShopAllocationController;
use App\Http\Controllers\PublicShopApplicationController;

Route::get('/', [PublicEstablishmentController::class, 'landing'])->name('public.landing');
Route::get('/search', [PublicEstablishmentController::class, 'search'])->name('public.search');
Route::get('/map', [PublicEstablishmentController::class, 'map'])->name('public.map');
Route::get('/how-to-pay', [PublicEstablishmentController::class, 'howToPay'])->name('public.how-to-pay');
Route::get('/faq', [PublicEstablishmentController::class, 'faq'])->name('public.faq');
Route::post('/public/pay', [PublicEstablishmentController::class, 'pay'])->name('public.pay');
Route::get('/public/payment-success/{reference}', [PublicEstablishmentController::class, 'success'])->name('public.payment-success');
Route::get('/public/payment-verify/{reference}', [PublicEstablishmentController::class, 'verifyReceipt'])->name('public.payment-verify');

// Public Shop Allocation Application (Kanogis-inspired OTP & Tracking workflow)
Route::prefix('shop-application')->name('public.shop-application.')->group(function () {
    Route::get('/', [PublicShopApplicationController::class, 'index'])->name('index');
    Route::post('/initiate-otp', [PublicShopApplicationController::class, 'initiateOtp'])->name('initiate-otp');
    Route::post('/verify-otp', [PublicShopApplicationController::class, 'verifyOtp'])->name('verify-otp');
    Route::post('/submit', [PublicShopApplicationController::class, 'submit'])->name('submit');
    Route::get('/track', [PublicShopApplicationController::class, 'track'])->name('track');
    Route::get('/track/{application_no}', [PublicShopApplicationController::class, 'trackStatus'])->name('track-status');
    Route::get('/track/{application_no}/verify', [PublicShopApplicationController::class, 'showVerifyTracking'])->name('track-verify');
    Route::post('/track/{application_no}/send-otp', [PublicShopApplicationController::class, 'sendTrackingOtp'])->name('track-send-otp');
    Route::post('/track/{application_no}/verify-otp', [PublicShopApplicationController::class, 'verifyTrackingOtp'])->name('track-verify-otp');
    Route::get('/update/{application_no}', [PublicShopApplicationController::class, 'edit'])->name('edit');
    Route::post('/update/{application_no}', [PublicShopApplicationController::class, 'update'])->name('update');
    Route::get('/certificate/{application_no}', [PublicShopApplicationController::class, 'certificate'])->name('certificate');
    Route::get('/checkout/{application_no}', [PublicShopApplicationController::class, 'checkout'])->name('checkout');
    Route::get('/payment-callback/{application_no}', [PublicShopApplicationController::class, 'paymentCallback'])->name('payment-callback');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');

// Public Email Verification
Route::match(['get', 'post'], '/verify-email/{id}/{hash}', [UserController::class, 'verifyEmailLink'])->name('verification.verify');
Route::get('/verify-email/notice', [UserController::class, 'showVerificationNotice'])->name('verification.notice');
Route::post('/verify-email/resend', [UserController::class, 'resendVerification'])->name('verification.resend');

// Two-Factor Authentication Challenge
Route::get('/2fa-challenge', [LoginController::class, 'showChallenge'])->name('2fa.challenge');
Route::post('/2fa-challenge', [LoginController::class, 'verifyChallenge'])->name('2fa.verify');
Route::post('/2fa-recovery', [LoginController::class, 'verifyRecovery'])->name('2fa.recovery');

// Mandatory Security Enforcement
Route::middleware('auth')->group(function () {
    Route::get('/security/password-change', [SecurityEnforcementController::class, 'showPasswordChange'])->name('security.password');
    Route::post('/security/password-update', [SecurityEnforcementController::class, 'updatePassword'])->name('security.password.update');
    Route::get('/security/2fa-setup', [SecurityEnforcementController::class, 'show2faSetup'])->name('security.2fa');
    Route::post('/security/2fa-confirm', [SecurityEnforcementController::class, 'confirm2fa'])->name('security.2fa.confirm');
});

Route::middleware(['auth', 'security_policy'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile');
    Route::put('/profile/info', [ProfileController::class, 'updateInfo'])->name('admin.profile.update-info');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
    Route::get('/profile/2fa', [ProfileController::class, 'showTwoFactor'])->name('admin.profile.2fa');
    Route::post('/profile/2fa/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('admin.profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disableTwoFactor'])->name('admin.profile.2fa.disable');

    // Establishment Approvals
    Route::middleware('permission:establishment approval')->group(function () {
        Route::get('/establishments/approvals', [EstablishmentApprovalController::class, 'index'])->name('admin.approvals.index');
        Route::get('/establishments/approvals/{id}', [EstablishmentApprovalController::class, 'show'])->whereNumber('id')->name('admin.approvals.show');
        Route::post('/establishments/approvals/{id}/process', [EstablishmentApprovalController::class, 'process'])->whereNumber('id')->name('admin.approvals.process');
    });

    // Establishment Management (CRUD)
    Route::get('/establishments', [EstablishmentController::class, 'index'])->name('admin.establishments.index');
    Route::get('/establishments/approved', [EstablishmentController::class, 'approved'])->name('admin.establishments.approved');
    Route::get('/establishments/invalid', [EstablishmentController::class, 'invalid'])->name('admin.establishments.invalid');
    Route::get('/establishments/unpaid', [EstablishmentController::class, 'unpaid'])->name('admin.establishments.unpaid');
    Route::get('/establishments/create', [EstablishmentController::class, 'create'])->name('admin.establishments.create');
    Route::post('/establishments', [EstablishmentController::class, 'store'])->name('admin.establishments.store');
    Route::get('/establishments/{id}/edit', [EstablishmentController::class, 'edit'])->whereNumber('id')->name('admin.establishments.edit');
    Route::put('/establishments/{id}', [EstablishmentController::class, 'update'])->whereNumber('id')->name('admin.establishments.update');
    Route::get('/establishments/{id}', [EstablishmentController::class, 'show'])->whereNumber('id')->name('admin.establishments.show');
    Route::get('/establishments/{id}/details', [EstablishmentController::class, 'details'])->whereNumber('id')->name('admin.establishments.details');
    Route::delete('/establishments/{id}', [EstablishmentController::class, 'destroy'])->whereNumber('id')->name('admin.establishments.destroy');

    // Owner Lookup API (Internal)
    Route::get('/owners/search', [\App\Http\Controllers\Api\V1\OwnerLookupController::class, 'search'])->name('admin.owners.search');

    // Markets Management (UI/index.html #markets)
    Route::middleware('permission:view markets')->group(function () {
        Route::get('/markets', [MarketController::class, 'index'])->name('admin.markets.index');
        Route::get('/markets/{market}', [MarketController::class, 'show'])->name('admin.markets.show');
    });
    Route::middleware('permission:create market')->group(function () {
        Route::get('/markets/create', [MarketController::class, 'create'])->name('admin.markets.create');
        Route::post('/markets', [MarketController::class, 'store'])->name('admin.markets.store');
    });
    Route::middleware('permission:edit market')->group(function () {
        Route::get('/markets/{market}/edit', [MarketController::class, 'edit'])->name('admin.markets.edit');
        Route::put('/markets/{market}', [MarketController::class, 'update'])->name('admin.markets.update');
    });
    Route::middleware('permission:delete market')->group(function () {
        Route::delete('/markets/{market}', [MarketController::class, 'destroy'])->name('admin.markets.destroy');
    });

    // Shop Inventory Management (UI/index.html #shops)
    Route::middleware('permission:view shops')->group(function () {
        Route::get('/shops', [ShopController::class, 'index'])->name('admin.shops.index');
        Route::get('/shops/{shop}', [ShopController::class, 'show'])->name('admin.shops.show');
    });
    Route::middleware('permission:create shop')->group(function () {
        Route::get('/shops/create', [ShopController::class, 'create'])->name('admin.shops.create');
        Route::post('/shops', [ShopController::class, 'store'])->name('admin.shops.store');
    });
    Route::middleware('permission:edit shop')->group(function () {
        Route::get('/shops/{shop}/edit', [ShopController::class, 'edit'])->name('admin.shops.edit');
        Route::put('/shops/{shop}', [ShopController::class, 'update'])->name('admin.shops.update');
    });
    Route::middleware('permission:delete shop')->group(function () {
        Route::delete('/shops/{shop}', [ShopController::class, 'destroy'])->name('admin.shops.destroy');
    });

    // Shop Allocations & 7-Stage Workflow (UI/index.html #allocations)
    Route::prefix('allocations')->name('admin.allocations.')->group(function () {
        Route::middleware('permission:view allocations')->group(function () {
            Route::get('/', [ShopAllocationController::class, 'index'])->name('index');
            Route::get('/{allocation}', [ShopAllocationController::class, 'show'])->name('show');
            Route::get('/{allocation}/card', [ShopAllocationController::class, 'card'])->name('card');
        });
        Route::middleware('permission:review allocation|approve allocation|execute allocation')->group(function () {
            Route::post('/{allocation}/review', [ShopAllocationController::class, 'processReview'])->name('review');
            Route::post('/{allocation}/request-update', [ShopAllocationController::class, 'requestUpdate'])->name('request-update');
            Route::post('/{allocation}/revert', [ShopAllocationController::class, 'revert'])->name('revert');
            Route::post('/{allocation}/reopen', [ShopAllocationController::class, 'reopen'])->name('reopen');
            Route::post('/{allocation}/reject', [ShopAllocationController::class, 'reject'])->name('reject');
        });
        Route::middleware('permission:approve allocation')->group(function () {
            Route::post('/{allocation}/approve', [ShopAllocationController::class, 'processApproval'])->name('approve');
        });
        Route::middleware('permission:execute allocation')->group(function () {
            Route::post('/{allocation}/allocate', [ShopAllocationController::class, 'processAllocation'])->name('allocate');
            Route::post('/{allocation}/payment', [ShopAllocationController::class, 'processPayment'])->name('payment');
            Route::post('/{allocation}/verify-payment', [ShopAllocationController::class, 'verifyPayment'])->name('verify-payment');
            Route::post('/{allocation}/resend-payment-notice', [ShopAllocationController::class, 'resendPaymentNotice'])->name('resend-payment-notice');
            Route::post('/{allocation}/complete', [ShopAllocationController::class, 'processComplete'])->name('complete');
        });
    });

    // System Setup
    Route::prefix('setup')->name('admin.setup.')->middleware('role:super-admin')->group(function () {
        Route::resource('establishment-types', EstablishmentTypeController::class)->except(['show', 'create', 'edit']);
        Route::resource('establishment-sizes', EstablishmentSizeController::class)->except(['show', 'create', 'edit']);
    });

    // User Management
    Route::get('users/trashed', [UserController::class, 'trashed'])->name('admin.users.trashed');
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('admin.users.force-delete');
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('users/{id}/resend-welcome', [UserController::class, 'resendWelcome'])->name('admin.users.resend-welcome');
    Route::post('users/{id}/resend-verification', [UserController::class, 'resendVerificationAdmin'])->name('admin.users.resend-verification');
    Route::post('users/{id}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('admin.users.reset-2fa');
    Route::post('users/{id}/verify-email', [UserController::class, 'verifyEmail'])->name('admin.users.verify-email');
    Route::post('users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::get('users/{id}/auth-logs', [UserController::class, 'authLogs'])->name('admin.users.auth-logs');
    Route::get('users/all-auth-logs', [UserController::class, 'allAuthLogs'])->name('admin.users.all-auth-logs');
    Route::post('users/{id}/send-email', [UserController::class, 'sendCustomEmail'])->name('admin.users.send-custom-email');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('admin.users.bulk-action');
    Route::post('users/{id}/unlock', [UserController::class, 'unlock'])->name('admin.users.unlock');
    Route::resource('users', UserController::class)->names('admin.users')->middleware('permission:manage users');
    Route::resource('roles', RoleController::class)->names('admin.roles')->middleware('permission:manage roles');
    
    // Agencies & Revenue Rules (Super Admin Only)
    Route::middleware(['role:super-admin'])->group(function () {
        Route::post('agencies/{agency}/retry-paystack', [AgencyController::class, 'retryPaystack'])->name('admin.agencies.retry-paystack');
        Route::post('agencies/{agency}/retry-monnify', [AgencyController::class, 'retryMonnify'])->name('admin.agencies.retry-monnify');
        Route::post('agencies/{agency}/manual-link-paystack', [AgencyController::class, 'manualLinkPaystack'])->name('admin.agencies.manual-link-paystack');
        Route::post('agencies/{agency}/manual-link-monnify', [AgencyController::class, 'manualLinkMonnify'])->name('admin.agencies.manual-link-monnify');
        Route::resource('agencies', AgencyController::class)->names('admin.agencies');
        Route::resource('revenue-heads', RevenueHeadController::class)->names('admin.revenue-heads');
        Route::redirect('revenue-rules', '/admin/revenue-heads');

        Route::post('service-fee-agencies/{agency}/retry-paystack', [ServiceFeeAgencyController::class, 'retryPaystack'])->name('admin.service-fee-agencies.retry-paystack');
        Route::post('service-fee-agencies/{agency}/retry-monnify', [ServiceFeeAgencyController::class, 'retryMonnify'])->name('admin.service-fee-agencies.retry-monnify');
        Route::post('service-fee-agencies/{agency}/manual-link-paystack', [ServiceFeeAgencyController::class, 'manualLinkPaystack'])->name('admin.service-fee-agencies.manual-link-paystack');
        Route::post('service-fee-agencies/{agency}/manual-link-monnify', [ServiceFeeAgencyController::class, 'manualLinkMonnify'])->name('admin.service-fee-agencies.manual-link-monnify');
        Route::resource('service-fee-agencies', ServiceFeeAgencyController::class)->parameters(['service-fee-agencies' => 'agency'])->names('admin.service-fee-agencies');
    });
    
    // Establishment Update Requests
    Route::get('/establishment/update-requests', [\App\Http\Controllers\Admin\EstablishmentUpdateRequestController::class, 'index'])->name('admin.establishment-update-requests.index');
    Route::get('/establishment/update-requests/{id}', [\App\Http\Controllers\Admin\EstablishmentUpdateRequestController::class, 'show'])->name('admin.establishment-update-requests.show');
    Route::post('/establishments/{id}/request-update', [\App\Http\Controllers\Admin\EstablishmentUpdateRequestController::class, 'store'])->name('admin.establishments.request-update');
    Route::post('/establishment/update-requests/{id}/approve', [\App\Http\Controllers\Admin\EstablishmentUpdateRequestController::class, 'approve'])->name('admin.establishment-update-requests.approve');
    Route::post('/establishment/update-requests/{id}/reject', [\App\Http\Controllers\Admin\EstablishmentUpdateRequestController::class, 'reject'])->name('admin.establishment-update-requests.reject');

    // Invoices Management
    Route::middleware(['can:view invoice'])->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('admin.invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('admin.invoices.show');
    });
    Route::post('/invoices/{id}/reverify', [InvoiceController::class, 'reverify'])
        ->middleware('can:verify invoice')
        ->name('admin.invoices.reverify');

    // Tax Billing and Collections (Payment routes)
    Route::get('/payments', [PaymentController::class, 'index'])
        ->middleware('can:view payments')
        ->name('admin.payments.index');
    Route::post('/establishments/{id}/pay-rule', [PaymentController::class, 'payRule'])->name('admin.establishments.pay-rule');
    Route::post('/payments/direct-pay', [PaymentController::class, 'directPay'])->name('admin.payments.direct-pay');

    // Reports and Analytics
    Route::middleware(['can:view report'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
    });

    // FAQ Management
    Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)->middleware('permission:manage faq')->names([
        'index' => 'admin.faqs.index',
        'create' => 'admin.faqs.create',
        'store' => 'admin.faqs.store',
        'edit' => 'admin.faqs.edit',
        'update' => 'admin.faqs.update',
        'destroy' => 'admin.faqs.destroy',
    ]);

    // Settings (Super Admin Only)
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
        Route::delete('settings/logo', [\App\Http\Controllers\Admin\SettingController::class, 'deleteLogo'])->name('admin.settings.delete-logo');
        Route::post('settings/test-mail', [\App\Http\Controllers\Admin\SettingController::class, 'sendTestMail'])->name('admin.settings.test-mail');
    });
});

Route::get('/establishment/{unique_id}', [PublicEstablishmentController::class, 'show'])->name('public.establishment.show');
