<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ScreenLockController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CareerJobController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TopbarController;
use App\Http\Controllers\TodoController;

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

Route::get('/email/track/{token}', [EmailTrackingController::class, 'open'])
    ->middleware('throttle:30,1')
    ->name('email.track.open');

Route::middleware('auth')->get('/kwikshift-gallery-image', [RoutingController::class, 'galleryAsset'])
    ->name('gallery.asset');

Route::middleware('auth')->get('/topbar/data', [TopbarController::class, 'getTopbarData'])
    ->name('topbar.data');

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('lock-screen', [ScreenLockController::class, 'show'])->name('lock-screen');
    Route::post('lock-screen', [ScreenLockController::class, 'lock'])->name('lock-screen.lock');
    Route::post('lock-screen/unlock', [ScreenLockController::class, 'unlock'])->name('lock-screen.unlock');

    Route::get('account', [AccountController::class, 'show'])->name('account.show');
    Route::get('account/signature', [AccountController::class, 'signature'])->name('account.signature');
    Route::patch('account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::patch('account/security', [AccountController::class, 'updateSecurity'])->name('account.security.update');
    Route::post('account/two-factor', [AccountController::class, 'requestTwoFactorEnable'])->name('account.two-factor.request');
    Route::post('account/two-factor/confirm', [AccountController::class, 'confirmTwoFactorEnable'])->middleware('throttle:5,10')->name('account.two-factor.confirm');
    Route::delete('account/two-factor', [AccountController::class, 'disableTwoFactor'])->name('account.two-factor.disable');
    Route::post('account/sessions/logout-others', [AccountController::class, 'logoutOtherDevices'])->name('account.sessions.logout-other-devices');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/payment', [SettingsController::class, 'paymentSettings'])->name('settings.payment');
    Route::post('settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment.update');
    Route::patch('settings/{section}', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('manage-apps', [SettingsController::class, 'apps'])->name('settings.apps');
    Route::get('todo', [TodoController::class, 'index'])->name('todo.index');
    Route::post('todo', [TodoController::class, 'store'])->name('todo.store');
    Route::patch('todo/{todoTask}', [TodoController::class, 'update'])->name('todo.update');
    Route::patch('todo/{todoTask}/toggle', [TodoController::class, 'toggle'])->name('todo.toggle');
    Route::delete('todo/{todoTask}', [TodoController::class, 'destroy'])->name('todo.destroy');

    Route::prefix('quotes')->name('quotes.')->group(function () {
        Route::get('', [QuoteController::class, 'index'])->name('index');
        Route::get('create', [QuoteController::class, 'create'])->name('create');
        Route::post('', [QuoteController::class, 'store'])->name('store');
        Route::get('{quote}/download', [QuoteController::class, 'download'])->name('download');
        Route::post('{quote}/send', [QuoteController::class, 'send'])->middleware('throttle:5,1')->name('send');
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
        Route::get('{quotation}/pdf', [QuotationController::class, 'pdf'])->name('pdf');
        Route::get('{quotation}', [QuotationController::class, 'show'])->name('show');
        Route::get('{quotation}/edit', [QuotationController::class, 'edit'])->name('edit');
        Route::put('{quotation}', [QuotationController::class, 'update'])->name('update');
        Route::post('{quotation}/send', [QuotationController::class, 'send'])->middleware('throttle:5,1')->name('send');
        Route::patch('{quotation}/approve', [QuotationController::class, 'approve'])->name('approve');
        Route::patch('{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
        Route::post('{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('duplicate');
        Route::delete('{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
    });

    Route::get('invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');
    Route::get('invoice/next-number', [InvoiceController::class, 'nextNumber'])->name('invoice.next-number');
    Route::get('invoice/quotes/{quote}', [InvoiceController::class, 'quote'])->name('invoice.quote');
    Route::post('invoice', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::post('invoice/{invoice}/send', [InvoiceController::class, 'send'])->middleware('throttle:5,1')->name('invoice.send');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->middleware('throttle:5,1')->name('invoices.send');
    Route::get('invoice/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoice.edit');
    Route::put('invoice/{invoice}', [InvoiceController::class, 'update'])->name('invoice.update');
    Route::patch('invoice/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoice.mark-paid');
    Route::patch('invoice/{invoice}/mark-unpaid', [InvoiceController::class, 'markUnpaid'])->name('invoice.mark-unpaid');
    Route::patch('invoice/{invoice}/mark-void', [InvoiceController::class, 'markVoid'])->name('invoice.mark-void');
    Route::post('invoice/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoice.duplicate');
    Route::delete('invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::get('invoice/invoices', [RoutingController::class, 'invoicesPage'])->name('invoice.index');
    Route::get('invoice/invoice-details/{invoice?}', [RoutingController::class, 'invoiceDetailsPage'])->name('invoice.details');

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::post('import', [CustomerController::class, 'import'])->name('import');
        Route::get('{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('careers')->name('careers.')->group(function () {
        Route::get('jobs', [CareerJobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/create', [CareerJobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [CareerJobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}', [CareerJobController::class, 'show'])->name('jobs.show');
        Route::get('jobs/{job}/edit', [CareerJobController::class, 'edit'])->name('jobs.edit');
        Route::put('jobs/{job}', [CareerJobController::class, 'update'])->name('jobs.update');
        Route::delete('jobs/{job}', [CareerJobController::class, 'destroy'])->name('jobs.destroy');

        Route::get('applications', [JobApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [JobApplicationController::class, 'show'])->name('applications.show');
        Route::patch('applications/{application}/status', [JobApplicationController::class, 'status'])->name('applications.status');
        Route::delete('applications/{application}', [JobApplicationController::class, 'destroy'])->name('applications.destroy');
    });

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('', [ReviewController::class, 'index'])->name('index');
        Route::get('{review}', [ReviewController::class, 'show'])->name('show');
        Route::patch('{review}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::patch('{review}/decline', [ReviewController::class, 'decline'])->name('decline');
        Route::delete('{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('faqs')->name('faqs.')->group(function () {
        Route::get('', [FaqController::class, 'index'])->name('index');
        Route::get('create', [FaqController::class, 'create'])->name('create');
        Route::post('', [FaqController::class, 'store'])->name('store');
        Route::get('{faq}', [FaqController::class, 'show'])->name('show');
        Route::get('{faq}/edit', [FaqController::class, 'edit'])->name('edit');
        Route::put('{faq}', [FaqController::class, 'update'])->name('update');
        Route::patch('{faq}/publish', [FaqController::class, 'publish'])->name('publish');
        Route::patch('{faq}/archive', [FaqController::class, 'archive'])->name('archive');
        Route::delete('{faq}', [FaqController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('gallery')->name('gallery.')->group(function () {
        Route::get('', [GalleryController::class, 'index'])->name('index');
        Route::get('create', [GalleryController::class, 'create'])->name('create');
        Route::post('', [GalleryController::class, 'store'])->name('store');
        Route::get('{gallery}', [GalleryController::class, 'show'])->name('show');
        Route::get('{gallery}/edit', [GalleryController::class, 'edit'])->name('edit');
        Route::put('{gallery}', [GalleryController::class, 'update'])->name('update');
        Route::patch('{gallery}/publish', [GalleryController::class, 'publish'])->name('publish');
        Route::patch('{gallery}/archive', [GalleryController::class, 'archive'])->name('archive');
        Route::delete('{gallery}', [GalleryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [ \App\Http\Controllers\MessageController::class, 'index' ])->name('index');
        Route::get('compose', [ \App\Http\Controllers\MessageController::class, 'compose' ])->name('compose');
        Route::post('/', [ \App\Http\Controllers\MessageController::class, 'store' ])->name('store');
        Route::get('{message}', [ \App\Http\Controllers\MessageController::class, 'show' ])->name('show');
        Route::post('{message}/respond', [ \App\Http\Controllers\MessageController::class, 'respond' ])->name('respond');
        Route::post('{message}/retry', [ \App\Http\Controllers\MessageController::class, 'retry' ])->name('retry');
        Route::delete('{message}', [ \App\Http\Controllers\MessageController::class, 'destroy' ])->name('destroy');
    });

    Route::delete('reports/email-delivery/{log}', [RoutingController::class, 'destroyEmailDeliveryLog'])
        ->name('reports.email-delivery.destroy');

    Route::get('', [RoutingController::class, 'index'])->name('root');
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});
