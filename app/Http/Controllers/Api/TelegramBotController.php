<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use App\Jobs\ProcessTelegramUpdate;

class TelegramBotController extends Controller
{
    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegramService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\TelegramService  $telegramService
     * @return void
     */
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the incoming Telegram webhook request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        try {
            $update = $request->all();
            Log::info('Telegram webhook received', ['update' => $update]);

            // Queue after the HTTP response is sent to avoid any DB/queue latency blocking Telegram
            ProcessTelegramUpdate::dispatch($update)->afterResponse();
        } catch (\Throwable $e) {
            // Never fail the webhook response; log and still return 200
            Log::error('Telegram webhook handling error', [
                'message' => $e->getMessage(),
            ]);
        }

        // Telegram expects a 200 OK quickly to consider delivery successful
        return response()->json(['status' => 'ok']);
    }

    /**
     * Process an update from Telegram.
     *
     * @param array $update
     * @return void
     */
    public function processUpdate(array $update)
    {
        // Check if this is a message with text
        if (isset($update['message']) && isset($update['message']['text'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'];
            $username = $message['from']['username'] ?? null;

            // Save the chat data for debugging
            Log::info('Processing Telegram message', [
                'chat_id' => $chatId,
                'username' => $username,
                'text' => $text
            ]);

            // Check if this is a command (starts with /)
            if (strpos($text, '/') === 0) {
                $this->handleCommand($text, $chatId, $username, $message);
            }
        }
    }

    /**
     * Handle Telegram bot commands.
     *
     * @param string $text
     * @param string $chatId
     * @param string|null $username
     * @param array $message
     * @return void
     */
    protected function handleCommand($text, $chatId, $username = null, $message = [])
    {
        // Extract command and parameters
        $parts = explode(' ', trim($text));
        $command = strtolower(array_shift($parts));
        $params = $parts;

        switch ($command) {
            case '/start':
                $this->handleStartCommand($chatId, $username);
                break;

            case '/link':
                $this->handleLinkCommand($chatId, $username, $params);
                break;

            case '/unlink':
                $this->handleUnlinkCommand($chatId, $username);
                break;

            case '/enable':
                $this->handleEnableNotificationsCommand($chatId, $username);
                break;

            case '/disable':
                $this->handleDisableNotificationsCommand($chatId, $username);
                break;

            case '/register':
                $this->handleRegisterCommand($chatId, $username, $params, $message);
                break;

            case '/help':
                $this->handleHelpCommand($chatId);
                break;

            case '/status':
                $this->handleStatusCommand($chatId, $username);
                break;

            case '/activity':
                $this->handleActivityCommand($chatId, $username);
                break;

            default:
                // Use predefined commands from config or fall back to default message
                if (!$this->telegramService->handleCommand($text, $chatId)) {
                    $this->telegramService->sendMessage(
                        $chatId,
                        "Unknown command. Type /help to see available commands."
                    );
                }
                break;
        }
    }

    /**
     * Handle the /start command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleStartCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            // User is already linked
            $status = $user->telegram_notifications_enabled ? '✅ Enabled' : '❌ Disabled';
            $message = "<b>Welcome back, " . e($user->full_name) . "!</b>\n\n";
            $message .= "Your Telegram account is linked to the MHR Reporting Compliance System.\n\n";
            $message .= "<b>Email:</b> " . e($user->email) . "\n";
            $message .= "<b>Notification Status:</b> " . $status . "\n\n";
            $message .= "You are all set to receive real-time notifications. Type /help to see all available commands.";
        } else {
            // New user
            $message = "<b>Welcome to the MHR Reporting Compliance System Bot!</b>\n\n";
            $message .= "This bot delivers real-time notifications for instructions and deadlines directly to your Telegram account.\n\n";
            $message .= "To get started, please link this Telegram account with your MHR system email address using the following command:\n\n";
            $message .= "<code>/link your.email@example.com</code>\n\n";
            $message .= "<i>(Replace <b>your.email@example.com</b> with the email you use for the MHR system.)</i>";
        }

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle the /link command.
     *
     * @param string $chatId
     * @param string|null $username
     * @param array $params
     * @return void
     */
    protected function handleLinkCommand($chatId, $username = null, $params = [])
    {
        // Check if an email parameter was provided
        $email = $params[0] ?? null;

        if (empty($email)) {
            $message = "Please provide your email address to link your account.\n";
            $message .= "Example: /link your.email@example.com";
            $this->telegramService->sendMessage($chatId, $message);
            return;
        }

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "No user found with email: {$email}");
            return;
        }

        // Update user with Telegram info
        $user->telegram_chat_id = $chatId;

        if ($username) {
            $user->telegram_username = $username;
        }

        // Enable Telegram notifications by default when linking
        $user->telegram_notifications_enabled = true;
        $user->save();

        $this->telegramService->sendMessage(
            $chatId,
            "Success! Your MHR Reporting Compliance System account has been linked to this Telegram account. You will now receive notifications here."
        );
    }

    /**
     * Handle the /unlink command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleUnlinkCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            $user->telegram_chat_id = null;
            $user->telegram_username = null;
            $user->telegram_notifications_enabled = false;
            $user->save();

            $this->telegramService->sendMessage($chatId, "Your Telegram account has been successfully unlinked from the MHR system.");
        } else {
            $this->telegramService->sendMessage($chatId, "This Telegram account is not linked to any account. Use /link to get started.");
        }
    }

    /**
     * Handle the /enable command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleEnableNotificationsCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            if ($user->telegram_notifications_enabled) {
                $this->telegramService->sendMessage($chatId, "Notifications are already enabled for your account.");
            } else {
                $user->telegram_notifications_enabled = true;
                $user->save();
                $this->telegramService->sendMessage($chatId, "✅ Notifications have been enabled. You will now receive alerts via Telegram.");
            }
        } else {
            $this->telegramService->sendMessage($chatId, "This Telegram account is not linked. Use /link to get started.");
        }
    }

    /**
     * Handle the /disable command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleDisableNotificationsCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            if (!$user->telegram_notifications_enabled) {
                $this->telegramService->sendMessage($chatId, "Notifications are already disabled for your account.");
            } else {
                $user->telegram_notifications_enabled = false;
                $user->save();
                $this->telegramService->sendMessage($chatId, "❌ Notifications have been disabled. You will no longer receive alerts via Telegram.");
            }
        } else {
            $this->telegramService->sendMessage($chatId, "This Telegram account is not linked. Use /link to get started.");
        }
    }

    /**
     * Handle the /register command.
     *
     * @param string $chatId
     * @param string|null $username
     * @param array $params
     * @param array $message
     * @return void
     */
    protected function handleRegisterCommand($chatId, $username = null, $params = [], $message = [])
    {
        // This is a placeholder - actual registration would require more complex flow with email verification
        $this->telegramService->sendMessage(
            $chatId,
            "Registration via Telegram is not available. Please register on our website first, then link your account using /link command."
        );
    }

    /**
     * Handle the /help command.
     *
     * @param string $chatId
     * @return void
     */
    protected function handleHelpCommand($chatId)
    {
        $message = "<b>MHR Reporting Compliance System Bot Help</b>\n\n";
        $message .= "Here are the available commands:\n\n";
        $message .= "<b>/start</b> - <i>Display welcome message and status.</i>\n";
        $message .= "<b>/link [email]</b> - <i>Link your Telegram to your MHR account.</i>\n";
        $message .= "<b>/unlink</b> - <i>Remove the link between your Telegram and MHR account.</i>\n";
        $message .= "<b>/status</b> - <i>Check your account linking status.</i>\n";
        $message .= "<b>/enable</b> - <i>Enable receiving notifications.</i>\n";
        $message .= "<b>/disable</b> - <i>Disable receiving notifications.</i>\n";
        $message .= "<b>/activity</b> - <i>Show your recent activities.</i>\n";
        $message .= "<b>/help</b> - <i>Show this help message.</i>";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle the /status command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleStatusCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if ($user) {
            $status = $user->telegram_notifications_enabled ? '✅ Enabled' : '❌ Disabled';

            $message = "<b>Your Account Status</b>\n\n";
            $message .= "This Telegram account is linked to the following MHR System user:\n\n";
            $message .= "<b>Name:</b> " . e($user->full_name) . "\n";
            $message .= "<b>Email:</b> " . e($user->email) . "\n";
            $message .= "<b>Notification Status:</b> " . $status . "\n\n";
            $message .= "To change your notification preference, use /enable or /disable.";

            $this->telegramService->sendMessage($chatId, $message);
        } else {
            $message = "Your Telegram account is not linked to any MHR Reporting Compliance System account.\n\n";
            $message .= "Please use the <code>/link [your-email]</code> command to link your account.";

            $this->telegramService->sendMessage($chatId, $message);
        }
    }

    /**
     * Handle the /activity command.
     *
     * @param string $chatId
     * @param string|null $username
     * @return void
     */
    protected function handleActivityCommand($chatId, $username = null)
    {
        $user = $this->findUserByTelegram($chatId, $username);

        if (!$user) {
            $this->telegramService->sendMessage($chatId, "🚫 This Telegram account is not linked. Use /link to get started.");
            return;
        }

        // Fetch recent activities for the current user
        $activities = $user->activities()->latest()->take(10)->get();

        if ($activities->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "You have no recent activity.");
            return;
        }

        $message = "<b>🔎 Your Recent Activities (Last 10)</b>\n\n";
        $message .= "<pre>";

        foreach ($activities as $key => $activity) {
            $activityType = ucwords(str_replace('_', ' ', $activity->activity_type));
            $activityTime = $activity->created_at->format('Y-m-d H:i:s T');

            $message .= "Event:    " . e($activityType) . "\n";
            $message .= "Desc:     " . e($activity->activity_description) . "\n";
            $message .= "Time:     " . $activityTime . "\n";
            $message .= "IP:       " . e($activity->ip_address) . "\n";

            if ($activity->device !== 'Unknown' && $activity->device !== null) {
                $deviceInfo = e($activity->device) . " (" . e($activity->platform) . ", " . e($activity->browser) . ")";
                $message .= "Device:   " . $deviceInfo . "\n";
            }
            if ($activity->location) {
                $message .= "Location: " . e($activity->location) . "\n";
            }

            // Add a separator between entries, but not for the last one
            if ($key < $activities->count() - 1) {
                $message .= "---------------------------------\n";
            }
        }

        $message .= "</pre>";

        $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'HTML']);
    }

    /**
     * Find user by Telegram chat ID or username.
     *
     * @param string|null $chatId
     * @param string|null $username
     * @return \App\Models\User|null
     */
    protected function findUserByTelegram($chatId = null, $username = null)
    {
        $query = User::query();

        if ($chatId) {
            $query->where('telegram_chat_id', $chatId);
        } elseif ($username) {
            $query->where('telegram_username', $username);
        } else {
            return null;
        }

        return $query->first();
    }

    /**
     * Set the webhook URL for the Telegram bot.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setWebhook(Request $request)
    {
        $customUrl = $request->input('url');

        try {
            $response = $this->telegramService->setWebhook($customUrl);

            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to set webhook'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the webhook URL for the Telegram bot.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteWebhook()
    {
        try {
            $response = $this->telegramService->deleteWebhook();

            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete webhook'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get information about the current webhook.
     *
     * @return \Illuminate\Http\Response
     */
    public function getWebhookInfo()
    {
        try {
            $response = $this->telegramService->getWebhookInfo();

            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'response' => $response
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get webhook info'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }
}
