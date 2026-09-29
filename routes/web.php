<?php

use App\Http\Controllers\ExportDownloadController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\TransactionExportController;
use App\Livewire\Admin\CategoriesIndex as AdminCategoriesIndex;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\UsersIndex as AdminUsersIndex;
use App\Livewire\Budgets\Index as BudgetsIndex;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Debts\Index as DebtsIndex;
use App\Livewire\Exports\Index;
use App\Livewire\Reconciliations\Index as ReconciliationsIndex;
use App\Livewire\Recurring\Index as RecurringIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Savings\Index as SavingsIndex;
use App\Livewire\Savings\Show as SavingsShow;
use App\Livewire\Sources\Index as SourcesIndex;
use App\Livewire\Sources\Show as SourcesShow;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Livewire\Transfers\Index as TransfersIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('dashboard');
    }

    return view('welcome');
});

// Authenticated user & admin shared
Route::middleware(['auth', 'active'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
});

// User routes
Route::middleware(['auth', 'active', 'role:user'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('sources', SourcesIndex::class)->name('sources.index');
    Route::get('sources/{source}', SourcesShow::class)->name('sources.show');

    Route::get('categories', CategoriesIndex::class)->name('categories.index');
    Route::get('transactions', TransactionsIndex::class)->name('transactions.index');
    Route::get('transactions/export/excel', [TransactionExportController::class, 'excel'])->name('transactions.export.excel');
    Route::get('transfers', TransfersIndex::class)->name('transfers.index');

    // Tabungan (Fase 2)
    Route::get('savings', SavingsIndex::class)->name('savings.index');
    Route::get('savings/{goal}', SavingsShow::class)->name('savings.show');

    // Laporan & Export (Fase 2 & Fase 4)
    Route::get('reports', ReportsIndex::class)->name('reports.index');
    Route::get('reports/export/pdf', [ReportExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('reports/export/excel', [ReportExportController::class, 'excel'])->name('reports.export.excel');
    Route::get('exports', Index::class)->name('exports.index');
    Route::get('exports/download/{exportHistory}', [ExportDownloadController::class, 'download'])->name('exports.download');

    // Fase 3
    Route::get('budgets', BudgetsIndex::class)->name('budgets.index');
    Route::get('recurring', RecurringIndex::class)->name('recurring.index');
    Route::get('debts', DebtsIndex::class)->name('debts.index');
    Route::get('reconciliations', ReconciliationsIndex::class)->name('reconciliations.index');
});

// Admin routes
Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('users', AdminUsersIndex::class)->name('users');
    Route::get('categories', AdminCategoriesIndex::class)->name('categories');
});

require __DIR__.'/auth.php';
