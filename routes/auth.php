<?php

use App\Http\Controllers\Auth\BackofficeAuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Customer\Auth\CustomerAuthenticatedSessionController;
use App\Http\Controllers\Customer\Auth\CustomerRegisteredUserController;
use App\Http\Controllers\Customer\Auth\CustomerNewPasswordController;
use App\Http\Controllers\Customer\Auth\CustomerPasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ========== ลูกค้า (customers table, guard customer) ==========
Route::middleware('guest')->group(function () {
    Route::get('register', [CustomerRegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [CustomerRegisteredUserController::class, 'store']);

    Route::get('login', [CustomerAuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [CustomerAuthenticatedSessionController::class, 'store']);
});

// ลูกค้าออกจากระบบ (หรือ admin ก็ใช้ route นี้ได้)
Route::post('logout', function (\Illuminate\Http\Request $request) {
    if (Auth::guard('customer')->check()) {
        Auth::guard('customer')->logout();
    } else {
        Auth::guard('web')->logout();
    }
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth:customer,web');

// ========== Backoffice (users table, guard web) ==========
Route::middleware('guest')->group(function () {
    Route::get('backoffice/login', [BackofficeAuthenticatedSessionController::class, 'create'])->name('backoffice.login');
    Route::post('backoffice/login', [BackofficeAuthenticatedSessionController::class, 'store']);
});

// ========== Password reset (สำหรับ users / backoffice) ==========
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [CustomerPasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [CustomerPasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [CustomerNewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [CustomerNewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});
