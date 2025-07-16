<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class TelegramService
{
    /**
     * The Telegram API token.
     *
     * @var string
     */
    protected $token;

    /**
     * Whether debug mode is enabled.
     *
     * @var bool
     */
    protected $debug;

    /**
     * Predefined command responses.
     *
     * @var array
     */
    protected $commands;

    /**
     * Whether running in local environment
     *
     * @var bool
     */
    protected $isLocal;

    /**
     * Create a new Telegram service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->token = config('telegram.api_token') ?: config('services.telegram.bot_token');
        $this->debug = config('telegram.debug', false);
        $this->commands = config('telegram.commands', []);
        $this->isLocal = App::environment('local');
    }

    /**
     * Send a message to a Telegram chat.
     *
     * @param int|string $chatId
     * @param string $message
     * @return array|null
     */
    public function sendMessage($chatId, $message)
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($this->debug) {
                Log::debug('Telegram message sent', [
                    'chat_id' => $chatId,
                    'message' => $message,
                    'response' => $response->json(),
                ]);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Telegram API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Send a notification to multiple Telegram chats.
     *
     * @param array $chatIds
     * @param string $message
     * @return array
     */
    public function broadcastMessage(array $chatIds, $message)
    {
        $results = [];

        foreach ($chatIds as $chatId) {
            $results[$chatId] = $this->sendMessage($chatId, $message);
        }

        return $results;
    }

    /**
     * Send a notification to all admin chats.
     *
     * @param string $message
     * @return array
     */
    public function notifyAdmins($message)
    {
        $adminChatIds = config('telegram.admin_chat_ids', []);

        if (empty($adminChatIds)) {
            Log::warning('No admin chat IDs configured for Telegram notifications');
            return [];
        }

        return $this->broadcastMessage($adminChatIds, $message);
    }

    /**
     * Handle a command from a Telegram chat.
     *
     * @param string $command
     * @param int|string $chatId
     * @return array|null
     */
    public function handleCommand($command, $chatId)
    {
        $command = ltrim($command, '/');

        if (isset($this->commands[$command])) {
            return $this->sendMessage($chatId, $this->commands[$command]);
        }

        return null;
    }

    /**
     * Get updates using long polling (for local development).
     *
     * @param int $offset
     * @param int $limit
     * @param int $timeout
     * @return array|null
     */
    public function getUpdates($offset = null, $limit = 100, $timeout = 0)
    {
        try {
            $params = [
                'limit' => $limit,
                'timeout' => $timeout,
            ];

            if ($offset !== null) {
                $params['offset'] = $offset;
            }

            $response = Http::get("https://api.telegram.org/bot{$this->token}/getUpdates", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Telegram getUpdates error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram getUpdates exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Set or delete the webhook URL based on environment.
     *
     * @param string|null $url The webhook URL or null to delete
     * @return array|null
     */
    public function setWebhook($url = null)
    {
        try {
            // If we're in local environment and no URL is provided, delete any existing webhook
            if ($this->isLocal && $url === null) {
                return $this->deleteWebhook();
            }

            // Use provided URL or get from config
            $webhookUrl = $url ?: config('telegram.webhook_url') ?: config('services.telegram.webhook_url');

            if (empty($webhookUrl)) {
                Log::warning('No webhook URL provided');
                return null;
            }

            $response = Http::get("https://api.telegram.org/bot{$this->token}/setWebhook", [
                'url' => $webhookUrl
            ]);

            if ($response->successful()) {
                Log::info('Telegram webhook set', ['url' => $webhookUrl]);
                return $response->json();
            }

            Log::error('Telegram setWebhook error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram setWebhook exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete any existing webhook.
     *
     * @return array|null
     */
    public function deleteWebhook()
    {
        try {
            $response = Http::get("https://api.telegram.org/bot{$this->token}/deleteWebhook");

            if ($response->successful()) {
                Log::info('Telegram webhook deleted');
                return $response->json();
            }

            Log::error('Telegram deleteWebhook error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram deleteWebhook exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get webhook info.
     *
     * @return array|null
     */
    public function getWebhookInfo()
    {
        try {
            $response = Http::get("https://api.telegram.org/bot{$this->token}/getWebhookInfo");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Telegram getWebhookInfo error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Telegram getWebhookInfo exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
