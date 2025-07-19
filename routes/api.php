<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\WebPushController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Notification API routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread.count');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');
    Route::post('notifications/send-telegram', [NotificationController::class, 'sendTelegramNotification'])->name('notifications.send-telegram');
});

// Push Notification Subscription Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');
    Route::get('/webpush/vapid-public-key', [WebPushController::class, 'getVapidPublicKey'])->name('webpush.vapid-public-key');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    require __DIR__.'/api_v1.php';
});

/*
 * Telegram Bot API Routes
 */
Route::prefix('telegram')->group(function () {
    Route::post('webhook', [App\Http\Controllers\Api\TelegramBotController::class, 'webhook']);
    Route::get('set-webhook', [App\Http\Controllers\Api\TelegramBotController::class, 'setWebhook']);
    Route::get('delete-webhook', [App\Http\Controllers\Api\TelegramBotController::class, 'deleteWebhook']);
    Route::get('webhook-info', [App\Http\Controllers\Api\TelegramBotController::class, 'getWebhookInfo']);

    // User account Telegram integration routes - protected
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('account', [App\Http\Controllers\Api\TelegramAccountController::class, 'info']);
        Route::post('account/link', [App\Http\Controllers\Api\TelegramAccountController::class, 'link']);
        Route::post('account/unlink', [App\Http\Controllers\Api\TelegramAccountController::class, 'unlink']);
        Route::post('account/toggle-notifications', [App\Http\Controllers\Api\TelegramAccountController::class, 'toggleNotifications']);
        Route::post('account/test-notification', [App\Http\Controllers\Api\TelegramAccountController::class, 'sendTestNotification']);
    });
});
