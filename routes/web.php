<?php

use App\Http\Controllers\{
    CommonController,
    DashboardController,
    User\RoleController,
    User\UserController,
};
use App\Http\Controllers\Master\ExpenseController;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

Route::middleware('auth')->group(function () {

    /* Dashboard Routes */
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* Master Routes */
    Route::prefix('master')->name('master.')->group(function () {

        /* Work Category Routes */
        Route::resource('expense', ExpenseController::class);
        Route::post('expense/get-list', [ExpenseController::class, "getList"])->name("expense.getList");
        Route::get('expense/delete/{expense}', [ExpenseController::class, "destroy"])->name("expense.delete");

    });

    /* User Management Routes */
    Route::prefix('manage-user')->name("users.")->group(function () {
        /* Users */
        Route::resource('/', UserController::class)->except(['update']);
        Route::put('users/{user}', [UserController::class, "update"])->name("update");
        Route::post('get-list', [UserController::class, "getList"])->name("getList");
        Route::get('delete/{user}', [UserController::class, "destroy"])->name("user.delete");

        /* Roles & Permissions */
        Route::resource('role', RoleController::class);
        Route::post('role/get-list', [RoleController::class, "getList"])->name("role.getList");
        Route::get('role/delete/{role}', [RoleController::class, "destroy"])->name("role.delete");
    });
});
Route::fallback(function () {
    return view('404-page');
});

/* Authentication Routes  */
require __DIR__ . '/auth.php';
