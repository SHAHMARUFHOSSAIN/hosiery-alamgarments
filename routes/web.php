<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CardPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PreviousDueController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MainBalanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserBalanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\SettingsController;
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
        Route::put('/{customer}/opening-balance', [CustomerController::class, 'updateOpeningBalance'])->name('customers.opening-balance');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    Route::prefix('banks')->group(function () {
        Route::get('/', [BankController::class, 'index'])->name('banks.index');
        Route::get('/search', [BankController::class, 'search'])->name('banks.search');
        Route::post('/', [BankController::class, 'store'])->name('banks.store');
        Route::put('/{bank}', [BankController::class, 'update'])->name('banks.update');
        Route::delete('/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');
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
        Route::get('/checks-report', [DueController::class, 'checksReport'])->name('dues.checks-report');
        Route::get('/tt-report', [DueController::class, 'ttReport'])->name('dues.tt-report');
        Route::get('/cash-report', [DueController::class, 'cashReport'])->name('dues.cash-report');
        Route::post('/mark-paid/{id}', [DueController::class, 'markPaid'])->name('dues.mark-paid');
        Route::post('/add-payment', [DueController::class, 'addPayment'])->name('dues.add-payment');
        Route::post('/encash/{id}', [DueController::class, 'encashCheck'])->name('dues.encash');
    });

    Route::prefix('card-payments')->group(function () {
        Route::get('/', [CardPaymentController::class, 'index'])->name('card-payments.index');
        Route::post('/encash/{id}', [CardPaymentController::class, 'encash'])->name('card-payments.encash');
    });

    Route::resource('previous-dues', PreviousDueController::class);
    Route::post('/previous-dues/add-payment', [PreviousDueController::class, 'addPayment'])->name('previous-dues.add-payment');

    Route::prefix('imports')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('imports.index');
        Route::post('/preview', [ImportController::class, 'preview'])->name('imports.preview');
        Route::post('/confirm', [ImportController::class, 'confirm'])->name('imports.confirm');
        Route::get('/history', [ImportController::class, 'history'])->name('imports.history');
        Route::get('/sample/{type}', [ImportController::class, 'downloadSample'])->name('imports.sample');
    });

    Route::prefix('main-balance')->group(function () {
        Route::get('/', [MainBalanceController::class, 'index'])->name('main-balance.index');
        Route::post('/', [MainBalanceController::class, 'store'])->name('main-balance.store');
        Route::get('/report', [MainBalanceController::class, 'balanceReport'])->name('main-balance.report');
        Route::get('/{mainBalance}/voucher', [MainBalanceController::class, 'voucher'])->name('main-balance.voucher');
    });

    Route::prefix('user-balance')->group(function () {
        Route::get('/', [UserBalanceController::class, 'index'])->name('user-balance.index');
        Route::post('/', [UserBalanceController::class, 'store'])->name('user-balance.store');
        Route::get('/{mainBalance}/voucher', [UserBalanceController::class, 'voucher'])->name('user-balance.voucher');
    });

    Route::prefix('user-reports')->group(function () {
        Route::get('/', [UserReportController::class, 'index'])->name('user-reports.index');
        Route::get('/sales', [UserReportController::class, 'sales'])->name('user-reports.sales');
        Route::get('/dues', [UserReportController::class, 'dues'])->name('user-reports.dues');
    });

    Route::middleware(['admin'])->group(function () {
        Route::delete('/imports/{importLog}', [ImportController::class, 'destroy'])->name('imports.destroy');
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
            Route::get('/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
            Route::get('/previous-dues', [ReportController::class, 'previousDue'])->name('reports.previous-dues');
            Route::get('/inactive-customers', [ReportController::class, 'inactiveCustomers'])->name('reports.inactive-customers');
            Route::get('/inactive-customers/export', [ExportController::class, 'inactiveCustomers'])->name('export.inactive-customers');
        });

        Route::prefix('export')->group(function () {
            Route::get('/bills', [ExportController::class, 'bills'])->name('export.bills');
            Route::get('/dues', [ExportController::class, 'dues'])->name('export.dues');
            Route::get('/customers', [ExportController::class, 'customers'])->name('export.customers');
            Route::get('/customer/{customer}', [ExportController::class, 'customer'])->name('export.customer');
            Route::get('/previous-dues', [ExportController::class, 'previousDues'])->name('export.previous-dues');
        });

        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('/users', [SettingsController::class, 'users'])->name('settings.users');
            Route::post('/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
            Route::put('/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
            Route::delete('/users/{user}', [SettingsController::class, 'deleteUser'])->name('settings.users.delete');
            Route::get('/transactions', [SettingsController::class, 'allTransactions'])->name('settings.transactions');
            Route::get('/system-info', [SettingsController::class, 'systemInfo'])->name('settings.system-info');
            Route::get('/data', [SettingsController::class, 'dataManagement'])->name('settings.data');
            Route::get('/company', [SettingsController::class, 'company'])->name('settings.company');
            Route::post('/company', [SettingsController::class, 'updateCompany'])->name('settings.company.update');
            Route::delete('/bills/{bill}', [SettingsController::class, 'deleteBill'])->name('settings.bills.delete');
            Route::put('/bills/{bill}', [SettingsController::class, 'editBill'])->name('settings.bills.update');
            Route::delete('/customers/{customer}', [SettingsController::class, 'deleteCustomer'])->name('settings.customers.delete');
            Route::put('/customers/{customer}', [SettingsController::class, 'editCustomer'])->name('settings.customers.update');
            Route::delete('/dues/{due}', [SettingsController::class, 'deleteDue'])->name('settings.dues.delete');
            Route::put('/dues/{due}', [SettingsController::class, 'editDue'])->name('settings.dues.update');
        });
    });
});

require __DIR__.'/auth.php';
