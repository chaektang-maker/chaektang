<?php

use App\Http\Controllers\Admin\LotteryNumberController;
use App\Http\Controllers\Admin\LotteryResultController;
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
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\DreamInterpretationController;
use App\Http\Controllers\Public\VoteController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/check-lottery', [HomeController::class, 'checkLottery'])->name('check-lottery');

// Sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// รองรับการเปิดไฟล์รูปภาพใน storage/app/public ได้เสมอ แม้ยังไม่ได้รัน storage:link หรือรันบน Windows
Route::get('/storage/{path}', function (string $path) {
    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        abort(404);
    }
    \App\Services\MediaStorageService::syncToPublicDirectory($path);
    return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($path));
})->where('path', '.*')->withoutMiddleware(['web'])->name('storage.local');

Route::get('/lucky-numbers', [LuckyNumbersController::class, 'index'])->name('lucky-numbers.index');
Route::get('/advertise', fn () => Inertia::render('Public/Advertise'))->name('advertise.index');
Route::get('/accuracy', [AccuracyController::class, 'index'])->name('accuracy.index');
Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
Route::get('/recheck', [ResultsController::class, 'index'])->name('results.index');

// Lotto Analyzer (วิเคราะห์เลขนำโชค)
Route::get('/lotto-analyzer', [\App\Http\Controllers\Public\LottoAnalyzerController::class, 'index'])->name('lotto-analyzer.index');
Route::post('/lotto-analyzer/analyze', [\App\Http\Controllers\Public\LottoAnalyzerController::class, 'analyze'])->name('lotto-analyzer.analyze');

// Phone Analyzer (วิเคราะห์เบอร์มงคล & ถอดรหัสเลขนำโชคจากเบอร์มือถือ)
Route::get('/phone-analyzer', [\App\Http\Controllers\Public\PhoneAnalyzerController::class, 'index'])->name('phone-analyzer.index');
Route::post('/phone-analyzer/analyze', [\App\Http\Controllers\Public\PhoneAnalyzerController::class, 'analyze'])->name('phone-analyzer.analyze');
Route::get('/undrawn-first-prizes', [\App\Http\Controllers\Public\UndrawnFirstPrizeController::class, 'index'])->name('undrawn-first-prizes.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Vote routes
Route::post('/vote', [VoteController::class, 'store'])->name('vote.store');
Route::get('/vote/check', [VoteController::class, 'check'])->name('vote.check');
Route::get('/vote/counts', [VoteController::class, 'getVoteCounts'])->name('vote.counts');
Route::get('/vote/leaderboard', [VoteController::class, 'leaderboard'])->name('vote.leaderboard');

// ทำนายฝัน (ใช้ AI จาก Google AI Studio)
Route::get('/dream-interpretation', [DreamInterpretationController::class, 'index'])->name('dream-interpretation.index');
Route::post('/dream-interpretation', [DreamInterpretationController::class, 'interpret'])
    ->middleware('throttle:10,1')
    ->name('dream-interpretation.interpret');

// หน้าแนะนำสิทธิพิเศษและแพ็กเกจ VIP (เปิดให้ทุกคนเข้าดูข้อเสนอก่อนตัดสินใจได้ทันที)
Route::get('/request-vip', [\App\Http\Controllers\Customer\VipRequestController::class, 'create'])->name('vip-request.create');

// ลูกค้าส่งสลิปขอเปิดสิทธิ์ VIP (ต้องล็อกอิน)
Route::middleware('auth:customer')->group(function () {
    Route::post('/request-vip', [\App\Http\Controllers\Customer\VipRequestController::class, 'store'])->name('vip-request.store');
});

// ถ้าเข้าที่ /backoffice:
// - ถ้ายังไม่ login (guard web) → ไปหน้า /backoffice/login
// - ถ้า login เป็นเจ้าสำนัก → ไปหน้า /backportal
// - ถ้า login พนักงาน/แอดมิน → ไปหน้า dashboard หลังบ้าน
Route::get('/backoffice', function () {
    if (auth('web')->check()) {
        $u = auth('web')->user();
        if ($u && $u->isSourceOwner() && !$u->isAdmin()) {
            return redirect()->route('backportal.index');
        }
        return redirect()->route('backoffice.dashboard');
    }

    return redirect()->route('backoffice.login')->with('url.intended', route('backoffice.dashboard'));
});

// ทางเข้าสำหรับเจ้าสำนัก (/backportal):
// - ถ้ายังไม่ login → ไปหน้า /backportal/login
// - ถ้า login แล้ว → เปิดหน้าพอร์ทัลเจ้าสำนัก
Route::get('/backportal', function (\Illuminate\Http\Request $request) {
    if (!auth('web')->check()) {
        return redirect()->route('backportal.login');
    }
    return app(\App\Http\Controllers\Backoffice\SourceOwnerPortalController::class)->index($request);
})->name('backportal.index');

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ระบบพอร์ทัลเจ้าสำนัก (/backportal/*)
    Route::prefix('backportal')->name('backportal.')->middleware([\App\Http\Middleware\CheckPermission::class])->group(function () {
        Route::post('profile', [\App\Http\Controllers\Backoffice\SourceOwnerPortalController::class, 'updateProfile'])->name('profile');
        Route::post('numbers', [\App\Http\Controllers\Backoffice\SourceOwnerPortalController::class, 'saveNumbers'])->name('numbers');
        Route::post('products', [\App\Http\Controllers\Backoffice\SourceOwnerPortalController::class, 'saveProduct'])->name('products.save');
        Route::delete('products/{productId}', [\App\Http\Controllers\Backoffice\SourceOwnerPortalController::class, 'deleteProduct'])->name('products.delete');
    });

    // ระบบหลังบ้าน (/backoffice/*) เข้าได้เฉพาะพนักงาน (admin / staff) เท่านั้น
    Route::prefix('backoffice')->name('backoffice.')->middleware(['verified', \App\Http\Middleware\CheckPermission::class])->group(function () {
        // Dashboard หลังบ้าน (เฉพาะ admin และ staff)
        Route::get('dashboard', [\App\Http\Controllers\Backoffice\DashboardController::class, 'index'])->name('dashboard');

        // Redirect /backoffice/my-portal ไปยัง /backportal
        Route::get('my-portal', fn () => redirect()->route('backportal.index'))->name('my-portal.index');
        
        // Routes ที่ต้อง permission (admin/staff) - พนักงานและแอดมินสามารถลงเลขแทนสำนักได้ผ่านเมนู numbers
        Route::post('sources/{source}/toggle-status', [SourceController::class, 'toggleStatus'])->name('sources.toggle-status');
        Route::resource('sources', SourceController::class)->except(['show']);
        Route::get('results/import', [LotteryResultController::class, 'importForm'])->name('results.import');
        Route::post('results/import', [LotteryResultController::class, 'importFromApi'])->name('results.import.store');
        Route::post('results/sync-from-details', [LotteryResultController::class, 'syncFromDetails'])->name('results.sync');
        Route::resource('results', LotteryResultController::class)->except(['show']);
        Route::get('numbers/bulk', [LotteryNumberController::class, 'bulkEdit'])->name('numbers.bulk');
        Route::post('numbers/bulk', [LotteryNumberController::class, 'bulkUpdate'])->name('numbers.bulk.store');
        Route::resource('numbers', LotteryNumberController::class)->except(['show']);
        
        // Lotto Data Admin Interface (CRUD) - เปลี่ยน URI เป็น manage-lotto-data แต่ใช้ชื่อ route เดิมเพื่อไม่ให้กระทบจุดอื่น
        Route::resource('manage-lotto-data', \App\Http\Controllers\Admin\LottoDataAdminController::class)->names('lotto-data')->parameters(['manage-lotto-data' => 'lottoData'])->except(['show']);
        
        // Lotto Details Admin Interface (CRUD)
        Route::resource('manage-lotto-details', \App\Http\Controllers\Admin\LottoDetailsAdminController::class)->names('lotto-details-admin')->except(['show']);
        
        // Lotto Details API endpoints
        Route::post('lotto-details/fetch-all-pending', [LottoDetailsController::class, 'fetchAllPending'])->name('lotto-details.fetch-all-pending');
        Route::match(['get', 'post'], 'lotto-details/fetch-batch', [LottoDetailsController::class, 'fetchBatch'])->name('lotto-details.fetch-batch');
        Route::post('lotto-details/fetch-single', [LottoDetailsController::class, 'fetchSingle'])->name('lotto-details.fetch-single');
        Route::post('lotto-details/save-manual', [LottoDetailsController::class, 'saveManual'])->name('lotto-details.save-manual');
        
        // User & Permission management (เฉพาะ admin)
        Route::middleware(\App\Http\Middleware\EnsureUserIsAdmin::class)->group(function () {
            Route::resource('users', \App\Http\Controllers\Backoffice\UserController::class);
            Route::get('permissions', [\App\Http\Controllers\Backoffice\PermissionController::class, 'index'])->name('permissions.index');
            Route::put('permissions/{user}', [\App\Http\Controllers\Backoffice\PermissionController::class, 'update'])->name('permissions.update');

            // แพลตฟอร์ม & สินค้าวัตถุมงคล (Affiliate) & ตำราเบอร์มงคล
            Route::resource('platforms', \App\Http\Controllers\Backoffice\PlatformController::class);
            Route::resource('affiliate-products', \App\Http\Controllers\Backoffice\AffiliateProductController::class);
            Route::post('posts/upload-image', [\App\Http\Controllers\Backoffice\PostController::class, 'uploadImage'])->name('posts.upload-image');
            Route::post('posts/{post}/toggle-publish', [\App\Http\Controllers\Backoffice\PostController::class, 'togglePublish'])->name('posts.toggle-publish');
            Route::resource('posts', \App\Http\Controllers\Backoffice\PostController::class);

            // จัดการตำราเบอร์มงคล (00-99) แบบ Hybrid (Default + Database Override)
            Route::get('phone-pairs', [\App\Http\Controllers\Backoffice\PhonePairAdminController::class, 'index'])->name('phone-pairs.index');
            Route::put('phone-pairs/{pair}', [\App\Http\Controllers\Backoffice\PhonePairAdminController::class, 'update'])->name('phone-pairs.update');
            Route::delete('phone-pairs/{pair}/reset', [\App\Http\Controllers\Backoffice\PhonePairAdminController::class, 'reset'])->name('phone-pairs.reset');

            // จัดการตำราทำนายฝัน (ก-ฮ) + นำเข้าจากข้อความ/ไฟล์ .txt
            Route::get('dream-dictionary', [\App\Http\Controllers\Backoffice\DreamDictionaryAdminController::class, 'index'])->name('dream-dictionary.index');
            Route::post('dream-dictionary/import-bulk', [\App\Http\Controllers\Backoffice\DreamDictionaryAdminController::class, 'importBulk'])->name('dream-dictionary.import-bulk');
            Route::post('dream-dictionary/sync-file', [\App\Http\Controllers\Backoffice\DreamDictionaryAdminController::class, 'syncDefaultFile'])->name('dream-dictionary.sync-file');
            Route::post('dream-dictionary', [\App\Http\Controllers\Backoffice\DreamDictionaryAdminController::class, 'store'])->name('dream-dictionary.store');
            Route::delete('dream-dictionary/{id}', [\App\Http\Controllers\Backoffice\DreamDictionaryAdminController::class, 'destroy'])->name('dream-dictionary.destroy');

            // จัดการคำขอ VIP จากการโอนเงิน
            Route::get('vip-requests', [VipRequestAdminController::class, 'index'])->name('vip-requests.index');
            Route::get('vip-requests/{vipRequest}', [VipRequestAdminController::class, 'show'])->name('vip-requests.show');
            Route::post('vip-requests/{vipRequest}/approve', [VipRequestAdminController::class, 'approve'])->name('vip-requests.approve');
            Route::post('vip-requests/{vipRequest}/reject', [VipRequestAdminController::class, 'reject'])->name('vip-requests.reject');

            // จัดการสมาชิกหน้าเว็บ (Customers)
            Route::get('customers', [\App\Http\Controllers\Backoffice\CustomerAdminController::class, 'index'])->name('customers.index');
            Route::post('customers/{customer}/grant-vip', [\App\Http\Controllers\Backoffice\CustomerAdminController::class, 'grantVip'])->name('customers.grant-vip');
            Route::post('customers/{customer}/revoke-vip', [\App\Http\Controllers\Backoffice\CustomerAdminController::class, 'revokeVip'])->name('customers.revoke-vip');
            Route::post('customers/{customer}/reset-link', [\App\Http\Controllers\Backoffice\CustomerAdminController::class, 'generateResetLink'])->name('customers.reset-link');
            Route::post('customers/{customer}/reset-password', [\App\Http\Controllers\Backoffice\CustomerAdminController::class, 'resetPassword'])->name('customers.reset-password');
        });
    });
});

require __DIR__.'/auth.php';
