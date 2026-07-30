<?php

use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['web', 'auth'])->get('/admin/transactions/{transaction}/receipt', ReceiptController::class)
    ->name('admin.transactions.receipt');

Route::middleware(['web', 'auth'])->prefix('admin/report-exports')->name('admin.report-exports.')->group(function (): void {
    Route::get('/sales/{format}', [ReportExportController::class, 'sales'])->name('sales');
    Route::get('/products/{format}', [ReportExportController::class, 'products'])->name('products');
    Route::get('/ingredients/{format}', [ReportExportController::class, 'ingredients'])->name('ingredients');
    Route::get('/customer-statement/{format}', [ReportExportController::class, 'customerStatement'])->name('customer-statement');
});
