<?php

use App\Http\Controllers\Admin\LotteryNumberController;
use App\Http\Controllers\Admin\LotteryResultController;
use App\Http\Controllers\Admin\LottoDataController;
use App\Http\Controllers\Admin\LottoDetailsController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\ProfileController;
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

Route::get('/lucky-numbers', [LuckyNumbersController::class, 'index'])->name('lucky-numbers.index');
Route::get('/accuracy', [AccuracyController::class, 'index'])->name('accuracy.index');
Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
Route::get('/recheck', [ResultsController::class, 'index'])->name('results.index');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->middleware(['verified'])->group(function () {
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
        Route::post('lotto-details/fetch-batch', [LottoDetailsController::class, 'fetchBatch'])->name('lotto-details.fetch-batch');
        Route::post('lotto-details/fetch-single', [LottoDetailsController::class, 'fetchSingle'])->name('lotto-details.fetch-single');
    });
});

require __DIR__.'/auth.php';
