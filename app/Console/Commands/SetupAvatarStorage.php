<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SetupAvatarStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avatar:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup avatar storage directories and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up avatar storage...');

        // Create storage link if it doesn't exist
        if (!File::exists(public_path('storage'))) {
            $this->call('storage:link');
            $this->info('Storage link created successfully.');
        } else {
            $this->info('Storage link already exists.');
        }

        // Ensure avatars directory exists
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
            $this->info('Avatars directory created in storage/app/public/avatars');
        } else {
            $this->info('Avatars directory already exists.');
        }

        // Set proper permissions (Unix/Linux only)
        if (PHP_OS_FAMILY !== 'Windows') {
            $storagePath = storage_path('app/public');
            $publicStoragePath = public_path('storage');
            
            if (File::exists($storagePath)) {
                chmod($storagePath, 0775);
                $this->info('Set permissions for storage directory.');
            }
            
            if (File::exists($publicStoragePath)) {
                chmod($publicStoragePath, 0755);
                $this->info('Set permissions for public storage link.');
            }
        }

        $this->info('Avatar storage setup completed successfully!');
        $this->info('You can now upload avatars up to 10MB.');
        
        return 0;
    }
}
