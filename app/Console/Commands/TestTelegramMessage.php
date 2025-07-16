<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test-message {chat_id} {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test message to a Telegram chat';

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
        $chatId = $this->argument('chat_id');
        $message = $this->argument('message') ?? 'Good Success! Welcome to MHR Reporting Compliance System Notifications';

        $this->info("Sending message to chat ID: {$chatId}");
        $this->info("Message: {$message}");

        $response = $this->telegramService->sendMessage($chatId, $message);

        if ($response) {
            $this->info('Message sent successfully!');
            $this->info(json_encode($response, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->error('Failed to send message.');
        return 1;
    }
}
