<?php

namespace App\Console\Commands;

use App\Models\Instruction;
use App\Notifications\InstructionDeadlineReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminders to users for instructions that are approaching their deadline.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending instruction deadline reminders...');

        // Define how many days in advance to send reminders.
        $reminderPeriodsInDays = [3, 1];

        foreach ($reminderPeriodsInDays as $days) {
            $targetDate = now()->addDays($days)->startOfDay();

            $this->line("Checking for deadlines on: {$targetDate->toDateString()} ({$days} days from now)");

            // Get instructions with approaching deadlines that are not yet completed.
            // Eager-load recipients and sender to prevent N+1 issues.
            $instructions = Instruction::with(['recipients', 'sender', 'replies'])
                ->whereNotNull('target_deadline')
                ->whereDate('target_deadline', '=', $targetDate)
                ->whereDoesntHave('activities', function ($query) {
                    $query->where('action', 'completed');
                })
                ->get();

            if ($instructions->isEmpty()) {
                $this->line("-> No instructions found with a deadline on this date.");
                continue;
            }

            $this->info("-> Found {$instructions->count()} instructions.");

            foreach ($instructions as $instruction) {
                // Determine which recipients have already replied
                $repliedUserIds = $instruction->replies->pluck('user_id')->unique();

                // Filter out recipients who have already replied
                $recipientsToNotify = $instruction->recipients->whereNotIn('id', $repliedUserIds);

                if ($recipientsToNotify->isNotEmpty()) {
                    $this->line("   - Notifying {$recipientsToNotify->count()} recipients for instruction #{$instruction->id}: '{$instruction->title}'");
                    Notification::send($recipientsToNotify, new InstructionDeadlineReminder($instruction));
                } else {
                    $this->line("   - All recipients have already replied to instruction #{$instruction->id}.");
                }
            }
        }

        $this->info('Finished sending reminders.');

        return self::SUCCESS;
    }
}
