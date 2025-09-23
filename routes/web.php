<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\InstructionController;
use App\Http\Controllers\InstructionMonitorController;
use App\Http\Controllers\PendingUpdateController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\Api\V1\SystemNotificationsController as ApiSystemNotificationsController;
use App\Http\Controllers\AiAssistantController;

// Offline fallback route for service worker
Route::get('/offline', function () {
    return File::get(public_path() . '/offline.html');
});

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/help', function () {
    return view('help');
});

// Public presentation of end-user slides
Route::get('/presentation', [PresentationController::class, 'show'])->name('presentation.show');

Auth::routes(['verify' => false]); // Disable default verification routes

// Email Verification Routes
Route::get('/email/verify', [EmailVerificationController::class, 'showVerificationForm'])
    ->middleware('guest')
    ->name('verification.notice');

Route::post('/email/verify', [EmailVerificationController::class, 'verify'])
    ->middleware('guest')
    ->name('verification.verify');

Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
    ->middleware('guest')
    ->name('verification.resend');

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// RCS Assistant (AI) endpoint
Route::middleware(['auth', 'throttle:30,1'])->post('/ai/assistant', AiAssistantController::class)->name('ai.assistant');

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

    // System Settings
    Route::get('system-settings', [SystemSettingController::class, 'index'])->name('system-settings.index');
    Route::post('system-settings', [SystemSettingController::class, 'store'])->name('system-settings.store');
    Route::get('system-settings/mail', [SystemSettingController::class, 'mail'])->name('system-settings.mail');
    Route::post('system-settings/mail', [SystemSettingController::class, 'updateMail'])->name('system-settings.mail.update');

    // Ratings Monitor (SYSTEM_ADMIN only)
    Route::get('ratings', [\App\Http\Controllers\RatingController::class, 'adminIndex'])->name('ratings.index');

    // System Notifications
    Route::resource('system-notifications', \App\Http\Controllers\SystemNotificationsController::class);
    Route::post('system-notifications/{systemNotification}/send-now', [\App\Http\Controllers\SystemNotificationsController::class, 'sendNow'])
        ->name('system-notifications.send-now');
});

// Database Backup Routes for SYSTEM_ADMIN only
Route::middleware(['auth', 'role:SYSTEM_ADMIN'])->group(function () {
    Route::get('database/backups', [DatabaseBackupController::class, 'index'])->name('database.backups');
    Route::get('database/backup/create', [DatabaseBackupController::class, 'create'])->name('database.backup.create');
    Route::get('database/backup/download/{filename}', [DatabaseBackupController::class, 'download'])->name('database.backup.download');
    Route::delete('database/backup/delete/{filename}', [DatabaseBackupController::class, 'delete'])->name('database.backup.delete');
    Route::post('database/backup/restore/{filename}', [DatabaseBackupController::class, 'restore'])->name('database.backup.restore');
    Route::delete('database/backup/destroy/{filename}', [DatabaseBackupController::class, 'destroy'])->name('database.backup.destroy');
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

    // Attachment download route
    Route::get('instructions/replies/{reply}/download', [InstructionController::class, 'downloadAttachment'])->name('instructions.replies.download');

    // Monitor routes (Admin and System Admin only)
    Route::get('instructions/monitor/dashboard', [InstructionMonitorController::class, 'index'])->name('instructions.monitor');
    Route::get('instructions/monitor/activities/{instruction}', [InstructionMonitorController::class, 'showActivityLogs'])->name('instructions.monitor.activities');
    Route::get('instructions/monitor/all-activities', [InstructionMonitorController::class, 'allActivityLogs'])->name('instructions.monitor.all-activities');
    Route::get('instructions/monitor/reports', [InstructionMonitorController::class, 'reports'])->name('instructions.monitor.reports');
});

// Notification API routes
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread.count');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');

    // System Notifications for session-authenticated users (non-SYSTEM_ADMIN will receive data)
    Route::get('system-notifications', [ApiSystemNotificationsController::class, 'index'])->name('system-notifications.index');

    // Ratings (session-auth via web guard)
    Route::post('ratings', [\App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
    Route::get('ratings', [\App\Http\Controllers\RatingController::class, 'index'])->name('ratings.index');
    Route::get('ratings/stats', [\App\Http\Controllers\RatingController::class, 'getUserStats'])->name('ratings.stats');
});

// Test route for debugging notifications (remove in production)
Route::middleware('auth')->get('/test-notification', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $user->notify(new \App\Notifications\RealTimeNotification(
        'Test notification from debug route',
        route('home'),
        'test'
    ));

    return response()->json([
        'message' => 'Test notification sent successfully',
        'user_id' => $user->id,
        'pusher_config' => [
            'app_key' => config('broadcasting.connections.pusher.key'),
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'host' => config('broadcasting.connections.pusher.options.host'),
        ],
    ]);
})->name('test.notification');
