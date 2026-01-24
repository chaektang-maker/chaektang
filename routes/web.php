<?php

use App\Http\Controllers\Admin\LotteryNumberController;
use App\Http\Controllers\Admin\LotteryResultController;
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
    });
});

require __DIR__.'/auth.php';
