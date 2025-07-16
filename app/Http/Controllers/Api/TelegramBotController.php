<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

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
        $update = $request->all();
        Log::info('Telegram webhook received', ['update' => $update]);

        $this->processUpdate($update);

        return response()->json(['status' => 'success']);
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
                $this->handleStartCommand($chatId, $username, $params);
                break;

            case '/link':
                $this->handleLinkCommand($chatId, $username, $params);
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
     * @param array $params
     * @return void
     */
    protected function handleStartCommand($chatId, $username = null, $params = [])
    {
        $message = "Good Success! Welcome to MHR Reporting Compliance System Notifications\n\n";
        $message .= "Here are some available commands:\n";
        $message .= "/help - Show available commands\n";
        $message .= "/link [email] - Link your account with your email\n";
        $message .= "/status - Check your account linking status\n";

        $this->telegramService->sendMessage($chatId, $message);

        // If user already has an account linked by chat ID, show status
        if (!empty($username) || !empty($chatId)) {
            $user = $this->findUserByTelegram($chatId, $username);

            if ($user) {
                $statusMessage = "\nYour account is already linked to: " . $user->email;
                $this->telegramService->sendMessage($chatId, $statusMessage);
            }
        }
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
        $message = "MHR Reporting Compliance System Bot Commands:\n\n";
        $message .= "/start - Start the bot and get welcome message\n";
        $message .= "/link [email] - Link your account with your email address\n";
        $message .= "/status - Check if your account is linked\n";
        $message .= "/help - Show this help message\n";

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
            $status = $user->telegram_notifications_enabled ? 'enabled' : 'disabled';

            $message = "Your account is linked to:\n";
            $message .= "Email: {$user->email}\n";
            $message .= "Name: {$user->name}\n";
            $message .= "Notifications: {$status}\n\n";
            $message .= "You will receive notifications for new instructions and updates.";

            $this->telegramService->sendMessage($chatId, $message);
        } else {
            $message = "Your Telegram account is not linked to any MHR Reporting Compliance System account.\n";
            $message .= "Please use /link [your-email] to link your account.";

            $this->telegramService->sendMessage($chatId, $message);
        }
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
