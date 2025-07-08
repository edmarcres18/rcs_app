<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Email Verification Routes
Route::get('/email/verify', [EmailVerificationController::class, 'showVerificationForm'])
    ->name('verification.notice');
Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
    ->name('verification.resend');

// User Management Routes
Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::get('users/{user}/activities', [UserController::class, 'activities'])->name('users.activities');
    Route::get('activities', [UserController::class, 'allActivities'])->name('users.all-activities');
});
