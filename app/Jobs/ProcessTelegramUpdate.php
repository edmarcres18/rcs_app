<?php

namespace App\Jobs;

use App\Http\Controllers\Api\TelegramBotController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTelegramUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The update data from Telegram.
     *
     * @var array
     */
    protected $update;

    /**
     * Create a new job instance.
     *
     * @param array $update
     * @return void
     */
    public function __construct(array $update)
    {
        $this->update = $update;
    }

    /**
     * Execute the job.
     *
     * @param \App\Http\Controllers\Api\TelegramBotController $controller
     * @return void
     */
    public function handle(TelegramBotController $controller)
    {
        try {
            Log::info('Processing Telegram update from queue.', ['update_id' => $this->update['update_id'] ?? null]);
            $controller->processUpdate($this->update);
        } catch (\Exception $e) {
            Log::error('Error processing Telegram update from queue.', [
                'update_id' => $this->update['update_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
            // Re-throw the exception to let the queue handle retries/failures
            throw $e;
        }
    }
}
