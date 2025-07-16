<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SetupTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup-webhook {url?} {--ngrok : Use ngrok for local development}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the Telegram bot webhook';

    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegramService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\TelegramService $telegramService
     * @return void
     */
    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');
        $useNgrok = $this->option('ngrok');
        $isLocal = App::environment('local');

        // If no URL provided but we're in local environment
        if (!$url && $isLocal) {
            if ($useNgrok) {
                $url = $this->setupWithNgrok();
                if (!$url) {
                    return 1; // Error already displayed
                }
            } else {
                $this->info('You are in local environment without a webhook URL.');
                $this->info('Options:');
                $this->info('1. Use polling instead: php artisan telegram:poll');
                $this->info('2. Use ngrok: php artisan telegram:setup-webhook --ngrok');
                $this->info('3. Provide a custom URL: php artisan telegram:setup-webhook https://your-url.com/api/telegram/webhook');

                if (!$this->confirm('Do you want to delete any existing webhook and use polling instead?', true)) {
                    return 0;
                }

                $this->info('Deleting existing webhook...');
                $response = $this->telegramService->deleteWebhook();

                if ($response && isset($response['result']) && $response['result'] === true) {
                    $this->info('Webhook deleted successfully. You can now use: php artisan telegram:poll');
                    return 0;
                } else {
                    $this->error('Failed to delete webhook. Please try again.');
                    return 1;
                }
            }
        }

        // At this point, we should have a URL
        if (empty($url)) {
            // Try to get from config
            $url = config('telegram.webhook_url') ?: config('services.telegram.webhook_url');
        }

        if (empty($url)) {
            $this->error('Telegram webhook URL is not configured. Please provide a URL or check your .env file.');
            return 1;
        }

        $this->info('Setting up Telegram webhook...');
        $this->info("Webhook URL: {$url}");

        try {
            $response = $this->telegramService->setWebhook($url);

            if ($response && isset($response['result']) && $response['result'] === true) {
                $this->info('Webhook set up successfully!');

                // Get webhook info to verify
                $webhookInfo = $this->telegramService->getWebhookInfo();
                if ($webhookInfo) {
                    $this->info('Webhook Information:');
                    $this->info('URL: ' . $webhookInfo['result']['url']);
                    $this->info('Has custom certificate: ' . ($webhookInfo['result']['has_custom_certificate'] ? 'Yes' : 'No'));
                    $this->info('Pending update count: ' . $webhookInfo['result']['pending_update_count']);
                }

                return 0;
            }

            $this->error('Failed to set up webhook:');
            $this->error(json_encode($response, JSON_PRETTY_PRINT));
            return 1;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('Failed to set up Telegram webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Set up webhook using ngrok for local development.
     *
     * @return string|null The ngrok URL if successful, null otherwise
     */
    protected function setupWithNgrok()
    {
        $this->info('Attempting to set up with ngrok...');

        // Check if ngrok is running
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://127.0.0.1:4040/api/tunnels');

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['tunnels']) && count($data['tunnels']) > 0) {
                    // Look for HTTPS tunnel
                    $tunnel = null;
                    foreach ($data['tunnels'] as $t) {
                        if ($t['proto'] === 'https') {
                            $tunnel = $t;
                            break;
                        }
                    }

                    if ($tunnel) {
                        $ngrokUrl = $tunnel['public_url'];
                        $webhookUrl = $ngrokUrl . '/api/telegram/webhook';

                        $this->info("Found ngrok tunnel: {$ngrokUrl}");
                        $this->info("Using webhook URL: {$webhookUrl}");

                        return $webhookUrl;
                    }
                }
            }

            $this->error('No active ngrok HTTPS tunnel found.');
            $this->info('Please start ngrok with: ngrok http your_local_port');
            return null;
        } catch (\Exception $e) {
            $this->error('Error connecting to ngrok API: ' . $e->getMessage());
            $this->info('Make sure ngrok is running with: ngrok http your_local_port');
            return null;
        }
    }
}
