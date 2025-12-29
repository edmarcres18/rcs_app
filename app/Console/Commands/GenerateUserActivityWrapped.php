<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserActivityWrappedService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateUserActivityWrapped extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wrapped:generate {year? : Year to generate (default: current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate yearly RCS Wrapped summaries for all users';

    /**
     * Execute the console command.
     */
    public function handle(UserActivityWrappedService $service): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);
        $start = Carbon::create($year, 1, 1, 0, 0, 0);
        $this->info("Generating Wrapped summaries for year {$year}...");

        $totalUsers = User::count();
        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        User::select('id')->chunk(200, function ($chunk) use ($service, $year, $bar) {
            foreach ($chunk as $user) {
                $summary = $service->generateWrappedSummary($user->id, $year);
                $path = "wrapped/{$user->id}/{$year}.json";
                Storage::put($path, json_encode($summary));
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Wrapped summaries generated and cached successfully.');

        return self::SUCCESS;
    }
}
