<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TradingAccountController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Trading Accounts
    Route::resource('accounts', TradingAccountController::class);
    Route::post('accounts/switch/{account}', [TradingAccountController::class, 'switch'])->name('accounts.switch');

    // Trade Journal (CRUD)
    Route::resource('trades', TradeController::class);

    // Journal (calendar view)
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Tools
    Route::get('/tools', [ToolsController::class, 'index'])->name('tools.index');

    // Export
    Route::get('/export/csv', [ExportController::class, 'csv'])
        ->middleware('throttle:10,1')
        ->name('export.csv');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
