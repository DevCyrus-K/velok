<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CareerApiController;
use App\Http\Controllers\ContentApiController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Frontend message submission (public endpoint)
Route::post('/messages/submit', [MessageController::class, 'storeFromFrontend']);
Route::get('/careers/jobs', [CareerApiController::class, 'jobs']);
Route::post('/careers/applications', [CareerApiController::class, 'storeApplication']);
Route::get('/reviews', [ReviewApiController::class, 'index']);
Route::post('/reviews/submit', [ReviewApiController::class, 'store']);
Route::get('/faqs', [ContentApiController::class, 'faqs']);
Route::get('/gallery', [ContentApiController::class, 'gallery']);
