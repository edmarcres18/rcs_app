<?php

namespace App\Console\Commands;

use App\Models\Instruction;
use App\Notifications\InstructionDeadlineReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInstructionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instructions:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminders for instructions that are due in 5 days.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending instruction deadline reminders...');

        $daysToRemind = [5, 4, 3, 2];

        foreach ($daysToRemind as $day) {
            $reminderDate = Carbon::now()->addDays($day)->toDateString();
            $this->info("Checking for instructions due in {$day} day(s)...");

            $instructions = Instruction::whereDate('target_deadline', $reminderDate)->get();

            if ($instructions->isEmpty()) {
                $this->info("No instructions are due in {$day} day(s).");
                continue;
            }

            foreach ($instructions as $instruction) {
                $recipients = $instruction->recipients;

                foreach ($recipients as $recipient) {
                    // Check if the recipient has replied
                    $hasReplied = $instruction->replies()->where('user_id', $recipient->id)->exists();

                    if (!$hasReplied) {
                        try {
                            $recipient->notify(new InstructionDeadlineReminder($instruction));
                            $this->info("Reminder sent to {$recipient->email} for instruction #{$instruction->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send reminder to {$recipient->email} for instruction #{$instruction->id}: " . $e->getMessage());
                            $this->error("Failed to send reminder to {$recipient->email}. Check logs for details.");
                        }
                    }
                }
            }
        }

        $this->info('Finished sending instruction deadline reminders.');
    }
}
