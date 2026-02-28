<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('products', [ProductController::class, 'index'])->name('products.index')->middleware('role:manager,director');
    Route::middleware('role:manager')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('role:manager,director')->group(function () {
        Route::get('/operations', [OperationController::class, 'index'])->name('operations.index');
        Route::get('/operations/all', [OperationController::class, 'all'])->name('operations.all');
    });
    Route::post('/operations', [OperationController::class, 'store'])
        ->name('operations.store')
        ->middleware('auth', 'role:manager');

    Route::middleware('role:manager,accountant,director')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{report}/{format}', [ReportController::class, 'export'])
            ->name('reports.export');
        Route::get('/reports/export-by-period', [ReportController::class, 'exportByPeriod'])
            ->name('reports.export-by-period');
    });
    Route::post('/reports/generate', [ReportController::class, 'generate'])
        ->name('reports.generate')
        ->middleware('auth', 'role:manager');
    Route::post('/reports/save', [ReportController::class, 'save'])
        ->name('reports.save')
        ->middleware('auth', 'role:manager');

    Route::middleware('role:accountant,director')->group(function () {
        Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
        Route::get('/salary/export', [SalaryController::class, 'export'])->name('salary.export');
    });

    Route::middleware('role:director')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    });
});
