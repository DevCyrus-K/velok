<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CareerApiController;
use App\Http\Controllers\ContentApiController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', UserController::class)->name('api.user');

// Production hardening: public API endpoints are named and controller-backed.
Route::post('/messages/submit', [MessageController::class, 'storeFromFrontend'])->name('api.messages.submit');
Route::get('/careers/jobs', [CareerApiController::class, 'jobs'])->name('api.careers.jobs');
Route::post('/careers/applications', [CareerApiController::class, 'storeApplication'])->name('api.careers.applications.store');
Route::get('/reviews', [ReviewApiController::class, 'index'])->name('api.reviews.index');
Route::post('/reviews/submit', [ReviewApiController::class, 'store'])->name('api.reviews.submit');
Route::get('/faqs', [ContentApiController::class, 'faqs'])->name('api.faqs.index');
Route::get('/gallery', [ContentApiController::class, 'gallery'])->name('api.gallery.index');
