<?php

use App\Http\Controllers\Admin\QrisController;
use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\ReportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::redirect('/login', '/admin/login')->name('login');

Route::middleware(['web', 'auth'])->get('/admin/transactions/{transaction}/receipt', ReceiptController::class)
    ->name('admin.transactions.receipt');

Route::middleware(['web', 'auth'])->get('/admin/transactions/{transaction}/order-print', [ReceiptController::class, 'orderPrint'])
    ->name('admin.transactions.order-print');

Route::middleware(['web', 'auth'])->get('/admin/transactions/{transaction}/qris', [QrisController::class, 'show'])
    ->name('admin.transactions.qris');

Route::middleware(['web', 'auth'])->get('/admin/transactions/{transaction}/qris/download', [QrisController::class, 'download'])
    ->name('admin.transactions.qris.download');

Route::middleware(['web', 'auth'])->prefix('admin/report-exports')->name('admin.report-exports.')->group(function (): void {
    Route::get('/sales/{format}', [ReportExportController::class, 'sales'])->name('sales');
    Route::get('/products/{format}', [ReportExportController::class, 'products'])->name('products');
    Route::get('/ingredients/{format}', [ReportExportController::class, 'ingredients'])->name('ingredients');
    Route::get('/customer-statement/{format}', [ReportExportController::class, 'customerStatement'])->name('customer-statement');
    Route::get('/customer-product-sales/{format}', [ReportExportController::class, 'customerProductSales'])->name('customer-product-sales');
});
