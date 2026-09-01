<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackofficeController;
use App\Http\Controllers\Api\CompanyUserController;
use App\Http\Controllers\Api\LawHearingController;
use App\Http\Controllers\Api\PlatformAdminController;
use App\Http\Controllers\Api\PlatformAuthController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UsageSnapshotController;
use App\Http\Middleware\EnsureCompanyContext;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsurePlatformPermission;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register-company', [AuthController::class, 'registerCompany']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/request-password-reset', [AuthController::class, 'requestPasswordReset']);
Route::post('/auth/set-password', [AuthController::class, 'setPassword']);
Route::post('/auth/accept-membership', [AuthController::class, 'acceptMembership']);
Route::post('/auth/accept-admin-transfer', [AuthController::class, 'acceptAdminTransfer']);
Route::post('/webhooks/mercado-pago', [SubscriptionController::class, 'webhook']);
Route::post('/integrations/usage', [UsageSnapshotController::class, 'store'])->middleware('throttle:60,1');
Route::get('/catalog/{product}', [SubscriptionController::class, 'publicCatalog']);

Route::prefix('backoffice/auth')->group(function () {
    Route::post('/login', [PlatformAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/verify-mfa', [PlatformAuthController::class, 'verifyMfa'])->middleware('throttle:5,1');
    Route::post('/resend-mfa', [PlatformAuthController::class, 'resendMfa'])->middleware('throttle:2,1');
    Route::post('/activate-invitation', [PlatformAuthController::class, 'activateInvitation'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/register-company-for-current-user', [AuthController::class, 'registerCompanyForCurrentUser']);
    Route::post('/auth/select-company', [AuthController::class, 'selectCompany']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
    Route::middleware(EnsureCompanyContext::class)->group(function () {
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('/subscriptions/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/subscriptions/{subscription}/change', [SubscriptionController::class, 'change']);
        Route::get('/law/hearings', [LawHearingController::class, 'index']);
        Route::post('/law/hearings', [LawHearingController::class, 'store']);
        Route::get('/law/hearings/{hearing}', [LawHearingController::class, 'show']);
        Route::patch('/law/hearings/{hearing}', [LawHearingController::class, 'update']);
        Route::post('/law/hearings/{hearing}/status', [LawHearingController::class, 'status']);
        Route::get('/law/hearings/{hearing}/timeline', [LawHearingController::class, 'timeline']);
        Route::post('/law/hearings/{hearing}/external-access', [LawHearingController::class, 'createExternalAccess']);
        Route::delete('/law/hearings/{hearing}/external-access/{access}', [LawHearingController::class, 'revokeExternalAccess']);
    });

    Route::middleware(EnsureCompanyContext::class)->prefix('portal')->group(function () {
        Route::get('/users', [CompanyUserController::class, 'index']);
        Route::post('/users', [CompanyUserController::class, 'store']);
        Route::patch('/users/{membership}', [CompanyUserController::class, 'update']);
        Route::post('/users/{membership}/restore', [CompanyUserController::class, 'restore']);
        Route::post('/transfer-admin', [CompanyUserController::class, 'transferAdmin']);
        Route::get('/audit-history', [CompanyUserController::class, 'auditHistory']);
    });
});

Route::get('/law/external/hearings/{token}', [LawHearingController::class, 'external'])->where('token', '[A-Za-z0-9]+');

Route::middleware(EnsurePlatformAdmin::class)->prefix('backoffice')->group(function () {
    Route::get('/auth/me', [PlatformAuthController::class, 'me']);
    Route::post('/auth/logout', [PlatformAuthController::class, 'logout']);
    Route::get('/dashboard', [BackofficeController::class, 'dashboard'])->middleware(EnsurePlatformPermission::class.':platform.dashboard.view');
    Route::get('/catalog', [BackofficeController::class, 'catalog'])->middleware(EnsurePlatformPermission::class.':platform.catalog.manage');
    Route::get('/plans', [BackofficeController::class, 'plans'])->middleware(EnsurePlatformPermission::class.':platform.catalog.manage');
    Route::post('/plans', [BackofficeController::class, 'createPlan'])->middleware(EnsurePlatformPermission::class.':platform.catalog.manage');
    Route::patch('/plans/{plan}', [BackofficeController::class, 'updatePlan'])->middleware(EnsurePlatformPermission::class.':platform.catalog.manage');
    Route::delete('/plans/{plan}', [BackofficeController::class, 'deletePlan'])->middleware(EnsurePlatformPermission::class.':platform.catalog.publish');
    Route::get('/companies', [BackofficeController::class, 'companies'])->middleware(EnsurePlatformPermission::class.':platform.companies.view');
    Route::get('/companies/{company}', [BackofficeController::class, 'company'])->middleware(EnsurePlatformPermission::class.':platform.companies.view');
    Route::patch('/subscriptions/{subscription}', [BackofficeController::class, 'changeSubscription'])->middleware(EnsurePlatformPermission::class.':platform.subscriptions.manage');
    Route::get('/vouchers', [BackofficeController::class, 'vouchers'])->middleware(EnsurePlatformPermission::class.':platform.vouchers.manage');
    Route::post('/vouchers', [BackofficeController::class, 'createVoucher'])->middleware(EnsurePlatformPermission::class.':platform.vouchers.manage');
    Route::patch('/vouchers/{voucher}', [BackofficeController::class, 'updateVoucherStatus'])->middleware(EnsurePlatformPermission::class.':platform.vouchers.manage');
    Route::delete('/vouchers/{voucher}', [BackofficeController::class, 'deleteVoucher'])->middleware(EnsurePlatformPermission::class.':platform.vouchers.manage');
    Route::get('/admins', [PlatformAdminController::class, 'index'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::post('/admins/invitations', [PlatformAdminController::class, 'invite'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::patch('/admins/{admin}/role', [PlatformAdminController::class, 'updateRole'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::post('/admins/{admin}/block', [PlatformAdminController::class, 'block'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::post('/admins/{admin}/unblock', [PlatformAdminController::class, 'unblock'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::post('/admins/{admin}/deactivate', [PlatformAdminController::class, 'deactivate'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::get('/admins/{admin}/security-events', [PlatformAdminController::class, 'securityEvents'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::post('/users/{user}/force-password-reset', [BackofficeController::class, 'forcePasswordReset'])->middleware(EnsurePlatformPermission::class.':platform.security.manage');
    Route::get('/audit', [BackofficeController::class, 'audit'])->middleware(EnsurePlatformPermission::class.':platform.audit.view_commercial');
});
