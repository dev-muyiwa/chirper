<?php

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify',  [AuthController::class, 'resendNotification'])
    ->middleware('auth:sanctum')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, "verifyEmail"])
    ->middleware(["signed"])
    ->name('verification.verify');

