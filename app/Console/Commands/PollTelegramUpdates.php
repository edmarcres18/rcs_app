<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\TelegramBotController;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollTelegramUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll {--timeout=30} {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll for Telegram updates using getUpdates API (for local development)';

    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegramService;

    /**
     * The Telegram bot controller instance.
     *
     * @var \App\Http\Controllers\Api\TelegramBotController
     */
    protected $telegramController;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\TelegramService  $telegramService
     * @param  \App\Http\Controllers\Api\TelegramBotController  $telegramController
     * @return void
     */
    public function __construct(TelegramService $telegramService, TelegramBotController $telegramController)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
        $this->telegramController = $telegramController;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $timeout = (int) $this->option('timeout');
        $offset = null;

        $this->info('Starting Telegram polling...');
        $this->info('Press Ctrl+C to stop');

        // First, ensure no webhook is set
        $webhookInfo = $this->telegramService->getWebhookInfo();
        if ($webhookInfo && $webhookInfo['result']['url'] !== '') {
            $this->warn('Webhook is currently set up. Removing webhook for local polling...');
            $this->telegramService->deleteWebhook();
        }

        while (true) {
            $this->info("Polling for updates (offset: " . ($offset ?? 'none') . ")...");

            try {
                $updates = $this->telegramService->getUpdates($offset, $limit, $timeout);

                if ($updates && !empty($updates['result'])) {
                    foreach ($updates['result'] as $update) {
                        $this->processUpdate($update);

                        // Update offset to acknowledged this update
                        $offset = $update['update_id'] + 1;
                    }
                }
            } catch (\Exception $e) {
                $this->error('Error polling updates: ' . $e->getMessage());
                Log::error('Error polling Telegram updates', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Wait a bit before retrying
                sleep(5);
            }
        }

        return 0;
    }

    /**
     * Process a Telegram update.
     *
     * @param array $update
     * @return void
     */
    protected function processUpdate($update)
    {
        $this->info('Processing update ID: ' . $update['update_id']);

        if (isset($update['message'])) {
            $message = $update['message'];
            $this->info('Message from ' . ($message['from']['username'] ?? 'Unknown') . ': ' . ($message['text'] ?? 'No text'));

            // Call the same method that would handle webhook updates
            $this->telegramController->processUpdate($update);
        } else {
            $this->warn('Update contains no message');
        }
    }
}
