<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class VerifyAndRepairTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:verify-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that the Telegram webhook is set correctly and repair it if needed.';

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
        if (!app()->environment('production')) {
            $this->info('Webhook verification is only for production environment. Skipping.');
            return 0;
        }

        $this->info('Verifying Telegram webhook...');

        $expectedUrl = config('app.url') . '/api/telegram/webhook';
        $webhookInfo = $this->telegramService->getWebhookInfo();

        if ($webhookInfo && isset($webhookInfo['result']['url']) && $webhookInfo['result']['url'] === $expectedUrl) {
            $this->info('Webhook is set correctly: ' . $webhookInfo['result']['url']);
        } else {
            $this->warn('Webhook is not set correctly or is missing. Attempting to repair...');
            Log::warning('Telegram webhook is incorrect or missing. Repairing.', [
                'expected_url' => $expectedUrl,
                'current_info' => $webhookInfo,
            ]);

            try {
                $this->telegramService->setWebhook($expectedUrl);
                $this->info('Webhook has been repaired successfully.');
                Log::info('Telegram webhook repaired successfully.', ['url' => $expectedUrl]);
            } catch (\Exception $e) {
                $this->error('Failed to repair webhook: ' . $e->getMessage());
                Log::error('Failed to repair Telegram webhook.', ['error' => $e->getMessage()]);
                return 1;
            }
        }

        return 0;
    }
}
