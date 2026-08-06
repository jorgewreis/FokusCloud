<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyUserController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Middleware\EnsureCompanyContext;
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

Route::middleware('auth')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/select-company', [AuthController::class, 'selectCompany']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/subscriptions/checkout', [SubscriptionController::class, 'checkout'])->middleware(EnsureCompanyContext::class);

    Route::middleware(EnsureCompanyContext::class)->prefix('admin')->group(function () {
        Route::get('/users', [CompanyUserController::class, 'index']);
        Route::post('/users', [CompanyUserController::class, 'store']);
        Route::patch('/users/{membership}', [CompanyUserController::class, 'update']);
        Route::post('/transfer-admin', [CompanyUserController::class, 'transferAdmin']);
    });
});
