<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\MonthlyFundController as AdminMonthlyFundController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\SettlementController as AdminSettlementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HeaderStatsController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\ExpenseController as EmployeeExpenseController;
use App\Http\Controllers\Employee\PaymentController as EmployeePaymentController;
use App\Http\Controllers\Employee\SettlementController as EmployeeSettlementController;
use App\Http\Controllers\Employee\UpiController as EmployeeUpiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user?->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->role === 'employee') {
        return redirect()->route('employee.dashboard');
    }

    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::middleware('guest')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::view('/offline', 'offline')->name('offline');
Route::get('/header-stats', HeaderStatsController::class)->middleware(['auth', 'active'])->name('header-stats');

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('/employees', AdminEmployeeController::class);
    Route::get('/expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/{expense}', [AdminExpenseController::class, 'show'])->name('expenses.show');
    Route::patch('/expenses/{expense}/status', [AdminExpenseController::class, 'updateStatus'])->name('expenses.status');
    Route::redirect('/monthly-funds', '/admin/company-funds');
    Route::resource('/company-funds', AdminMonthlyFundController::class)
        ->names('monthly-funds')
        ->parameters(['company-funds' => 'monthlyFund']);
    Route::get('/settlements', [AdminSettlementController::class, 'index'])->name('settlements.index');
    Route::post('/settlements/{employee}/pay', [AdminSettlementController::class, 'pay'])->name('settlements.pay');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
});

Route::middleware(['auth', 'active', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', EmployeeDashboardController::class)->name('dashboard');
    Route::resource('/expenses', EmployeeExpenseController::class);
    Route::get('/settlements', [EmployeeSettlementController::class, 'index'])->name('settlements.index');
    Route::get('/payments', [EmployeePaymentController::class, 'index'])->name('payments.index');
    Route::patch('/upi', [EmployeeUpiController::class, 'update'])->name('upi.update');
});
