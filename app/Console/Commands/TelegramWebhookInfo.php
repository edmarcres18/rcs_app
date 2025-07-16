<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramWebhookInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get information about the current Telegram webhook';

    /**
     * The Telegram service instance.
     *
     * @var \App\Services\TelegramService
     */
    protected $telegramService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\TelegramService  $telegramService
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
        $this->info('Getting Telegram webhook information...');

        try {
            $response = $this->telegramService->getWebhookInfo();

            if ($response && isset($response['ok']) && $response['ok'] === true) {
                $result = $response['result'];

                $this->info('Webhook Information:');
                $this->info('--------------------------');

                if (empty($result['url'])) {
                    $this->warn('No webhook is currently set');
                    return 0;
                }

                $this->info('URL: ' . $result['url']);
                $this->info('Has Custom Certificate: ' . ($result['has_custom_certificate'] ? 'Yes' : 'No'));
                $this->info('Pending Update Count: ' . $result['pending_update_count']);

                if (!empty($result['last_error_date'])) {
                    $errorDate = new \DateTime('@' . $result['last_error_date']);
                    $this->error('Last Error Date: ' . $errorDate->format('Y-m-d H:i:s'));
                    $this->error('Last Error Message: ' . $result['last_error_message']);
                }

                $this->info('Max Connections: ' . ($result['max_connections'] ?? 'Default'));
                $this->info('Allowed Updates: ' . (empty($result['allowed_updates']) ? 'All' : implode(', ', $result['allowed_updates'])));

                return 0;
            }

            $this->error('Failed to get webhook information.');
            $this->error(json_encode($response, JSON_PRETTY_PRINT));
            return 1;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
