<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class DeleteTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:delete-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the current Telegram webhook';

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
        $this->info('Deleting Telegram webhook...');

        try {
            $response = $this->telegramService->deleteWebhook();

            if ($response && isset($response['ok']) && $response['ok'] === true && $response['result'] === true) {
                $this->info('Webhook successfully deleted!');
                $this->info('You can now use polling with: php artisan telegram:poll');
                return 0;
            }

            $this->error('Failed to delete webhook.');
            $this->error(json_encode($response, JSON_PRETTY_PRINT));
            return 1;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
