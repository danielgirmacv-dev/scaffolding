<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::resource('sites', SiteController::class)->only(['index', 'show']);
Route::resource('materials', MaterialController::class)->only(['index', 'show']);
Route::resource('transactions', TransactionController::class)->only(['index']);
Route::resource('rentals', RentalController::class)->only(['index']);

Route::get('/invoices/{invoice}/print', [InvoicePrintController::class, 'show'])->name('invoices.print');

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/company-balance', [ReportController::class, 'companyBalance'])->name('company-balance');
    Route::get('/cost-summary', [ReportController::class, 'costSummary'])->name('cost-summary');
    Route::get('/anomalies', [ReportController::class, 'anomalies'])->name('anomalies');
});
