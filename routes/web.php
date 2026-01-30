<?php

use App\Http\Controllers\Admin\LotteryNumberController;
use App\Http\Controllers\Admin\LotteryResultController;
use App\Http\Controllers\Admin\LottoDataController;
use App\Http\Controllers\Admin\LottoDetailsController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backoffice\MyNumbersController;
use App\Http\Controllers\Backoffice\VipRequestAdminController;
use App\Http\Controllers\Public\AccuracyController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LuckyNumbersController;
use App\Http\Controllers\Public\ResultsController;
use App\Http\Controllers\Public\StatisticsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/check-lottery', [HomeController::class, 'checkLottery'])->name('check-lottery');

// Sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::get('/lucky-numbers', [LuckyNumbersController::class, 'index'])->name('lucky-numbers.index');
Route::get('/accuracy', [AccuracyController::class, 'index'])->name('accuracy.index');
Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
Route::get('/recheck', [ResultsController::class, 'index'])->name('results.index');

// ถ้าเข้าที่ /backoffice:
// - ถ้ายังไม่ login → ไปหน้า login (/backoffice/login)
// - ถ้า login แล้ว → ไปหน้า dashboard หลังบ้าน
Route::get('/backoffice', function () {
    if (auth()->check()) {
        return redirect()->route('backoffice.dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::prefix('backoffice')->name('backoffice.')->middleware(['verified'])->group(function () {
        // Dashboard - เข้าถึงได้ทุกคนที่ login แล้ว
        Route::get('dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
        
        // Routes ที่ต้อง permission (admin/staff)
        Route::middleware(\App\Http\Middleware\CheckPermission::class)->group(function () {
        Route::resource('sources', SourceController::class)->except(['show']);
        Route::resource('results', LotteryResultController::class)->except(['show']);
        Route::get('results/import', [LotteryResultController::class, 'importForm'])->name('results.import');
        Route::post('results/import', [LotteryResultController::class, 'importFromApi'])->name('results.import.store');
        Route::resource('numbers', LotteryNumberController::class)->except(['show']);
        
        // Lotto Data Admin Interface (หน้า admin เหมือน lottodataBack/admin/index.php)
        Route::get('lotto-data', [\App\Http\Controllers\Admin\LottoDataAdminController::class, 'index'])->name('lotto-data.index');
        
        // Lotto Data API endpoints (เหมือนกับ lottodataBack/admin/fetch_lotto_data.php)
        Route::post('lotto-data/fetch-all', [LottoDataController::class, 'fetchAll'])->name('lotto-data.fetch-all');
        Route::post('lotto-data/fetch-single', [LottoDataController::class, 'fetchSingle'])->name('lotto-data.fetch-single');
        
        // Lotto Details API endpoints (เหมือนกับ lottodataBack/admin/fetch_lotto_details.php)
        Route::post('lotto-details/fetch-all-pending', [LottoDetailsController::class, 'fetchAllPending'])->name('lotto-details.fetch-all-pending');
        // อนุญาตทั้ง GET/POST สำหรับ batch ให้เรียกจาก browser ได้ โดยไม่ error 405
        Route::match(['get', 'post'], 'lotto-details/fetch-batch', [LottoDetailsController::class, 'fetchBatch'])->name('lotto-details.fetch-batch');
        Route::post('lotto-details/fetch-single', [LottoDetailsController::class, 'fetchSingle'])->name('lotto-details.fetch-single');
        
        // User & Permission management (เฉพาะ admin)
        Route::middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)->group(function () {
            Route::resource('users', \App\Http\Controllers\Backoffice\UserController::class);
            Route::get('permissions', [\App\Http\Controllers\Backoffice\PermissionController::class, 'index'])->name('permissions.index');
            Route::put('permissions/{user}', [\App\Http\Controllers\Backoffice\PermissionController::class, 'update'])->name('permissions.update');

            // แพลตฟอร์ม & สินค้าวัตถุมงคล (Affiliate)
            Route::resource('platforms', \App\Http\Controllers\Backoffice\PlatformController::class);
            Route::resource('affiliate-products', \App\Http\Controllers\Backoffice\AffiliateProductController::class);

            // จัดการคำขอ VIP จากการโอนเงิน
            Route::get('vip-requests', [VipRequestAdminController::class, 'index'])->name('vip-requests.index');
            Route::get('vip-requests/{vipRequest}', [VipRequestAdminController::class, 'show'])->name('vip-requests.show');
            Route::post('vip-requests/{vipRequest}/approve', [VipRequestAdminController::class, 'approve'])->name('vip-requests.approve');
            Route::post('vip-requests/{vipRequest}/reject', [VipRequestAdminController::class, 'reject'])->name('vip-requests.reject');
        });

        // เจ้าของสำนักอัปเดตเลขของตัวเอง
        Route::middleware(\App\Http\Middleware\EnsureUserIsSourceOwner::class)->group(function () {
            Route::get('my-numbers', [MyNumbersController::class, 'index'])->name('my-numbers.index');
            Route::get('my-numbers/create', [MyNumbersController::class, 'create'])->name('my-numbers.create');
            Route::post('my-numbers', [MyNumbersController::class, 'store'])->name('my-numbers.store');
            Route::get('my-numbers/{number}/edit', [MyNumbersController::class, 'edit'])->name('my-numbers.edit');
            Route::put('my-numbers/{number}', [MyNumbersController::class, 'update'])->name('my-numbers.update');
        });
        });
    });
});

require __DIR__.'/auth.php';
