<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DeliveryChallanController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SesController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Delivery challan Routes
    Route::group(['prefix' => 'delivery-challans'], function () {

        Route::get('/', [DeliveryChallanController::class, 'index'])->name('delivery-challans');
        Route::get('/create', [DeliveryChallanController::class, 'create'])->name('delivery-challans.create');
        Route::post('/store', [DeliveryChallanController::class, 'store'])->name('delivery-challans.store');
        Route::get('/{id}/download', [DeliveryChallanController::class, 'generateDeliveryChallanPDF'])->name('delivery-challans.download');
        Route::get('/{deliveryChallan}/edit', [DeliveryChallanController::class, 'edit'])->name('delivery-challans.edit');
        Route::put('/{deliveryChallan}', [DeliveryChallanController::class, 'update'])->name('delivery-challans.update');
        Route::delete('{deliveryChallan}', [DeliveryChallanController::class, 'destroy'])->name('delivery-challans.destroy');

    });

    // Quotation Routes
    Route::group(['prefix' => 'quotations'], function () {
        Route::get('/', [QuotationController::class, 'index'])->name('quotations');
        Route::get('/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/store', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
        Route::get('/{id}/download', [QuotationController::class, 'generateQuotationPDF'])->name('quotations.download');
    });

    // Invoice Routes

    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('invoices');

        Route::get('/create', [InvoiceController::class, 'create'])->name('invoices.create');

        Route::post('/store', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        Route::get('/{id}/download', [InvoiceController::class, 'generateInvoicePdf'])->name('invoices.generatePdf');
    });

    // SES Routes

    Route::group(['prefix' => 'ses'], function () {
        Route::get('/', [SesController::class, 'index'])->name('ses');
        Route::get('/create', [SesController::class, 'create'])->name('ses.create');
        Route::post('/store', [SesController::class, 'store'])->name('ses.store');
        Route::get('/{id}/download', [SesController::class, 'generateSesPDF'])->name('ses.download');
        Route::get('/{ses}/edit', [SesController::class, 'edit'])->name('ses.edit');
        Route::put('/{ses}', [SesController::class, 'update'])->name('ses.update');
        Route::delete('/{ses}', [SesController::class, 'destroy'])->name('ses.destroy');


    });

    Route::group(['prefix' => 'companies'], function () {
        Route::get('/', [CompanyController::class, 'index'])->name('companies');
        Route::get('/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/store', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });



});

Route::get('pdf', function () {
    return view('pdf.ses');
});

require __DIR__ . '/settings.php';
