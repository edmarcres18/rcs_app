<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for Web Push notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating VAPID keys...');

        try {
            $vapid = VAPID::createVapidKeys();

            $this->info('VAPID keys generated successfully!');
            $this->line('');
            $this->line('Add these keys to your .env file:');
            $this->line('');
            $this->line('VAPID_PUBLIC_KEY=' . $vapid['publicKey']);
            $this->line('VAPID_PRIVATE_KEY=' . $vapid['privateKey']);
            $this->line('');

            if ($this->confirm('Would you like to update your .env file automatically?')) {
                $this->updateEnvFile($vapid);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to generate VAPID keys: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Update the .env file with the new VAPID keys
     *
     * @param array $vapid
     * @return void
     */
    private function updateEnvFile(array $vapid)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->error('.env file not found!');
            return;
        }

        $envContent = file_get_contents($envPath);

        // Check if keys already exist
        $publicKeyExists = strpos($envContent, 'VAPID_PUBLIC_KEY=') !== false;
        $privateKeyExists = strpos($envContent, 'VAPID_PRIVATE_KEY=') !== false;

        if ($publicKeyExists) {
            $envContent = preg_replace(
                '/VAPID_PUBLIC_KEY=.*/',
                'VAPID_PUBLIC_KEY=' . $vapid['publicKey'],
                $envContent
            );
        } else {
            $envContent .= "\nVAPID_PUBLIC_KEY=" . $vapid['publicKey'] . "\n";
        }

        if ($privateKeyExists) {
            $envContent = preg_replace(
                '/VAPID_PRIVATE_KEY=.*/',
                'VAPID_PRIVATE_KEY=' . $vapid['privateKey'],
                $envContent
            );
        } else {
            $envContent .= "VAPID_PRIVATE_KEY=" . $vapid['privateKey'] . "\n";
        }

        file_put_contents($envPath, $envContent);

        $this->info('.env file updated successfully!');
    }
}
