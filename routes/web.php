<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\PendingUpdateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\InstructionMonitorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\NotificationController;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// Broadcasting Authentication for Pusher
Broadcast::routes(['middleware' => ['auth:sanctum']]);

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

// Pending Updates Routes for SYSTEM_ADMIN
Route::middleware(['auth', 'role:SYSTEM_ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('pending-updates', [PendingUpdateController::class, 'index'])->name('pending-updates.index');
    Route::post('pending-updates/{pendingUpdate}/approve', [PendingUpdateController::class, 'approve'])->name('pending-updates.approve');
    Route::post('pending-updates/{pendingUpdate}/reject', [PendingUpdateController::class, 'reject'])->name('pending-updates.reject');
});

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [App\Http\Controllers\UserProfileController::class, 'changePasswordForm'])->name('profile.change-password');
    Route::put('/profile/change-password', [App\Http\Controllers\UserProfileController::class, 'updatePassword'])->name('profile.update-password');
});

// Instruction Routes
Route::middleware('auth')->group(function () {
    // General instruction routes
    Route::get('instructions', [InstructionController::class, 'index'])->name('instructions.index');
    Route::get('instructions/create', [InstructionController::class, 'create'])->name('instructions.create');
    Route::post('instructions', [InstructionController::class, 'store'])->name('instructions.store');
    Route::get('instructions/{instruction}', [InstructionController::class, 'show'])->name('instructions.show');

    // Read, reply, forward
    Route::post('instructions/{instruction}/mark-as-read', [InstructionController::class, 'markAsRead'])->name('instructions.read');
    Route::post('instructions/{instruction}/reply', [InstructionController::class, 'reply'])->name('instructions.reply');
    Route::get('instructions/{instruction}/forward', [InstructionController::class, 'showForward'])->name('instructions.show-forward');
    Route::post('instructions/{instruction}/forward', [InstructionController::class, 'forward'])->name('instructions.forward');

    // API routes for real-time updates
    Route::get('api/instructions/{instruction}/updates', [InstructionController::class, 'getUpdates']);

    // Monitor routes (Admin and System Admin only)
    Route::get('instructions/monitor/dashboard', [InstructionMonitorController::class, 'index'])->name('instructions.monitor');
    Route::get('instructions/monitor/activities/{instruction}', [InstructionMonitorController::class, 'showActivityLogs'])->name('instructions.monitor.activities');
    Route::get('instructions/monitor/all-activities', [InstructionMonitorController::class, 'allActivityLogs'])->name('instructions.monitor.all-activities');
    Route::get('instructions/monitor/reports', [InstructionMonitorController::class, 'reports'])->name('instructions.monitor.reports');
});

// Notification API routes
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');
});
