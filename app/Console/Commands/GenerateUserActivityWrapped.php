<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserActivityWrappedService;
use Illuminate\Console\Command;

class GenerateUserActivityWrapped extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-activity:wrapped {year? : Year to summarize (defaults to current)} {--user= : Specific user ID to summarize}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate yearly "Wrapped" summaries for user activities.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);
        $userId = $this->option('user');

        $query = User::query()
            ->with(['activities' => fn ($q) => $q->whereYear('created_at', $year)])
            ->whereHas('activities', fn ($q) => $q->whereYear('created_at', $year));

        if ($userId) {
            $query->where('id', $userId);
        }

        $count = 0;
        $query->chunk(50, function ($users) use (&$count, $year) {
            foreach ($users as $user) {
                $wrapped = UserActivityWrappedService::generate($user, $year);
                $count++;

                // Output a friendly summary to the console; downstream consumers can hook events/notifications.
                $this->info($wrapped['summary']);
            }
        });

        $this->info("Generated Wrapped for {$count} user(s) for {$year}.");

        return self::SUCCESS;
    }
}
