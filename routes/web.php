<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    Route::prefix('bills')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('bills.index');
        Route::get('/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/', [BillController::class, 'store'])->name('bills.store');
        Route::get('/{bill}', [BillController::class, 'show'])->name('bills.show');
        Route::get('/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
        Route::put('/{bill}', [BillController::class, 'update'])->name('bills.update');
        Route::delete('/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    });

    Route::prefix('dues')->group(function () {
        Route::get('/', [DueController::class, 'index'])->name('dues.index');
        Route::get('/daily-report', [DueController::class, 'dailyReport'])->name('dues.daily-report');
        Route::post('/mark-paid/{id}', [DueController::class, 'markPaid'])->name('dues.mark-paid');
    });

    Route::middleware(['admin'])->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('reports.sales');
            Route::get('/dues', [ReportController::class, 'dues'])->name('reports.dues');
            Route::get('/inactive-customers', [ReportController::class, 'inactiveCustomers'])->name('reports.inactive-customers');
            Route::get('/inactive-customers/export', [ExportController::class, 'inactiveCustomers'])->name('export.inactive-customers');
        });

        Route::prefix('export')->group(function () {
            Route::get('/bills', [ExportController::class, 'bills'])->name('export.bills');
            Route::get('/dues', [ExportController::class, 'dues'])->name('export.dues');
        });
    });
});

require __DIR__.'/auth.php';
