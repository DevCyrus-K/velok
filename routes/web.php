<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RoutingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__ . '/auth.php';

Route::middleware('auth')->get('/kwikshift-gallery-image', [RoutingController::class, 'galleryAsset'])
    ->name('gallery.asset');

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::prefix('quotes')->name('quotes.')->group(function () {
        Route::get('', [QuoteController::class, 'index'])->name('index');
        Route::get('create', [QuoteController::class, 'create'])->name('create');
        Route::post('', [QuoteController::class, 'store'])->name('store');
        Route::get('{quote}', [QuoteController::class, 'show'])->name('show');
        Route::get('{quote}/edit', [QuoteController::class, 'edit'])->name('edit');
        Route::put('{quote}', [QuoteController::class, 'update'])->name('update');
        Route::delete('{quote}', [QuoteController::class, 'destroy'])->name('destroy');
        Route::patch('{quote}/approve', [QuoteController::class, 'approve'])->name('approve');
        Route::patch('{quote}/decline', [QuoteController::class, 'decline'])->name('decline');
    });

    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('{quote}/create', [QuotationController::class, 'create'])->name('create');
        Route::post('', [QuotationController::class, 'store'])->name('store');
        Route::get('{quotation}', [QuotationController::class, 'show'])->name('show');
        Route::get('{quotation}/edit', [QuotationController::class, 'edit'])->name('edit');
        Route::put('{quotation}', [QuotationController::class, 'update'])->name('update');
        Route::post('{quotation}/send', [QuotationController::class, 'send'])->name('send');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::post('import', [CustomerController::class, 'import'])->name('import');
        Route::get('{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [ \App\Http\Controllers\MessageController::class, 'index' ])->name('index');
    });

    Route::delete('reports/email-delivery/{log}', [RoutingController::class, 'destroyEmailDeliveryLog'])
        ->name('reports.email-delivery.destroy');

    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});
